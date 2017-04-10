<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/*基础配置项*/
$setting['author']='chenzhiqing@shopex.cn';
$setting['version']='v1.0';
$setting['name']='推荐商品';
$setting['order']=0;
$setting['stime']='2013-11';
$setting['catalog']='商品相关';
$setting['description'] = '展示模板使用的推荐商品挂件';
$setting['userinfo'] = '';
$setting['usual']    = '1';
$setting['tag']    = 'auto';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
/*初始化配置项*/
$setting['selector'] = 'filter';
$setting['limit'] = 2;
$setting['block_name'] = '推荐商品';
?>
