<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class ectools_ctl_payment_list extends desktop_controller{

    var $workground = 'ectools_ctl_payment_list';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    function index(){

        $params = array(

            'title'=>app::get('ectools')->_('收款帐号管理'),

            'actions'=>array(
                array('label'=>app::get('ectools')->_('新增'),'href'=>'index.php?app=ectools&ctl=payment&act=addnew_list'
                      ,'target'=>'dialog::{title:\''.app::get('ectools')->_('新增').'\',width:500,height:290}')
                ),
            );
        $this->finder('ectools_mdl_payment_list',$params);
    }
}