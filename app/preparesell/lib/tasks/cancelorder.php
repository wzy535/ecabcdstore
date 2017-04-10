<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class preparesell_tasks_cancelorder extends base_task_abstract implements base_interface_task{


    function exec($params=null){
        $cancel_mdl = app::get('preparesell')->model('prepare_order');
        $order_mdl = app::get('b2c')->model('orders');
        $cancel_obj = kernel::single('preparesell_prepare_order');
        $nowtime = time();
        $orders = $cancel_mdl->getList("order_id",array('canceltime|lthan'=>$nowtime));
        foreach ($orders as $key => $value) {
        	$order_id[]=$value['order_id'];
        }
        $orders_cancel=$order_mdl->getList('order_id,pay_status',array('order_id'=>$order_id));
        foreach ($orders_cancel as $key => $value) {
        	if($value['pay_status']==0)
        	{
        		$orders_id[]=$value['order_id'];
        	}
        }
        //error_log(print_r($order_id,1),3,DATA_DIR.'/01.log');
        $cancel_obj->check_order($orders_id);
    }
}


