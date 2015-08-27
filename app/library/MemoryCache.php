<?php
	/**
	 * @usage 用于缓存全局的查询数据表信息
	 * @author Wangyaohui
	 * @Date 2015-8-26
	 */
class MemoryCache {
	
	/**
	 * @method $rt->module_names, $rt->index_names, $rt->factor_names, $rt->exam_json;
	 * @usage 用于缓存通过project_id获取到的ProjectDetail表中的数据
	 * @return \Phalcon\Mvc\Model\Resultset\Simple
	 * @param int $project_id
	 */
	public static function getProjectDetail($project_id) {
		return ProjectDetail::findFirst(
			  array (
			  		"project_id = :project_id:",
			  		'bind' => array ('project_id' => $project_id),
			  		'hydration' => \Phalcon\Mvc\Model\Resultset\Simple::HYDRATE_ARRAYS,
			  		'cache' => array ('key' => 'project_detail_id_'.$project_id)
		)
		);
	}
	
	/**
	 * @method $rt->id $rt->name $rt->children
	 * @usage 缓存根据因子名称查取到的因子详细记录
	 * @param string $factor_name
	 */
	public static function getFactorDetail($factor_name){
		return Factor::findFirst(
			 array(
			"name = :factor_name:",
			'bind'=>array('factor_name'=>$factor_name),
			'hydration' => \Phalcon\Mvc\Model\Resultset\Simple::HYDRATE_ARRAYS,
			'cache' => array ('key' => 'factor_detail_name_'.$factor_name)
		) 	
		);
	}
}