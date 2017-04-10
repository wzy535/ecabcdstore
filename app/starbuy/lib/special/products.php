<?php

class starbuy_special_products{

    function __construct($app){
        $this->app = $app;
        $this->mdl_product = app::get('b2c')->model('products');
        $this->mdl_goods = app::get('b2c')->model('goods');
        $this->mdl_special = app::get('starbuy')->model('special');
        $this->mdl_special_goods = app::get('starbuy')->model('special_goods');
        $this->mdl_promotion_type = app::get('starbuy')->model('promotions_type');
    }


    /*
     *special_goods列表页面中数据的处理
     *@$params special_product 数据
     *
     */
    function getParams($params){
        $listpro = $this->_getProduct($params['product_id']);
        $listgoods = $this->_getGoods($listpro['goods_id']);
        foreach($listpro['spec_desc']['spec_private_value_id'] as $k=>$v){
            if($img = $listgoods['spec_desc'][$k][$v]['spec_goods_images']){
                $imgs = explode(',',$img);
                $num=count($imgs)-1;
                $listgoods['image_default_id']=$imgs[$num]?$imgs[$num]:$listgoods['image_default_id'];
            }

        }
        $spec_value = implode(" ",$listpro['spec_desc']['spec_value']);
        $listgoods['name'] = $listgoods['name']." ".$spec_value;
        $listpro['price']=$params['promotion_price'];
        $listpro['special_typeid']=$params['type_id'];
        $listpro['begin_time']=$params['begin_time'];
        $listpro['end_time']=$params['end_time'];
        $listpro['initial_num']=$params['initial_num'];
        if($params['remind_time']){
            $listpro['remind_time']=strtotime("-".$params['remind_time']." minute",$params['begin_time']);
        }
        $remind = $params['remind_way'];
        $listpro['remind_way'] = $remind;
        if(in_array('email',$remind) && !in_array('sms',$remind)){
            $listpro['email_remind'] = true;
        }elseif(in_array('sms',$remind) && !in_array('email',$remind)){
            $listpro['sms_remind'] = true;
        }elseif(in_array('email',$remind) && in_array('sms',$remind)){
            $listpro['all_remind'] = true;
        }
        $listgoods['products'] = $listpro;

        return $listgoods;
    }


    /*
     *special_goods详情页面中数据的处理
     *@$params special_product 数据
     *
     */
    function getdetailParams($params){

        $product = $this->_getProduct($params['product_id']);
        $goodsdata = array_merge($params,$product);
        $goodsdata['price'] = $goodsdata['promotion_price'];
        $goods = $this->_getGoods($product['goods_id']);

        if($product['spec_desc']['spec_private_value_id']){
            foreach($product['spec_desc']['spec_private_value_id'] as $k=>$row){
                $spec_goods_images = $goods['spec_desc'][$k][$row]['spec_goods_images'];
                if(!empty($spec_goods_images) && $spec_goods_images != $default_spec_image){
                    $imagesArr = explode(',',$spec_goods_images);
                    foreach( (array)$imagesArr as $image_id ){
                        $goodsdata['images'][]['image_id'] = $image_id;
                        $goods['image_default_id']=$image_id?$image_id:$goods['image_default_id'];
                    }
                }
            }
        }

        $remind = $params['remind_way'];
        if(in_array('email',$remind) && !in_array('sms',$remind)){
            $goodsdata['email_remind'] = true;
        }elseif(in_array('sms',$remind) && !in_array('email',$remind)){
            $goodsdata['sms_remind'] = true;
        }elseif(in_array('email',$remind) && in_array('sms',$remind)){
            $goodsdata['all_remind'] = true;
        }else{
            $goodsdata['msgbox_remind'] = true;
        }
        $goodsdata['goods'] = $goods;
        return $goodsdata;
    }


    function _getProduct($filter){
        $products="";
        if($filter){
            $products = $this->mdl_product->getRow("*",array('product_id'=>$filter));
        }
        return $products;
    }

    function _getGoods($filter){
        $goods="";
        if($filter){
            $goods = $this->mdl_goods->getRow('goods_id,name,bn,image_default_id,spec_desc,intro,cat_id,brand_id',array('goods_id'=>$filter));
            $goods['brand_name'] = app::get('b2c')->model('brand')->get_brand_name($goods['brand_id']);
            $goods['cat_name'] = app::get('b2c')->model('goods_cat')->get_cat_name($goods['cat_id']);
        }
        return $goods;

    }

    function getSpecialGoodsDetail($filter){
        $special_goods = $this->mdl_special_goods->getList('*',$filter);
        foreach($special_goods as $key=>$value){
            $product = $this->mdl_product->getRow('*',array('product_id'=>$value['product_id']));
            $result[] = array_merge($value,$product);
        }
        return $result;
    }

    function getSpecialProduct($product_id){
        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['status']='true';
        $filter['product_id'] = $product_id;
        $product = $this->mdl_special_goods->getRow('*',$filter);
        if($product){
            $product['type_id'] = $this->getTypename(array('type_id'=>$product['type_id']));
            return $product;
        }
        return false;
    }

    function getTypename($filter){
        $type_name = $this->mdl_promotion_type->getList("name",$filter);
        return $type_name[0]['name'];
    }

    function _get_protype(){

        $type_name = $this->mdl_promotion_type->getList("type_id,name");
        return $type_name;
    }

    function getStatus($filter){
        $special = $this->mdl_special->getRow("status",array('special_id'=>$filter));
        return $special['status'];
    }

    function getPrice($product_id,&$price){
        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['product_id'] = $product_id;
        $filter['status'] = 'true';
        $list = $this->mdl_special_goods->getRow('promotion_price,type_id',$filter);
        if($list){
            $price['price'] = $list['promotion_price'];
            $price['pricelabel'] = $this->getTypename(array('type_id'=>$list['type_id']));
        }
    }

    function getStore($product_id,&$store){
        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['product_id'] = $product_id;
        $filter['status'] = 'true';

        $list = $this->mdl_special_goods->getRow('*',$filter);
        if($list){
            $limit = $list['limit'];
            if($limit){
                $store['store'] = ($store['store'] < $limit) ? $store['store'] : $limit;
            }
        }
    }

    function check_store($product_id,$num,&$msg){
        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['product_id'] = $product_id;
        $filter['status'] = 'true';
        $list = $this->mdl_special_goods->getRow('*',$filter);
        $product = $this->_getProduct($product_id);
        if($list){
            if($list['limit']>0 && $list['limit'] < intval($num)){
                $msg = "购买超过限购量";
                return false;
            }
        }
        return true;
    }


    function ifSpecial($product_id){
        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['product_id'] = $product_id;
        $filter['status'] = 'true';

        $list = $this->mdl_special_goods->getRow('type_id',$filter);
        if($list){
            return "starbuy";
        }
        return false;
    }

    function check_special_goods(&$products){
        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['product_id'] = $products['product_id'];
        $filter['status'] = 'true';

        if($products){
            $special_goods = $this->mdl_special_goods->getRow('type_id,promotion_price',$filter);
            if($special_goods){
                $products['price'] = $special_goods['promotion_price'];
                $products['promotion_type'] = $this->getTypename(array('type_id'=>$special_goods['type_id']));
            }
        }
    }
}
