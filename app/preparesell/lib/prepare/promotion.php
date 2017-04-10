<?php

class preparesell_prepare_promotion
{
	function __construct($app)
	{
		$this->app = $app;
		$this->mdl_product = app::get('b2c')->model('products');

	}
	//预售排他
	function extend_filter(&$filter)
	{	
		$mdl_starbuy = app::get('starbuy')->model('special_goods');
		$mdl_gift = app::get('gift')->model('ref');
		$mdl_preparesell = app::get('preparesell')->model('preparesell');
		if($filter['promotion']=='prepare')
		{	
			//预售的
			$pre_goods_id = $mdl_preparesell->getList('goods_id');
			if (empty($pre_goods_id)) {
				$pre_goods_id = array();
			}
			//团购的
			$product_id = $mdl_starbuy->getList('product_id');
			if(!empty($product_id))
			{
				foreach ($product_id as $key => $value) {
					$pro_id[] = $value['product_id'];
				}
				$starbuy_goods_id = $this->mdl_product->getList('goods_id',array('product_id|in'=>$pro_id));
			}
			else
			{
				$starbuy_goods_id = array();
			}
			
			//赠品的
			$gift_goods_id = $mdl_gift->getList('goods_id');
			if(empty($gift_goods_id))
			{
				$gift_goods_id=array();
			}
			$fmt_id = array_merge($starbuy_goods_id,$gift_goods_id,$pre_goods_id);
			foreach ($fmt_id as $key => $value) {
				$goods_id[]=$value['goods_id'];
			}
			unset($filter['promotion']);
			$filter['goods_id|notin'] = $goods_id;
		}
		
	}
}
