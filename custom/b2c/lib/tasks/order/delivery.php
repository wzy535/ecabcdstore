<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class b2c_tasks_order_delivery extends base_task_abstract implements base_interface_task{

    function exec($params=null)
    {
        $delivery_mdl = app::get('b2c')->model('order_delivery_time');
        $orders = $delivery_mdl->getList("*",array('delivery_time|sthan'=>time()));
        $this->delivery_orders($orders);
    }

    function delivery_orders($orders)
    {
        $order = app::get('b2c')->model('orders');
        $delivery_mdl = app::get('b2c')->model('order_delivery_time');
        foreach($orders as $val){
            $sdf = array(
                'order_id' => $val['order_id'],
                'received_time' => $val['delivery_time'],
                'received_status' =>1,
            );
            if($order->save($sdf)){
                $delivery_mdl->delete(array('order_id' => $val['order_id']));
            }
        }
    }
}

