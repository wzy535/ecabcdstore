<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class referrals_ctl_site_member extends b2c_ctl_site_member
{
    /**
     * 构造方法
     * @param object application
     */
    public function __construct(&$app)
    {
        $this->app_current = $app;
        $this->app_b2c = app::get('b2c');
        parent::__construct($this->app_b2c);
    }

    public function register()
    {
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $setting = app::get('referrals')->getConf('register_rule');

        if(!is_array($setting)){
            $this->end(false, app::get('referrals')->_("推荐注册应用不存在！"));
        }
        $this->pagedata['setting']=$setting;
        $member_id = $this->app_b2c->member_id;
        $obj_policy = kernel::service("referrals.member_policy");
        if(!is_object($obj_policy)){
            $this->end(false, app::get('referrals')->_("推荐注册应用不存在！"));
        }
        $this->pagedata['setting']= $setting;
        $code = $obj_policy->create_code($member_id);
        $url=$this->creat_url($code);
        $this->pagedata['code_image_id']=kernel::single('weixin_qrcode')->store($url);
        $this->output('referrals');
    }

    public function creat_url($code)
    {
        $wap_status = app::get('wap')->getConf('wap.status');
        if($wap_status == 'false'){

            $url=$this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'signup','full'=>1)).'?referrals_code='.$code;
        }else{

            $url=strtolower(kernel::request()->get_schema()).'://'.kernel::request()->get_host().kernel::single('wap_frontpage')->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'signup')).'?referrals_code='.$code;
        }
        return $url;
    }

}
