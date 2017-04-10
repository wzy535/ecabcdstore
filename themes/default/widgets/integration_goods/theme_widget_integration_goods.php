<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_integration_goods(&$setting,&$render){
    $coupon_exchange_info = app::get('b2c')->model('coupons')->getList('*',array('cpns_status'=>1,'cpns_point|than'=>0,'cpns_type'=>1,'cpns_gen_quantity|than'=>0),0,3,'cpns_id DESC');
    $mSRO = app::get('b2c')->model('sales_rule_order');
    foreach($coupon_exchange_info as $key=>$val){
        $aRule = $mSRO->getList('description', array('rule_id'=>$val['rule_id']));
        $coupon_exchange_info[$key]['description'] = $aRule['0']['description'];
    }
    return $coupon_exchange_info;
}

