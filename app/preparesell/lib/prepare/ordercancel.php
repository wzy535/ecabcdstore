<?php
/**
 * preparesell_prepare_ordercancel
 * 订单作废佣金订单处理功能类
 * 
 * @uses
 * @package preparesell
 * @author gongjiapeng<gongjiapeng@shopex.cn>
 * @copyright 2014 ShopEx
 * @license Commercial
 * 
 */
class preparesell_prepare_ordercancel {
    
    /**
     * 订单作废进行佣金订单处理
     * @access public
     * @param array $order
     * @return bool
     * @version 1 Jul 15, 2011
     */
    public function order_notify($order) {
        if($order['promotion_type']=='prepare')
        {
            $prepare_goods_mdl=app::get('preparesell')->model('preparesell_goods');
            $prepare_order_mdl=app::get('preparesell')->model('prepare_order');
            $prepare=$prepare_order_mdl->getRow('order_id,product_id,initial_num',array('order_id'=>$order['order_id']));
            $initial_num=$prepare['initial_num']+1;
            $data=array(
                'initial_num'=>$initial_num,
            );
            //预售订单表的库存
            $filter=array(
                'order_id'=>$prepare['order_id'],
            );
            //预售商品表的库存
            $filters=array(
                'product_id'=>$prepare['product_id'],
            );
            
            $order_res=$prepare_order_mdl->update($data,$filter);
            $goods_res=$prepare_goods_mdl->update($data,$filters);
           
            //error_log(print_r($prepare,1),3,DATA_DIR.'/22.log');
        }
    }
}