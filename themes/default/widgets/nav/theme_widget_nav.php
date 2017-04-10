<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
function theme_widget_nav($setting, &$smarty){

    define('IN_SHOP',true);

    $result = app::get('site')->model('menus')->select()->where('hidden = ?', 'false')->order('display_order ASC')->instance()->fetch_all();

    $setting['max_leng'] = $setting['max_leng'] ? $setting['max_leng'] : 7;
    $setting['showinfo'] = $setting['showinfo'] ? $setting['showinfo'] : app::get('b2c')->_("更多");

    return $result;
}
?>
