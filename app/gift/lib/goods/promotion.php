<?php

class gift_goods_promotion
{
	function __construct($app)
	{
		$this->app = $app;
	}
	//赠品和预售的排他
	function extend_filter(&$filter)
	{	
		$mdl_starbuy = app::get('starbuy')->model('special_goods');
		$mdl_preparesell = app::get('preparesell')->model('preparesell');
		if($filter['promotion']=='gift')
		{	
			//预售的
			$pre_goods_id = $mdl_preparesell->getList('goods_id');
			if (empty($pre_goods_id)) {
				$pre_goods_id = array();
			}
			
			$fmt_id = $pre_goods_id;
			foreach ($fmt_id as $key => $value) {
				$goods_id[]=$value['goods_id'];
			}
			
			$data = array(
				'goods_id|notin'=>$goods_id
				);
			unset($filter['promotion']);
			$filter['goods_id|notin'] = $goods_id;
		}
		
	}
}
