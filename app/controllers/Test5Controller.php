<?php

class Test5Controller extends Base
{
	public function initialize(){
		$this->response->setHeader("Content-Type", "text/html; charset=utf-8");
	}

	public function indexAction(){
		$this->cal_ansdo(12);
	}

	public function calStdAction($examinee_id){
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
					$std_score[$examinee_id][$factor_id][] = $this->cal_cpi_Std_score($dm,$factor_anses->factor_id,$factor_anses->score);
					break;
				case 'EPQA':
					$dm = ($examinee->sex ==0) ? 2 : 1;
					$dage = floor($age);
					$std_score[$examinee_id][$factor_id][] = $this->cal_epqa_Std_score($dm,$dage,$factor_anses->factor_id,$factor_anses->score);
					break;
				case '16PF':
					$dm = ($examinee->sex ==0) ? 9 : 8;
					$std_score[$examinee_id][$factor_id][] = $this->cal_ks_Std_score($dm,$factor_anses->factor_id,$factor_anses->score);
					break;
				case 'SPM': 
					$std_score[$examinee_id][$factor_id][] = $this->cal_cpi_Std_score($age,$factor_anses->factor_id,$factor_anses->score);
					break;
				default:
					$std_score[$examinee_id][$factor_id][] = $factor_anses->sore;
					break;
			}
			return $std_score;
		}
	}

	public function cal_cpi_Std_score($dm,$factor_id,$score){
		$cpimode = Cpimode::findFirst(array(
            'DM=?0 and YZ=?1',
            'bind'=>array(0=>$dm,1=>$factor_name)));
		$m = $cpimode->M;
		$sd = $cpimode->SD;
		$std_score = 50 + (10 * ($score - $m)) / $sd;
		return $std_score;
	}

	public function cal_epqa_Std_score($dm,$dage,$factor_id,$score){
		$factor_name = CalAge::getFactorName($factor_id);
		$epqamode = Epqamode::query()
				  ->where('DSEX =?0')
				  ->andWhere("DAGEL <= '$age' AND DAGEH >= '$age'")
				  ->bind(array(0 => $dm));
		switch ($factor_name) {
			case 'epqae':
				$m = $epqamode->EM;
				$sd = $epqamode->ESD;
				break;
			case 'epqan':
				$m = $epqamode->NM;
				$sd = $epqamode->NSD;
				break;
			case 'epqap':
				$m = $epqamode->PM;
				$sd = $epqamode->PSD;
				break;
			case 'epqal':
				$m = $epqamode->LM;
				$sd = $epqamode->LSD;
				break;
			
			default:
				throw new Exception("No record find!");
				break;
		}
		$std_score = 50 + (10 * ($score - $m)) / $sd;
		return $std_score;
	}

	public function cal_ks_Std_score($dm,$factor_id,$score){
		$factor_name = CalAge::getFactorName($factor_id);
		$ksmode = Ksmode::query()
				->where("DM =?0 AND YZ =?1")
				->andWhere("QSF <= '$age' AND ZZF >='$age'")
				->bind(array(0=>$dm,1=>$factor_name));
		$std_score = $ksmode->BZF;
		return $std_score;
	}

	public function cal_spm_Std_score($age,$factor_id,$score){
		if ($this->getFactorName($factor_anses->factor_id) == "spm"){
			$spmmode = Spmmode::query()
					 ->where("NLL <= '$age' AND NLH >= '$age'");
			if ($score >= $spmmode->B95) {
				$std_score = 1;
			}
			else if ($score >= $spmmode->B75) {
				$std_score =2;
			}
			else if ($score >= $spmmode->B25) {
				$std_score = 3;
			}
			else if ($score >= $spmmode->B5) {
				$std_score = 4;
			}
			else{
				$std_score = 5;
			}
		}
		else{
			$std_score = $score;
		}
		return $std_score;
	}

	public function cal_ansdo($examinee_id){
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
			$index = Index::findFirst($index_anses->index_id);
			$children = explode(",",$index->children);
			$childrentype = explode(",",$index->children_type);
		}
	}
}