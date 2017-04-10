<?php

class preparesell_prepare_goods{

    function __construct($app){
        $this->app = $app;
        $this->mdl_goods = app::get('b2c')->model('goods');
        $this->mdl_products = app::get('b2c')->model('products');
        $this->mdl_preparesell = app::get('preparesell')->model('preparesell');
        $this->mdl_preparesell_goods = app::get('preparesell')->model('preparesell_goods');
    }

    function getPrepareGoodsDetail($filter){
        $preparesell_goods = $this->mdl_preparesell->getRow('*',$filter);
        $col='name,bn,price';
        $goods = $this->mdl_goods->getRow($col,array('goods_id'=>$preparesell_goods['goods_id']));
        $result[] = array_merge($preparesell_goods,$goods);
        return $result;
    }
    //获取预售货品信息的详细信息
    function getPrepareProductsDetail($filter){
        $preparesell_products = $this->mdl_preparesell_goods->getList('*',$filter);
        foreach ($preparesell_products as $key => $value) {
            $products_id[$key]=$value['product_id'];
        }
        foreach ($preparesell_products as $key => $value) {
            $products[$value['product_id']]['product_id']=$value['product_id'];
            $products[$value['product_id']]['prepare_id']=$value['prepare_id'];
            $products[$value['product_id']]['preparesell_price']=$value['preparesell_price'];
            $products[$value['product_id']]['status']=$value['status'];
            $products[$value['product_id']]['begin_time']=$value['begin_time'];
            $products[$value['product_id']]['end_time']=$value['end_time'];
            $products[$value['product_id']]['begin_time_final']=$value['begin_time_final'];
            $products[$value['product_id']]['end_time_final']=$value['end_time_final'];
            $products[$value['product_id']]['remind_way']=$value['remind_way'];
            $products[$value['product_id']]['remind_time']=$value['remind_time'];
            $products[$value['product_id']]['prepares_rule']=$value['prepares_rule'];
            $products[$value['product_id']]['initial_num']=$value['initial_num'];
        }
        $product_detail = $this->mdl_products->getList('*',array('product_id|in'=>$products_id));
        foreach ($product_detail as $key => $value) {
           $product_detail[$key]['initial_num']=$products[$value['product_id']]['initial_num'];
           $product_detail[$key]['preparesell_price']=$products[$value['product_id']]['preparesell_price'];
           $product_detail[$key]['status']=$products[$value['product_id']]['status'];
           $product_detail[$key]['begin_time']=$products[$value['product_id']]['begin_time'];
           $product_detail[$key]['end_time']=$products[$value['product_id']]['end_time'];
           $product_detail[$key]['begin_time_final']=$products[$value['product_id']]['begin_time_final'];
           $product_detail[$key]['end_time_final']=$products[$value['product_id']]['end_time_final'];
           $product_detail[$key]['remind_time']=$products[$value['product_id']]['remind_time'];
           $product_detail[$key]['prepares_rule']=$products[$value['product_id']]['prepares_rule'];
        }
        return $product_detail;
    }

    //获取预售商品规则的详细信息
    function get_Prepare_Detail($filter)
    {
        $prepare=$this->mdl_preparesell->getRow('*',array('goods_id'=>$filter));
        $prepare['nowtime']=time();
        return $prepare;
    }
    //获取货品的类型
    function get_product_type($filter)
    {
        $product_id = array();
        if( $filter['goods']['adjunct'] ){
            foreach ($filter['goods']['adjunct'] as $key => $value) {
                foreach ($value as $k => $v) {
                    $product_id[] = $k;
                }
            }
        }
        array_push($product_id, $filter[2]);
        $promotion_type=$this->mdl_preparesell_goods->getList('promotion_type',array('product_id'=>$product_id));
        return $promotion_type;
    }
    //再次购买
    function get_product_buyagain($filter)
    {
        $promotion_type=$this->mdl_preparesell_goods->getList('promotion_type',array('product_id'=>$filter));
        return $promotion_type;
    }
    //下单结算
    function get_product_buy($filter)
    {   
        foreach ($filter as $key => $value) {
            foreach ($value['obj_items']['products'] as $k => $v) {
                $p[] = $v['product_id'];
            }
        }
        $promotion_type=$this->mdl_preparesell_goods->getList('promotion_type',array('product_id'=>$p));
        return $promotion_type;
    }
    //预售商品在商品后台的修改判断
    function is_update($filter)
    {   
        $prepare = $this->mdl_preparesell->getList('*',array('goods_id|in'=>$filter['goods_id']));
        return $prepare;
    }

    function goods_update($filter)
    {
        $prepare = $this->mdl_preparesell->getRow('*',array('goods_id'=>$filter));
        return $prepare;
    }
    //判断预售商品是否可以被删除
    public function is_delete( $gid,$pid=null ) {
        $filter = array();
        
        if( $pid )
            $filter['product_id'] = $pid;
        
        if( $gid ) 
            $filter['goods_id'] = $gid;
        $count = $this->mdl_preparesell->count( $filter );
        if( $count ) {
            $this->error_msg = '含有预售商品不可以删除，请先到预售活动中删除该预售商品！';
            return false;
        }
        return true;
    }


}
