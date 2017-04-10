<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class b2c_tasks_order_cancel extends base_task_abstract implements base_interface_task{

    function exec($params=null)
    {
        $cancel_mdl = app::get('b2c')->model('order_cancel_list');
        $orders = $cancel_mdl->getList("order_id,promotion_type,reason_desc",array('canceltime|sthan'=>time()));
        $this->cancel_orders($orders);
    }

    function cancel_orders($orders)
    {
        $cancel_mdl = app::get('b2c')->model('order_cancel_list');
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        $mdl_order_cancel_reason = app::get('b2c')->model('order_cancel_reason');
        $b2c_order_cancel = kernel::single("b2c_order_cancel");

        foreach($orders as $key=>$val){
            $oid = $val['order_id'];
            $o_reason = $val['reason_desc'];
            $o_type = $val['promotion_type'];
            if($obj_checkorder->check_order_cancel($oid))
            {
                $sdf['order_id'] = $oid;
                $sdf['op_id'] = 1;
                $sdf['opname'] = 'admin';
                $sdf['account_type'] = 'shopadmin';

                $order_cancel_reason = array(
                    'order_id' => $oid,
                    'reason_type' => 7,
                    'reason_desc' => $o_reason,
                    'cancel_time' => time(),
                );

                if ($b2c_order_cancel->generate($sdf,$null, $message)){
                    $result = $mdl_order_cancel_reason->save($order_cancel_reason);
                    if($order_object = kernel::service('b2c_order_rpc_async')){
                        $order_object->modifyActive($sdf['order_id']);
                    }

                    //预售订单自动取消后将不会再发通知的埋点
                    $preparesell_is_actived = app::get('preparesell')->getConf('app_is_actived');
                    if( $preparesell_is_actived == 'true' && $o_type == 'prepare')
                    {
                        #bala...bala...bala...bala...
                        //当预售应用处于激活状态，且订单状态为预售订单
                        $cancel_mdl = app::get('preparesell')->model('prepare_order');
                        $cancel_mdl->delete(array('order_id'=>$oid));
                    }
                }
            }
            $cancel_mdl->delete(array('order_id'=>$oid));
        }
    }
}

