<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_integration_center(&$setting,&$render){
    $filter = array(
        'marketable'=>'true',
        'goods_type' => array('gift','normal'),
        'to_time|than' => time()
    );

    $o_gift_ref = app::get('gift')->model('ref');
    $arr_gift_list = $o_gift_ref->get_list_finder('*', $filter, 0,$setting['limit']);

    $o = app::get('gift')->model('goods');        //商品类实例
    if( is_array($arr_gift_list) ) {
        foreach( $arr_gift_list as $key => &$row ) {
            $arr = $o->getList( 'image_default_id',array('goods_id'=>$row['goods_id'],'goods_type'=>array('normal','gift')) );
            if( is_array($arr) ) {
                reset( $arr );
                $adsf = current( $arr );
                if(!$adsf['image_default_id']){
                    $imageDefault = app::get('image')->getConf('image.set');
                    $adsf['image_default_id'] = $imageDefault['M']['default_image'];
                }
                $tmp = $row;
                $row = $adsf;
                $row['gift'] = $tmp;
            } else {
                unset($arr_gift_list[$key]);
            }
        }
    }
    //echo "<pre>";print_r($arr_gift_list);
    return $arr_gift_list;


}
