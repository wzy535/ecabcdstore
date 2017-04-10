
<?php
class trustlogin_plugin_taobao implements trustlogin_interface_trust
{
    public $name = '淘宝网';
    public $app_name = 'trustlogin';
    public $ver = '2.0';
    public $view = 'trust/taobao.html';

    public $dialog_url = ' https://oauth.taobao.com/authorize';
    public $token_url = 'https://oauth.taobao.com/token';

    public function __construct($app)
    {
        $this->my_url = kernel::openapi_url('openapi.trustlogin_api/parse/' . $this->app->app_id . '/trustlogin_plugin_taobao', 'callback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->my_url, $matches)){
            $this->my_url = str_replace('http://','',$this->my_url);
            $this->my_url = preg_replace("|/+|","/", $this->my_url);
            $this->my_url = "http://" . $this->my_url;
        } else {
            $this->my_url = str_replace('https://','',$this->my_url);
            $this->my_url = preg_replace("|/+|","/", $this->my_url);
            $this->my_url = "https://" . $this->my_url;
        }
        $this->app = $app;
        $this->obj_session = kernel::single('base_session');
        $this->obj_session->start();
        $this->back_url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'login','full'=>1));
    }

    public function set_setting($data)
    {
       return app::get('trustlogin')->setConf('trustlogin_plugin_taobao', $data['data']);
    }

    public function get_setting()
    {
        $data = app::get('trustlogin')->getConf('trustlogin_plugin_taobao');
        return $data;
    }

    //获取appkey
    public function get_appkey()
    {
        $data = app::get('trustlogin')->getConf('trustlogin_plugin_taobao');
        return $data['appKey'];
    }
    //获取appSecret
    public function get_appSecret()
    {
        $data = app::get('trustlogin')->getConf('trustlogin_plugin_taobao');
        return $data['appSecret'];
    }

    //获取图表和链接
    public function get_logo()
    {
        $_SESSION['taobaost'] = md5(uniqid(rand(), TRUE));
        $status = app::get('trustlogin')->getConf('trustlogin_plugin_taobao');
        $data['status'] = $status['status'];
        $data['image'] = $this->app->res_url.'/taobao.png';
        $data['url'] = $this->dialog_url.'?client_id='.$this->get_appkey()."&redirect_uri=" . urlencode($this->my_url)."&state=".$_SESSION['taobaost']."&response_type=code";
        return $data;
    }

    public function callback($data)
    {
        if($data['state'] == $_SESSION['taobaost'])
        {
            if ($data['error'])
            {
               echo "<script>top.window.location='".$this->back_url."'</script>";
               exit;
            }
            $params['grant_type'] = 'authorization_code';
            $params['client_id'] = $this->get_appkey();
            $params['client_secret'] = $this->get_appSecret();
            $params['redirect_uri'] = $this->my_url;
            $params['code'] = $data['code'];
            $params['state'] = $_SESSION['taobaost'];
            $res = kernel::single('base_httpclient')->post($this->token_url,$params);
            $result = json_decode($res,true);
            $result['taobao_user_nick'] = urldecode($result['taobao_user_nick']);
            if ($data['error'])
            {
               echo "<h3>error:</h3>" . $data['error'];
               echo "<h3>msg  :</h3>" . $data['error_description'];
               exit;
            }
            if($result['taobao_user_id'])
            {
                $userdata = $this->getUserInfo($result);
                $datainfo = array(
                    'rsp'=>'succ',
                    'data'=>$userdata,
                    'type'=>'pc',
                );
                return $datainfo;
            }
            else
            {
                echo("参数错误！");
                exit;
            }
           
        }
        else
        {
            echo("The state does not match. You may be a victim of CSRF.");
        }
    }
    
    public function getUserInfo($userinfo)
    {
        $userdata['openid'] = $userinfo['taobao_user_id'];
        $userdata['realname'] = $userinfo['taobao_user_nick'];
        $userdata['nickname'] = $userinfo['taobao_user_nick'];
        $userdata['avatar'] = $userinfo['avatar']?$userinfo['avatar']:' ';
        $userdata['url'] = $userinfo['profile_image_url']?$userinfo['profile_image_url']:' ';
        //$userdata['birthday'] = $userinfo['year'];
        $userdata['gender'] = $userinfo['gender']?$userinfo['gender']:' ';
        $userdata['address'] = $userinfo['location']?$userinfo['location']:' ';
        $userdata['province'] = $userinfo['province']?$userinfo['province']:' ';
        $userdata['city'] = $userinfo['city']?$userinfo['city']:' ';
        return $userdata;
    }

}