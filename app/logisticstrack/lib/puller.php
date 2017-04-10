<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class logisticstrack_puller {

    private static $kv_timeout = 6;// s

    public static function pull_logi($delivery_id, &$hock) {
        if ( !$delivery_id ) {
            $hock['msg'] = app::get('logisticstrack')->_('必要参数发货单号不能为空');
            return false;
        }

        // 从本地拉数据
        if (  !constant('WITHOUT_CACHE') && self::pull_from_local($delivery_id, $hock) ) {
            return true;
        }

        // 从中心拉数据
        if ( '1' == $delivery_id{0} ) { // 发货单
            $deliveryMdl = app::get('b2c')->model('delivery');
            $delivery = $deliveryMdl->getList('order_id,logi_id,logi_name,logi_no',array('delivery_id'=>$delivery_id,'disabled'=>'false'),0,1);
        } elseif ( '9' == $delivery_id{0}  ) { // 退货单
            $reshipMdl = app::get('b2c')->model('reship');
            $delivery = $reshipMdl->getList('order_id,logi_id,logi_name,logi_no',array('reship_id'=>$delivery_id,'disabled'=>'false'),0,1);
        }
        $logi_no = $delivery[0]['logi_no'];

        $dlycorpMdl = app::get('b2c')->model('dlycorp');
        $dlycorp = $dlycorpMdl->getList('corp_code',array('corp_id'=>$delivery[0]['logi_id']),0,1);
        $dlycorp_code = $dlycorp[0]['corp_code'];
        if( !$dlycorp_code ){
            $hock['msg'] = app::get('logisticstrack')->_('不支持该物流公司查询');
            return false;
        }

        $rpc_data['order_bn'] = $delivery[0]['order_id'];
        $rpc_data['logi_code'] = $dlycorp_code;
        $rpc_data['company_name'] = $delivery[0]['logi_name'];
        $rpc_data['logi_no'] = $delivery[0]['logi_no'];

        if ( self::pull_from_matrix($rpc_data, $hock) ) { // 从中心拉取数据成功
            self::store_to_local($delivery_id, $hock);
            return true;
        } else { // 从中心拉取数据失败,记录日志
            if ( base_kvstore::instance('logisticstrack')->fetch("delivery.error.$delivery_id", $hock_org) ) {
                $hock['error.times'] = $hock_org['error.times'] + 1;
                $hock['message'] .= $hock_org['message'];
            }

            base_kvstore::instance('logisticstrack')->store("delivery.error.$delivery_id", $hock);
        }
        return false;
    }

    private static function pull_from_matrix($rpc_data, &$hock) {
        $logi = kernel::single('logisticstrack_service_hqepay');
        $response = $logi->rpc_logistics_hqepay($rpc_data);
        $hock = $response;
        $hock['logi_no'] = $rpc_data['logi_no'];
        $hock['dlycorp_code'] = $rpc_data['logi_code'];

        if($hock['rsp'] == 'fail'){
            $hock['msg'] = $hock['err_msg'] ? $hock['err_msg'] : app::get('logisticstrack')->_('系统错误,请联系客服');
        }
        if($hock['rsp'] =='succ'){
            return true;
        } else {
            return false;
        }
    }
    public static function pull_from_local($delivery_id, &$hock) {
        if ( !$delivery_id ) return false;

        // 从kvstore取
        if ( base_kvstore::instance('logisticstrack')->fetch("delivery.$delivery_id", $hock) ) {
            return true;
        }

        // 从db取
        $logiMdl = app::get('logisticstrack')->model('logistic_log');
        $logs = $logiMdl->getList('*', array('delivery_id'=>$delivery_id),0,1);
        $logs = current($logs);
        if ( !$logs || ($logs['dtline'] < time()-self::$kv_timeout) ) {
            return false;
        }
        $hock = unserialize($logs['logistic_log']);

        return true;
    }

    /**
     * 将中心拉取的数据保存到本地，提高效率降低中心压力
    */
    public static function store_to_local($delivery_id,$logilogs) {
        if ( !$delivery_id || !$logilogs ) return false;

        // 存到kvstore 60s
        base_kvstore::instance('logisticstrack')->store("delivery.$delivery_id", $logilogs, self::$kv_timeout);
        // 存到db
        $logiMdl = app::get('logisticstrack')->model('logistic_log');
        $item = $logiMdl->getList('pulltimes',array('delivery_id'=>$delivery_id));

        $logilogs_ = array(
            'dly_corp'=>$logilogs['logi_no'],
            'delivery_id'=>$delivery_id,
            'logistic_log'=>serialize($logilogs),
            'pulltimes'=>(int)$item[0]['pulltimes'] + 1,
            'dtline'=>time(),
        );
        $logiMdl->store($logilogs_,$delivery_id);
    }
}
