<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class trustlogin_finder_trustlogin_cfg{

    /**
     * @var string 操作列名称
     */
    var $column_control = '配置';

    /**
     * 配置列显示的html
     * @param array 该行的数据
     * @return string html
     */
    function column_control($row){
        //echo '<pre>';print_r($row);exit();
        return '<a target="dialog::{width:0.6,height:0.7,title:\'信任登陆配置\'}" href="index.php?app=trustlogin&ctl=trustlogin_cfg&act=setting&p[0]='.$row['app_class'].'&finder_id='. $_GET['_finder']['finder_id'] . '">'.app::get('trustlogin')->_('配置').'</a>';
    }

}
