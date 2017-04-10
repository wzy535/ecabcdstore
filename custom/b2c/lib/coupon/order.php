<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_coupon_order {


    public function __construct() {
        $this->app = app::get('b2c');
    }

    public function get_list_order($order_id = 0) {
        if( empty($order_id) ) return false;
        $filter = array('memc_gen_orderid'=>$order_id);
        $filter['memc_isvalid'] = 'true'; //只获取已经激活的优惠券，避免更新因订单营销活动获得的优惠券
        $arr = $this->app->model('member_coupon')->getList('memc_code,member_id,disabled', $filter);
        return $arr;
    }

    public function order_pay_finish(&$sdf, $status='succ', $from='Back',&$msg=''){
        $order_data = $sdf['orders'];
        if( $order_data ){
            foreach($order_data as $rel_id=>$order_info){
                if ( $order_info['bill_type'] == 'payments' && $order_info['pay_object'] == 'order' && ($status == 'succ' || $status == 'progress') ){
                    $order_id = $order_info['rel_id'];
                    if( empty($order_id) ) continue;
                    $this->use_c($order_id);
                }
            }
        }
    }

    /**
     * 订单在【支付，发货，完成】后更新优惠券状态未已经使用
     * 取消订单后，且该订单未支付，更新优惠券状态未可用
     **/
    public function use_c( $order_id = null ) {
        if( empty($order_id) ) return false;
        $coupon_data = $this->get_list_order($order_id);

        if($coupon_data){
            $status = $this->get_status($order_id);
            $obj_coupon = kernel::single("b2c_coupon_mem");
            foreach($coupon_data as $coupon){
                if( $status != $coupon['disabled'] ){
                    $obj_coupon->use_c($coupon['memc_code'], $coupon['member_id'],$order_id,$status);
                }
            }
            if( $status == 'false' ){
                $this->cancel_c($order_id);
            }
        }
    }

    private function get_status($order_id){
        $order_data = $this->app->model('orders')->getRow('payment,pay_status,status',array('order_id'=>$order_id));
        $payment = $order_data['payment'];
        $pay_status = $order_data['pay_status'];
        $status = $order_data['status'];

        if( $pay_status == '0' && $status == 'dead' && $payment != '-1' ){
            return 'false'; //取消未支付订单，返回优惠券
        }

        return 'true';
    }

    /**
     * 返回优惠券时，删除优惠券的使用记录
     */
    function cancel_c($order_id){
        if( empty($order_id) ) return false;
        $coupon_user = app::get('couponlog')->model('order_coupon_user');
        $coupon_ref = app::get('couponlog')->model('order_coupon_ref');

        $filter = array('order_id'=>$order_id);
        $coupon_user->delete($filter);
        $coupon_ref->delete($filter);
    }
}

