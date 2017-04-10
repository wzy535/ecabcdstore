<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_apiv_apis_response_items_item
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
     * 获取商品信息
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get(&$sdf, &$thisObj)
    {
        $sdf['iid'] = floatval($sdf['iid']);
        //$sdf['fields'] = $sdf['fields'] ? trim($sdf['fields']) : '*';
        $sdf['fields'] = '*';
        
        if (!$sdf['iid']){
            $thisObj->send_user_error(app::get('b2c')->_('必填参数 iid 不能为空！'), array('item'=>''));
        }

        $mdl_goods = $this->app->model('goods');
        if (!$rs = $mdl_goods->dump($sdf['iid'], $sdf['fields'])){
            $thisObj->send_user_error(app::get('b2c')->_($sdf['iid'].' 对应的商品不存在！'), array('item'=>''));
        }
        
        /**
         * 得到返回的商品信息
         */
        $sdf_goods = array();
        //$sdf_goods['item'] = $this->get_item_detail($rows);
        $sdf_goods = array(
            "iid"=>$rs['goods_id'],
            "num_iid"=>$rs['goods_id'],
            "title"=>$rs['name'],
            "outer_id"=>$rs['bn'],
            //"seller_uname"=>"xxxx",
            //"type"=>"fixed",
            "shopcat_id"=>$rs['type']['type_id'],
            "input_pids"=>"",
            "input_str"=>"",
            //"score"=>"30",
            //"supplier_id"=>"1234",
            //"supplier_name"=>"xxx",
            //"barcode"=>"1212",
            //"is_simple"=>"true",
            //"valid_thru"=>"14",
            "mktprice"=>floatval($rs['mktprice']),
            "costprice"=>$rs['cost'],
            //"has_showcase"=>"true",
            //"auto_repost"=>"false",
            //"auction_point"=>"30%",
            //"detail_url"=>"http=>//shop1.test.shopex.cn=>20210/?product-39.html",
            "bn"=>$rs['bn'],
            "brand_id"=>floatval($rs['brand']['brand_id']),
            "cid"=>floatval($rs['category']['cat_id']),
            "num"=>$rs['store'],
            "status"=> ($rs['status']=='false') ? "instock" : "onsale",
            "approve_status"=> ($rs['status']=='false') ? "instock" : "onsale",
            "price"=>$rs['price'],
            "unit"=>$rs['unit'],
            "modified"=>$this->format_time($rs['last_modify']),
            "description"=>$rs['description'],
            "default_img_url"=>kernel::single('base_storager')->image_path($rs['image_default_id']),
            "item_imgs"=>'',
            "prop_imgs"=>'',
            "delist_time"=>$this->format_time($rs['downtime']),
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
            "skus"=>array('sku'=>$this->get_item_skus($rs['goods_id']))
        );

        return array('item'=>$sdf_goods);
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

    /**
     * 生成商品明细的数组
     * @param mixed 每行商品的数组-数据
     * @param object goods controller
     * @return mixed
     */
    private function get_item_detail($arr_row)
    {
        if (!$arr_row) return array();

        $cnt_props = 20;
        $cn_input_props = 50;

        $detal_url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$arr_row['goods_id']));
        /** props 目前是1-20 **/
        $props = "";
        for ($i=1;$i<=$cnt_props;$i++)
        {
            if ($arr_row['p_'.$i])
                $props .= $i.":".$arr_row['p_'.$i].";";
        }
        if ($props)
            $props = substr($props, 0, strlen($props)-1);
        /** end **/

        /** input props 21-50 **/
        $input_pids = "";
        $input_str = "";
        for ($i=$cnt_props+1;$i<=$cn_input_props;$i++)
        {
            if ($arr_row['p_'.$i])
            {
                $input_pids .= $i.",";
                $input_str .= $arr_row['p_'.$i].";";
            }
        }
        if ($input_pids)
            $input_pids = substr($input_pids, 0, strlen($input_pids)-1);
        if ($input_str)
            $input_str = substr($input_str, 0, strlen($input_str)-1);
        /** end **/
        $db = kernel::database();
        $arr_skus = $db->select("SELECT * FROM `sdb_b2c_products` WHERE `goods_id`=".$arr_row['goods_id']);
        $arr_sdf_skus = array();
        $str_skus = "";
        if ($arr_skus)
        {
            foreach ($arr_skus as $arr)
            {
                $str_skus_properties = '';
                $arr_spec_desc = unserialize($arr['spec_desc']);
                if($arr_spec_desc['spec_value_id'])
                {
                    foreach ($arr_spec_desc['spec_value_id'] as $spec_id_key =>$arr_value)
                        $str_skus_properties .= $spec_id_key.":".$arr_value . '_' . $arr_spec_desc['spec_value'] [$spec_id_key]. ";";
                }
                if ($str_skus_properties)
                    $str_skus_properties = substr($str_skus_properties, 0, strlen($str_skus_properties)-1);

                $arr_sdf_skus[] = array(
                    'sku_id'=>$arr['product_id'],
                    'iid'=>$arr['goods_id'],
                    'bn'=>$arr['bn'],
                    'properties'=>$str_skus_properties,
                    'quantity'=>$arr['store'],
                    'weight'=>$arr['weight'],
                    'price'=>$arr['price'],
                    'market_price'=>$arr['mktprice'],
                    'modified'=>$arr['last_modify'],
                    'cost'=>$arr['cost'],
                );
            }
            $str_skus = json_encode($arr_sdf_skus);
        }
        $default_img_url = kernel::single('base_storager')->image_path($arr_row['image_default_id']);


        $goods_images  = array();
        $arr_goods_images = $db->select("SELECT `image_id` FROM `sdb_image_image_attach` WHERE `target_type`='goods' and `target_id`=".$arr_row['goods_id']);
        if($arr_goods_images)
        {
            foreach($arr_goods_images as $single_row)
            {
                $goods_images[] = array(
                    'image_id'=>$single_row['image_id'],
                    'big_url'=>kernel::single('base_storager')->image_path($single_row['image_id'], 'l'),
                    'thisuasm_url'=>kernel::single('base_storager')->image_path($single_row['image_id'], 'm'),
                    'small_url'=>kernel::single('base_storager')->image_path($single_row['image_id'], 's'),
                    'is_default'=>'false',
                    );
            }
        }
        $goods_images = json_encode($goods_images);

        return array(
            'goods_id'=>$arr_row['goods_id'],
            'bn'=>$arr_row['bn'],
            'title'=>$arr_row['name'],
            'shop_cids'=>$arr_row['cat_id'],
            'cid'=>$arr_row['type_id'],
            'brand_id'=>$arr_row['brand_id'],
            'props'=>$props,
            'input_pids'=>$input_pids,
            'input_str'=>$input_str,
            'description'=>$arr_row['description'],
            'default_img_url'=>$default_img_url,
            'num'=>$arr_row['store'],
            'weight'=>$arr_row['weight'] ? $arr_row['weight'] : '',
            'score'=>$arr_row['score'] ? $arr_row['score'] : '',
            'price'=>$arr_row['price'],
            'market_price'=>$arr_row['mktprice'],
            'unit'=>$arr_row['unit'],
            'cost_price'=>$arr_row['cost'],
            'marketable'=>$arr_row['marketable'],
            'item_imgs'=>$goods_images,
            'modified'=>date('Y-m-d H:i:s',$arr_row['last_modify']),
            'list_time'=>date('Y-m-d H:i:s',$arr_row['uptime']),
            'delist_time'=>date('Y-m-d H:i:s',$arr_row['downtime']),
            'created'=>date('Y-m-d H:i:s',$arr_row['last_modify']),
            'skus'=>$str_skus,
            'brief'=>$arr_row['brief'],
        );
    }
    
}
