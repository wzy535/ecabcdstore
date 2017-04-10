<?php

class starbuy_cart_object_special extends b2c_cart_object_goods{

    /**
     * 检查库存
     * @param array 加入购物车的商品结构
     * @param array 现有购物车的数量
     * @param string message
     * @return boolean true or false
     */
    public function check_store($arr_data, $arr_carts, &$msg='', $_check_adjunct=true){
        if(empty($arr_data) || !$arr_data['goods'] || !$arr_data['goods']['goods_id'] || !$arr_data['goods']['product_id']) trigger_error(app::get('b2c')->_("购物车操作失败"),E_USER_ERROR);
        if(intval($arr_data['goods']['num'])<1){
            $msg = '商品数量错误！';
            return false;
        }
        $goods_id = $arr_data['goods']['goods_id'];
        $product_id = $arr_data['goods']['product_id'];

        $aData['quantity'] = $this->omath->number_plus($arr_data['goods']['num']);
        $return_status = $this->_check_products_with_add($goods_id, $product_id, $aData['quantity']);
        if ($return_status['status'] == 'false')
            return $return_status;

        if($_check_adjunct) {
            $result = null;
            $flag = $this->_check_adjunct($arr_data, $goods_id,$result);
            if( !$flag ) {
                $msg = '配件验证失败！';
                return false;
            } else {
                foreach( kernel::servicelist('b2c_addtocart_check') as $object ) {
                    if( !is_object($object) ) continue;
                    $flag = $object->check( $goods_id,$arr_product['product_id'],($result[$arr_product['product_id']]?$result[$arr_product['product_id']]:$aData['quantity']),$msg );
                    if( !$flag ) return false;
                }
            }
            return true;
        } else {
            return  true;
        }



    }
}
