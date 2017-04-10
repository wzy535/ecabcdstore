<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_apiv_apis_response_items_sku
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
     * 获取货品信息
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get(&$sdf, &$thisObj)
    {
        $skus = array();
        $sdf['sku_id'] = floatval($sdf['sku_id']);
        //$sdf['iid'] = floatval($sdf['iid']);
        if(!$sdf['sku_id']){
            $thisObj->send_user_error(app::get('b2c')->_('参数 sku_id 不能为空！'), array('item'=>''));
        }

        $obj_product = $this->app->model('products');
        $arr_product = $obj_product->getList('*', array('product_id'=>$sdf['sku_id']));
        if( ! $arr_product){
            $thisObj->send_user_error(app::get('b2c')->_($sdf['sku_id'].' 对应的商品SKU不存在！'), array('item'=>''));
        }
        
        foreach($arr_product as $arr){        
            /** 组成返回数组 **/
            $str_skus_properties = '';
            $arr_spec_desc = $arr['spec_desc'];
            if($arr_spec_desc['spec_value_id'])
            {
                foreach ($arr_spec_desc['spec_value_id'] as $spec_id_key => $arr_value)
                    $str_skus_properties .= $spec_id_key.":".$arr_value . '_' . $arr_spec_desc['spec_value'] [$spec_id_key]. ";";
            }
            if ($str_skus_properties)
                $str_skus_properties = substr($str_skus_properties, 0, strlen($str_skus_properties)-1);

            $skus = array(
                'sku_id'=>$arr['product_id'],
                'iid'=>$arr['goods_id'],
                "num_iid"=>$arr['goods_id'],
                'bn'=>$arr['bn'],
                'outer_id'=>$arr['bn'],
                'properties'=>$arr['spec_info'],
                'quantity'=>$arr['store'],
                'weight'=>$arr['weight'],
                'price'=>$arr['price'],
                'market_price'=>$arr['mktprice'],
                'modified'=>$arr['last_modify'],
                'cost'=>$arr['cost'],
                "approve_status"=> ($arr['marketable']=='false') ? "instock" : "onsale",
            );
        }
        
        return array('sku'=>($skus));
    }

}
