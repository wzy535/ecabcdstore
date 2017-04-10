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
class preparesell_prepare_ordercreate {
    
    /**
     * 订单创建进行库存处理
     * @access public
     * @param array $order
     * @return bool
     * @version 1 Jul 15, 2011
     */
    public function order_notify($order) {
        $prepare_goods_mdl=app::get('preparesell')->model('preparesell_goods');
        $prepare=$prepare_goods_mdl->getRow('product_id,initial_num',array('product_id'=>$order));
        $initial_num=$prepare['initial_num']-1;
        //echo '<pre>';print_r($prepare);exit();
        $data=array(
                'initial_num'=>$initial_num,
        );
        //预售商品表的库存
        $filter=array(
                'product_id'=>$prepare['product_id'],
        );
        $goods_res=$prepare_goods_mdl->update($data,$filter);
    }
}