<?php

class Test5Controller extends Base
{
	public function initialize(){
		$this->response->setHeader("Content-Type", "text/html; charset=utf-8");
	}

	public function indexAction(){
		$std_score = $this->calStd(12);
		$ans_score = $this->aclAns(12);
	}

	public function insertScore(){
		$factor_ans = FactorAns::find(array(
			'examinee_id=?0',
			'bind'=>array(0=>$examinee_id)));
		for ($i=0;$i<sizeof($factor_ans);$i++){
			$factor_ans->std_score = $std_score[$i];
			$factor_ans->ans_score = $ans_score[$i];
		}
	}

	public function calStd($examinee_id){
		$std_score = array();
		$examinee = CalAge::getExaminee($examinee_id);
		$age = CalAge::calAge1($examinee->birthday,$examinee->last_login);
		$factor_ans = FactorAns::find(array(
			'examinee_id=?0',
			'bind'=>array(0=>$examinee_id)));
		foreach ($factor_ans as $factor_anses) {
			$paper_name = CalAge::getPaperName($factor_anses->Factor->paper_id);
			switch ($paper_name) {
				case 'CPI':
					$dm = ($examinee->sex ==0) ? 2 : 1;
					$std_score[$examinee_id][$factor_anses->factor_id][] = $this->cal_cpi_Std_score($dm,$factor_anses->factor_id,$factor_anses->score);
					break;
				case 'EPQA':
					$dm = ($examinee->sex ==0) ? 2 : 1;
					$dage = floor($age);
					$std_score[$examinee_id][$factor_anses->factor_id][] = $this->cal_epqa_Std_score($dm,$dage,$factor_anses->factor_id,$factor_anses->score);
					break;
				case '16PF':
					$dage = floor($age);
					$dm = ($examinee->sex ==0) ? 9 : 8;
					$std_score[$examinee_id][$factor_anses->factor_id][] = $this->cal_ks_Std_score($dm,$dage,$factor_anses->factor_id,$factor_anses->score);
					break;
				case 'SPM': 
					$std_score[$examinee_id][$factor_anses->factor_id][] = $this->cal_cpi_Std_score($age,$factor_anses->factor_id,$factor_anses->score);
					break;
				default:
					$std_score[$examinee_id][$factor_anses->factor_id][] = $factor_anses->sore;
					break;
			}
			return $std_score;
		}
	}

	public function cal_cpi_Std_score($dm,$factor_id,$score){
		$cpimd = Cpimd::findFirst(array(
            'DM=?0 and YZ=?1',
            'bind'=>array(0=>$dm,1=>$factor_name)));
		$m = $cpimd->M;
		$sd = $cpimd->SD;
		$std_score = 50 + (10 * ($score - $m)) / $sd;
		return $std_score;
	}

	public function cal_epqa_Std_score($dm,$dage,$factor_id,$score){
		$factor_name = CalAge::getFactorName($factor_id);
		$epqamd = Epqamd::find(array(
			'DAGEL <= :age: AND DAGEH >= :age: AND DSEX = :sex:',
			'bind'=>array('age'=>$age,'sex'=>$sex)));
		switch ($factor_name) {
			case 'epqae':
				$m = $epqamd->EM;
				$sd = $epqamd->ESD;
				break;
			case 'epqan':
				$m = $epqamd->NM;
				$sd = $epqamd->NSD;
				break;
			case 'epqap':
				$m = $epqamd->PM;
				$sd = $epqamd->PSD;
				break;
			case 'epqal':
				$m = $epqamd->LM;
				$sd = $epqamd->LSD;
				break;		
			default:
				throw new Exception("No record find!");
				break;
		}
		$std_score = 50 + (10 * ($score - $m)) / $sd;
		return $std_score;
	}

	public function cal_ks_Std_score($dm,$dage,$factor_id,$score){
		$factor_name = CalAge::getFactorName($factor_id);
		$ksmd = Ksmd::find(array(
			'DM=?0 AND YZ=?1',
			'bind'=>array(0=>$dm,1=>$factor_name)));
		foreach ($ksmd as $ksmds) {
			if ($score <= $ksmds->ZZF && $score >= $ksmds->QSF) {
				$std_score = $ksmds->BZF;
			}
		}
		return $std_score;
	}

	public function cal_spm_Std_score($age,$factor_id,$score){
		if ($this->getFactorName($factor_anses->factor_id) == "spm"){
			$spmmd = Spmmd::find(array(
				'NLH >= :age: AND NLL <= :age:',
				'bind'=>array('age'=>$age)));
			foreach ($spmmd as $spmmds) {
				if ($score >= $spmmd->B95) {
					$std_score = 1;
				}
				else if ($score >= $spmmd->B75) {
					$std_score =2;
				}
				else if ($score >= $spmmd->B25) {
					$std_score = 3;
				}
				else if ($score >= $spmmd->B5) {
					$std_score = 4;
				}
				else{
					$std_score = 5;
				}			
			}
		}
		else{
				$std_score = $score;
			}
		return $std_score;
	}

	public function calAns($examinee_id){
		$ans_score = array();
		$factor_ans = FactorAns::find(array(
			'examinee_id=?0',
			'bind'=>array(0=>$examinee_id)));
		foreach ($factor_ans as $factor_anses) {
			$factor_id = $factor_anses->factor_id;
			$ans = $factor_anses->std_score;
			$factor = Factor::findFirst(array(
				'id=?0',
				'bind'=>array(0=>$factor_id)));
			eval($factor->ans_do.';');
			$ans_score[$examinee_id][$factor_id][] = $ans;
		}
		return $ans_score;
	}

	public function cal_index($examinee_id){
		$index_ans = IndexAns::find(array(
			'examinee_id=?0',
			'bind'=>array(0=>$examinee_id)));
		foreach ($index_ans as $index_anses) {
			$factor_score = array();
			$index = Index::findFirst($index_anses->index_id);
            $children = explode(",",$index->children );          
            $childrentype = explode(",", $index->childrentype);
            $action = $index->action;
            for ($j=0; $j < sizeof($childrentype); $j++) { 
                //0代表index，1代表factor
                if ($childrentype[$j] == "0") {
                    $index1 = Index::findFirst(array(
                        'name=?1',
                        'bind'=>array(1=>$children[$j])));
                    $children1 = $index1->children;
                    $children1 = explode(",",$children1);
                    $action = $index1->action;
                    for ($k=0; $k <sizeof($children1) ; $k++) {
                    	$factor_id = CalAge::getFactorId($children1[$k]);
                    	$factor_ans = FactorAns::findFirst(array(
                    		'factor_id=?0',
                    		'bind'=>array(0=>$factor_id)));
                    	$factor_score[] = $factor_ans->ans_score;
                    }
                    $score = $this->doAction($children,$action,$factor_score);
                }
                else{   
                    	$factor_id = CalAge::getFactorId($children[$j]);
                    	$factor_ans = FactorAns::findFirst(array(
                    		'factor_id=?0',
                    		'bind'=>array(0=>$factor_id)));
                    	$factor_score[] = $factor_ans->ans_score;
                }   
                $score = $this->doAction($children,$action,$factor_score);            
            }
		}
	}

	function doAction($children, $action, $array)
	{
		if ($this->action_function[$action] == null) {
			$this->action_function[$action] = $this->complie_action($children, $action);
		}
		return call_user_func_array($this->action_function[$action], $array);
	}

	function complie_action($child_list, $action)
	{
		// 这里需要正则加$符号
		$child_list = preg_replace('/[a-zA-Z][a-zA-Z0-9]*/', '\$$0', $child_list);
		$action     = preg_replace('/[a-zA-Z][a-zA-Z0-9]*/', '\$$0', $action);
		$action = "return $action;";
		return create_function($child_list, $action);
	}
	
}