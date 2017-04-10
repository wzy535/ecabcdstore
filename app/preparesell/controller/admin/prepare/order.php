<?php
class preparesell_ctl_admin_prepare_order extends desktop_controller
{	
	function __construct($app)
	{
		$this->app = $app;
		$this->mdl_prepare_order = app::get('preparesell')->model('prepare_order');
		$this->mdl_product = app::get('b2c')->model('products');
		$this->mdl_orders = app::get('b2c')->model('orders');
	}
	function prepare_order_number()
	{	
		$prepare_id = $_POST['prepare_id'];
		$prepare_order = $this->mdl_prepare_order->getList('prepare_id,preparename,preparesell_price,product_id,order_id',array('prepare_id'=>$prepare_id));
		foreach ($prepare_order as $key => $value) {
			$pay_status = $this->mdl_orders->getRow('pay_status',array('order_id'=>$value['order_id']));
			if($pay_status['pay_status']!=0)
			{
				$product = $this->mdl_product->getRow('product_id,name,bn,spec_info',array('product_id'=>$value['product_id']));
				unset($prepare_order[$key]);
				$num = $this->mdl_prepare_order->count(array('product_id'=>$value['product_id']));
				$prepare_detial[$value['product_id']]['pay_status'] = $pay_status['pay_status'];
				$prepare_detial[$value['product_id']]['prepare_id'] = $value['prepare_id'];
				$prepare_detial[$value['product_id']]['product_id'] = $value['product_id'];
				$prepare_detial[$value['product_id']]['preparename'] = $value['preparename'];
				$prepare_detial[$value['product_id']]['preparesell_price'] = $value['preparesell_price'];
				$prepare_detial[$value['product_id']]['num'] = $num;
				$prepare_detial[$value['product_id']]['name'] = $product['name'];
				$prepare_detial[$value['product_id']]['bn'] = $product['bn'];
				$prepare_detial[$value['product_id']]['spec_info'] = $product['spec_info'];
			}
		}
		//echo '<pre>';print_r($prepare_detial);exit();
		$this->pagedata['prepare_detial'] = $prepare_detial;
		$this->display('admin/preparesell/order_num.html');
	}
}
?>