<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_apiv_apis_response_items_list
{
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 获取商品列表
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get(&$sdf, &$thisObj)
    {
        $sdf['iids'] = trim($sdf['iids']);
        if (!$sdf['iids']){
            $thisObj->send_user_error(app::get('b2c')->_('必填参数 iids 不能为空！'), array('item'=>''));
        }
        
        //清理非法字段名称
        $this->clear_fields($sdf['fields']);
        $sdf['fields'] = $sdf['fields'] ? trim($sdf['fields']) : '*';
        
        $iid_arr = explode(',', $sdf['iids']);
        $iids = array();
        foreach($iid_arr as $k=>$v){
            if($k > 40) break;//单次最多只返回40个商品
            $iids[] = floatval($v);
        }

        /** 生成过滤条件 **/
        //$db = kernel::database();
        $filter = array();
        $filter['goods_id'] = $iids;

        $mdl_goods = $this->app->model('goods');
        $total_results = $mdl_goods->count($filter);
        if( ! $total_results){
            return array('total_results'=>0, 'items'=>'[]');            
        }
        
        $rs = $mdl_goods->getList($sdf['fields'], $filter);

        /**
         * 得到返回的商品信息
         */
        $sdf_goods = array();
        foreach($rs as $v){
            //$sdf_goods['item'][] = $this->get_item_detail($arr_row, $obj_ctl_goods);
            $sdf_goods['item'][] = array(
                "iid"=>$v['goods_id'],
                "num_iid"=>$v['goods_id'],
                "title"=>$v['name'],
                "outer_id"=>$v['bn'],
                //"seller_uname"=>"xxxx",
                //"type"=>"fixed",
                "shopcat_id"=>$v['type']['type_id'],
                "input_pids"=>"",
                "input_str"=>"",
                //"score"=>"30",
                //"supplier_id"=>"1234",
                //"supplier_name"=>"xxx",
                //"barcode"=>"1212",
                //"is_simple"=>"true",
                //"valid_thru"=>"14",
                "mktprice"=>floatval($v['mktprice']),
                "costprice"=>$v['cost'],
                //"has_showcase"=>"true",
                //"auto_repost"=>"false",
                //"auction_point"=>"30%",
                //"detail_url"=>"http=>//shop1.test.shopex.cn=>20210/?product-39.html",
                "bn"=>$v['bn'],
                "brand_id"=>floatval($v['brand']['brand_id']),
                "cid"=>floatval($v['category']['cat_id']),
                "num"=>$v['store'],
                "status"=> ($v['marketable']=='false') ? "instock" : "onsale",
                "approve_status"=> ($v['marketable']=='false') ? "instock" : "onsale",
                "price"=>$v['price'],
                "unit"=>$v['unit'],
                "modified"=>$this->format_time($v['last_modify']),
                "description"=>$v['description'],
                "default_img_url"=>kernel::single('base_storager')->image_path($v['image_default_id']),
                "item_imgs"=>'',
                "prop_imgs"=>'',
                "delist_time"=>$this->format_time($v['downtime']),
                "props"=>"1632501=>3679421;20000=>29468;",
                //"stuff_status"=>"new",
                //"country"=>"中国",
                //"state"=>"上海",
                //"city"=>"上海",
                //"district"=>"徐家汇",
                //"freight_payer"=>"buyer",
                //"postage_id"=>"0",
                //"post_fee"=>"0.20",
                //"express_fee"=>"15.00",
                //"ems_fee"=>"20.00",
                //"has_invoice"=>"true",
                //"has_warranty"=>"true",
                //"has_discount"=>"true",
                //"increment"=>"",
                "is_virtual"=>"false",
                "skus"=>array('sku'=>$this->get_item_skus($v['goods_id']))
            );
        }

        return array('total_results'=>$total_results, 'items'=>($sdf_goods));
    }
    
    private function get_item_skus($goods_id)
    {
        $mdl_products = $this->app->model('products');
        $arr_skus = $mdl_products->getList('*', array('goods_id'=>$goods_id));
        $arr_sdf_skus = array();
        $str_skus = "";
        if ($arr_skus){
            foreach ($arr_skus as $arr){
                $arr_sdf_skus[] = array(
                    'sku_id'=>$arr['product_id'],
                    'iid'=>$arr['goods_id'],
                    "num_iid"=>$arr['goods_id'],
                    'outer_id'=>$arr['bn'],
                    'bn'=>$arr['bn'],
                    'properties'=>$arr['spec_info'],
                    'quantity'=>$arr['store'],
                    'weight'=>$arr['weight'],
                    'price'=>$arr['price'],
                    'market_price'=>$arr['mktprice'],
                    'modified'=>$this->format_time($arr['last_modify']),
                    'cost'=>$arr['cost'],
                    "approve_status"=> ($arr['marketable']=='false') ? "instock" : "onsale",
                );
            }
        }
        
        return $arr_sdf_skus;
    }
    
    private function clear_fields(&$fields)
    {
        $fields_arr = array();
        $fields = ','.$fields.',';
        $mdl_goods = $this->app->model('goods');
        $dbschema = $mdl_goods->get_schema();
        foreach(array_keys($dbschema['columns']) as $v){
            if(stristr($fields, ','.$v.',')){
                $fields_arr[] = $v;
            }
        }
        $fields = implode(',', $fields_arr);
    }
    
    private function format_time($timestamp)
    {
        if($timestamp){
            if(strstr($timestamp, '-')){
                return $timestamp;
            }else{
                return date('Y-m-d H:i:s', $timestamp);
            }
        }
    }
    
}
