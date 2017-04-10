<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class b2c_tasks_order_cancel_unpay extends base_task_abstract implements base_interface_task{

    function exec($params=null)
    {
        $order_mdl = app::get('b2c')->model('orders');
        $site_trigger_cancelorder = app::get('b2c')->getConf('site.trigger_cancelorder'); //是否开启自动取消未支付订单
        $site_cancelorder_timelimit = app::get('b2c')->getConf('site.cancelorder_timelimit'); //设置的时间
        logger::info('trigger:'.$site_trigger_cancelorder.' timelimit:'.$site_cancelorder_timelimit);
        $nodes_obj = app::get('b2c')->model('shop');
        $nodes = $nodes_obj->count( array('node_type'=>'ecos.ome','status'=>'bind'));

        if( $site_trigger_cancelorder == 'true' && $nodes == 0 ){
            $time = time() - $site_cancelorder_timelimit * 60 * 60;
            $starttime = $time - 24 * 60 * 60;
            $orders = $order_mdl->getList("order_id",array('createtime|bthan'=>$starttime,'createtime|sthan'=>$time,'pay_status'=>'0','status'=>'active','ship_status'=>'0','promotion_type'=>'normal'));
            $this->cancel_orders($orders);
        }
    }

    function cancel_orders($orders)
    {
        if( !$orders ) return false;
        $worker = 'b2c_tasks_order_cancel_unpay_cancel';

        foreach( $orders as $val )
        {
            if( empty($val['order_id']) ){
                continue;
            }
            // 如果是团购订单则不自动取消（走团购订单的取消流程）
            if( $this->is_starbuy($val['order_id']) )
            {
                continue;
            }
            system_queue::instance()->publish($worker, $worker, $val);
        }
    }

    /**
     * 判断订单是否时团购订单
     * sdb_b2c_order_cancel_list内的订单号为团购和预售的订单号
     * params order_id 验证的订单号
     * return bool
     */
    function is_starbuy($order_id){
        if( empty($order_id) ) return false;

        $cancel_mdl = app::get('b2c')->model('order_cancel_list');
        $starbuy_order = $cancel_mdl->getRow("order_id",array('order_id'=>$order_id));

        if( $starbuy_order ){
            return true;
        }

        return false;
    }
}

