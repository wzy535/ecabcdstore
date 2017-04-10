<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class weixin_openid
{
    //目前这里只是为了微信支付写的，所以只是存session的。
    //如需其它用途，请继续开发
    public function check($return_url, &$msg)
    {
        $openid = $this->get_openid_by_session($msg);
        if($openid == false)
        {
            return $this->gen_openid_for_wxpay($return_url, $msg);
        }
        return true;
    }

    //生成openid的地方
    public function gen_openid_for_wxpay($return_url, &$msg)
    {
        $wxpayjsapi_conf = app::get('ectools')->getConf('weixin_payment_plugin_wxpayjsapi');
        $wxpayjsapi_conf = unserialize($wxpayjsapi_conf);
        if(!$_GET['code'])
        {
            $appId_to_get_code = trim($wxpayjsapi_conf['setting']['appId']);
            kernel::single('weixin_wechat')->get_code($appId_to_get_code, $return_url);
        }else{
            $code = $_GET['code'];
            $openid = kernel::single('weixin_wechat')->get_openid_by_code($wxpayjsapi_conf['setting']['appId'], $wxpayjsapi_conf['setting']['Appsecret'], $code);
            if($openid == null)
            {
                $msg = "获取openid失败";
                return false;
            }
            $this->set_openid_by_session($openid);
            return true;
        }
    }

    //在session中保存openid
    public function set_openid_by_session($openid)
    {
        kernel::single('base_session')->start();
        $_SESSION['wechat_openid'] = $openid;
        kernel::single('base_session')->close();
        return true;
    }

    //从session中读取openid
    public function get_openid_by_session(&$msg)
    {
        kernel::single('base_session')->start();
        $openid = $_SESSION['wechat_openid'];
        kernel::single('base_session')->close();

        //验证取到的openid是否为空
        if( is_null($openid) || $openid == '')
        {
            $msg = "session中未保存openid";
            return false;
        }
        else
        {
            return $openid;
        }
    }
}
