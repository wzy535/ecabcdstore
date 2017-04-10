<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$setting['author']='zinkwind@gmail.com';
$setting['name']='商品虚拟分类';
$setting['version']='135711';

$setting['stime']='2012-10';
$setting['catalog']='辅助信息';

$setting['description']    = '展示模板使用的商品虚拟分类';
$setting['order']='1';
$setting['usual']    = '1';

$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认'),
                            'toggle.html'=>app::get('b2c')->_('折叠式'),
                            'dropdown.html'=>app::get('b2c')->_('弹出式'),
                            'multree.html'=>app::get('b2c')->_('点击折叠')
                        );
?>
