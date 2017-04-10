<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/*基础配置项*/
$setting['author']='zinkwind@gmail.com';
$setting['version']='v1.0';
$setting['name']='商品销售排行榜';
$setting['order']=0;
$setting['stime']='2012-08';
$setting['catalog']='商品相关';
$setting['description'] = '展示模板使用的首页销售排行榜挂件';
$setting['userinfo'] = '';
$setting['usual']    = '1';
$setting['tag']    = 'auto';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
/*初始化配置项*/
$setting['selector'] = 'filter';
$setting['limit'] = 5;
$setting['block_name'] = '本类销售排行榜';
?>
