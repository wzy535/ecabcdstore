<?php
class starbuy_special_goods extends b2c_goods_check_abstract{

    #商品列表页、商品详情页参加special活动的货品 价格、标签
    function check_special_goods_list(&$products){
        $special_goods_mdl = app::get('starbuy')->model('special_goods');
        $filter = array(
            'status'=>'true',
            'begin_time|sthan'=>time(),
            'end_time|bthan'=>time(),
        );
        foreach($products as $key=>$product){
            $filter['product_id'] = $product['product_id'];
            $list = $special_goods_mdl->getRow('promotion_price,type_id',$filter);
            if($list){
                $products[$key]['price'] = $list['promotion_price'];
                $products[$key]['promotion_type_name']=$this->_getTypeName($list['type_id']);
                $products[$key]['promotion_type_id']=$list['type_id'];
                $products[$key]['app_name']="starbuy";
            }
        }
    }


    #获取活动类型名称
    function _getTypeName($id){
        $type_mdl = app::get('starbuy')->model('promotions_type');
        $list = $type_mdl->getRow('*',array('type_id'=>$id));
        return $list['name'];
    }

    public function check_product_delete($product_id,&$msg){
        $special_goods_mdl = app::get('starbuy')->model('special_goods');
        $filter = array(
            'status'=>'true',
            'release_time|sthan'=>time(),
            'end_time|bthan'=>time(),
            'product_id'=>$product_id
        );

        $list = $special_goods_mdl->getRow("status,type_id",$filter);
        if($list){
            $type_name = $this->_getTypeName($list['type_id']);
            $msg = "该商品有未结束的".$type_name."活动";
            return false;
        }
        return true;
    }

    function is_delete($goods,$product_id=null){
        if($product_id){
            $check_result = $this->check_product_delete($product_id,$msg);
            if(!$check_result){
                $this->error_msg = $msg;
                return false;
            }
        }
        return true;

    }


    function check_goods_marketable_false($goods_ids,&$msg){
        $products = app::get('b2c')->model('products')->getList('product_id',$goods_ids);
        if($products){
            foreach($products as $key=>$value){
                $check_result = $this->check_product_delete($value['product_id'],$msg);
                if(!$check_result){
                    return false;
                    break;
                }
            }
        }
        return true;
    }

    function check_goods_promotion($proids){

    }

}
