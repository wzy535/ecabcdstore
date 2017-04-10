<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$setting['author']='zinkwind@gmail.com';

$setting['catalog'] = '购物车';
$setting['name']    = '迷你购物车';
$setting['version']='v1.0.0';
$setting['usual']    = '0';
$setting['description']    = '展示模板使用的迷你购物车';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认'),
                        );
                        
$setting['cart_show_type'] = 1;

?>
