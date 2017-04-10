<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class b2c_tasks_order_cancel_unpay_cancel extends base_task_abstract implements base_interface_task{

    function exec($params=null)
    {
        $order_id = $params['order_id'];
        if( $order_id ){
            $this->cancel_orders($order_id);
        }
    }

    function cancel_orders($order_id)
    {
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        $mdl_order_cancel_reason = app::get('b2c')->model('order_cancel_reason');
        if ($obj_checkorder->check_order_cancel($order_id,'',$message))
        {
            $sdf['order_id'] = $order_id;
            $sdf['op_id'] = 1;
            $sdf['opname'] = 'admin';
            $sdf['account_type'] = 'auto';

            $order_cancel_reason = array(
                'order_id' => $order_id,
                'reason_type' => 7,
                'reason_desc' => '订单超过设置的支付时间，自动取消',
                'cancel_time' => time(),
            );
            $b2c_order_cancel = kernel::single("b2c_order_cancel");
            if ($b2c_order_cancel->generate($sdf, $null, $message))
            {
                $result = $mdl_order_cancel_reason->save($order_cancel_reason);
                if($order_object = kernel::service('b2c_order_rpc_async')){
                    $order_object->modifyActive($sdf['order_id']);
                }
                $obj_coupon = kernel::single("b2c_coupon_order");
                if( $obj_coupon ){
                    $obj_coupon->use_c($sdf['order_id']);
                }
            }
        }
    }
}

