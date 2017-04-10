<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class trustlogin_ctl_trustlogin_cfg extends desktop_controller{


    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $this->finder('trustlogin_mdl_trustlogin_cfg',array(
                'title'=>app::get('trustlogin')->_('信任登陆配置'),
                'use_buildin_recycle'=>false,
                'use_view_tab'=>true,
                'actions'=>array(
                    array(
                        'label'=>app::get('trustlogin')->_('全局配置'),
                        'target'=>'dialog::{ title:\''.app::get('trustlogin')->_('信任登陆全局配置').'\', width:400, height:200}',
                        'href'=>'index.php?app=trustlogin&ctl=trustlogin_cfg&act=add_rule',
                    ),
                ),
            )
        );
    }

    public function add_rule()
    {
        $data = app::get('trustlogin')->getConf('trustlogin_rule');
        $this->pagedata['data'] = $data;
        $this->display('rule.html');
    }
    public function saveRule()
    {
        $data = $_POST;
        $this->begin();
            app::get('trustlogin')->setConf('trustlogin_rule', $data['data']);
        $this->end(true, app::get('trustlogin')->_("设置成功！"));
    }
    public function setting()
    {
        $postdata = $_GET;
        $lib = kernel::single($postdata['p'][0]);
        $this->pagedata['data'] = $lib->get_setting();

        $this->pagedata['libclass'] = $postdata['p'][0];
        $this->display($lib->view, $lib->app_name);

    }
    //保存配置信息
    public function saveCfg()
    {
        $postdata = $_POST;
        $lib = kernel::single($postdata['libclass']);
        $this->begin();
            $lib->set_setting($postdata);
        $this->end(true, app::get('trustlogin')->_("设置成功！"));
    }

}
