<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_apiv_apis_response_items_all
{
    var $default_fields = 'goods_id,bn,name,price,store,last_modify';

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
        $sdf['page_no'] = $sdf['page_no'] ? max(1,(int)$sdf['page_no']) : 1;
        $sdf['page_size'] = $sdf['page_size'] ? max(1,(int)$sdf['page_size']) : 20;
        $sdf['page_size'] = min(100,$sdf['page_size']);
        $sdf['start_modified'] = $sdf['start_modified'] ? strtotime($sdf['start_modified']) : '';
        $sdf['end_modified'] = $sdf['end_modified'] ? strtotime($sdf['end_modified']) : '';
        
        $cat_id = floatval($sdf['cat_id']);
        $type_id = floatval($sdf['cid']);
        $brand_id = floatval($sdf['brand_id']);
        $sdf['approve_status'] = $sdf['approve_status'];
        
        switch($sdf['approve_status']){
            case 'onsale' : $marketable='true'; break;
            case 'instock' : $marketable='false'; break;
        }
        
        //清理非法字段名称
        $this->clear_fields($sdf['fields']);
        $sdf['fields'] = $sdf['fields'] ? trim($sdf['fields']) : '*';

        /** 生成过滤条件 **/
        //$db = kernel::database();
        $filter = array();

        $page_size = $sdf['page_size'];
        $page_no = ($sdf['page_no'] - 1) * $page_size;

        $start_time = $sdf['start_modified'];
        $end_time = $sdf['end_modified'];
        if($start_time) $filter['last_modify|bthan'] = $start_time;
        if($end_time) $filter['last_modify|lthan'] = $end_time;
        if($cat_id) $filter['cat_id'] = $cat_id;
        if($type_id) $filter['type_id'] = $type_id;
        if($brand_id) $filter['brand_id'] = $brand_id;
        if($marketable) $filter['marketable'] = $marketable;

        $mdl_goods = $this->app->model('goods');
        $total_results = $mdl_goods->count($filter);
        if( ! $total_results){
            return array('total_results'=>0, 'items'=>'[]');            
        }
        
        $rs = $mdl_goods->getList($sdf['fields'], $filter, $page_no, $page_size);

        /**
         * 得到返回的商品信息
         */
        $sdf_goods = array();
        //$sdf_goods['item'] = $rs;
        foreach($rs as $v){
            //$sdf_goods['item'][] = $this->get_item_detail($arr_row, $obj_ctl_goods);
            $sdf_goods['item'][] = array(
                "iid"=>$v['goods_id'],
                "num_iid"=>$v['goods_id'],
                "outer_id"=>$v['bn'],
                "bn"=>$v['bn'],
                "num"=>$v['store'],
                "title"=>$v['name'],
                "cid"=>$v['cat_id'],
                "shopcat_id"=>$v['type_id'],
                "brand_id"=>$v['brand_id'],
                "input_pids"=>"",
                "input_str"=>"",
                //"detail_url"=>kernel::base_url(true)."/index.php/product-".$v['goods_id'].".html",
                "default_img_url"=>kernel::single('base_storager')->image_path($v['image_default_id']),
                "score"=>floatval($v['score']),
                "supplier_id"=>"",
                "supplier_name"=>"",
                "barcode"=>"",
                "is_simple"=>"",
                "valid_thru"=>"",
                "costprice"=>$v['cost'],
                "list_time"=>$this->format_time($v['uptime']),
                "delist_time"=>$this->format_time($v['downtime']),
                //"stuff_status"=>"new",
                //"country"=>"中国",
                //"state"=>"上海市",
                //"city"=>"上海市",
                //"district"=>"徐汇区",
                //"post_fee"=>"15.00",
                //"express_fee"=>"30.00",
                //"ems_fee"=>"20.00",
                //"has_discount"=>"true",
                //"freight_payer"=>"buyer",
                //"has_invoice"=>"true",
                //"has_warranty"=>"true",
                //"has_showcase"=>"true",
                //"increment"=>"0",
                //"auto_repost"=>"false",
                //"postage_id"=>"",
                //"auction_point"=>"30%",
                //"is_virtual"=>"true",
                //"seller_uname"=>"张三",
                //"type"=>"fixed",
                //"props"=>"颜色=>红色;配置=>全配",
                "status"=> ($v['marketable']=='false') ? "instock" : "onsale",
                "approve_status"=> ($v['marketable']=='false') ? "instock" : "onsale",
                "price"=>$v['price'],
                "mktprice"=>floatval($v['mktprice']),
                //"unit"=>"台",
                "modified"=>$this->format_time($v['last_modify'])
            );
        }

        return array('total_results'=>$total_results, 'items'=>($sdf_goods));
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
    
}
