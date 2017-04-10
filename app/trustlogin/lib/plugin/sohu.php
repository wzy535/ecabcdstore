
<?php
class trustlogin_plugin_sohu implements trustlogin_interface_trust
{
    public $name = '搜狐网';
    public $app_name = 'trustlogin';
    public $ver = '2.0';
    public $view = 'trust/sohu.html';

    public $dialog_url = 'https://api.sohu.com/oauth2/authorize';
    public $token_url = 'https://api.sohu.com/oauth2/token';
    public $user_url = 'https://api.sohu.com/rest/pp/prv/1/user/get_info';

    public function __construct($app)
    {
        $this->my_url = kernel::openapi_url('openapi.trustlogin_api/parse/' . $this->app->app_id . '/trustlogin_plugin_sohu', 'callback');
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
       return app::get('trustlogin')->setConf('trustlogin_plugin_sohu', $data['data']);
    }

    public function get_setting()
    {
        $data = app::get('trustlogin')->getConf('trustlogin_plugin_sohu');
        return $data;
    }

    //获取appkey
    public function get_appkey()
    {
        $data = app::get('trustlogin')->getConf('trustlogin_plugin_sohu');
        return $data['appKey'];
    }
    //获取appSecret
    public function get_appSecret()
    {
        $data = app::get('trustlogin')->getConf('trustlogin_plugin_sohu');
        return $data['appSecret'];
    }

    //获取图表和链接
    public function get_logo()
    {
        $_SESSION['sohust'] = md5(uniqid(rand(), TRUE));
        $status = app::get('trustlogin')->getConf('trustlogin_plugin_sohu');
        $data['status'] = $status['status'];
        $data['image'] = $this->app->res_url.'/sohu.png';
        $data['url'] = $this->dialog_url.'?client_id='.$this->get_appkey()."&redirect_uri=" . urlencode($this->my_url) . "&state=". $_SESSION['sohust']."&response_type=code";
        return $data;
    }

    public function callback($data)
    {
        if($data['state'] == $_SESSION['sohust'])
        {
            if($data['error'])
            {
               echo "<script>top.window.location='".$this->back_url."'</script>";
               exit;
            }
            $params['grant_type'] = 'authorization_code';
            $params['client_id'] = $this->get_appkey();
            $params['client_secret'] = $this->get_appSecret();
            $params['redirect_uri'] = $this->my_url;
            $params['code'] = $data['code'];
            $res = kernel::single('base_httpclient')->post($this->token_url,$params);
            $result = json_decode($res,true);
            if($result['error'])
            {
               echo "<h3>error:</h3>" . $result['error'];
               echo "<h3>msg  :</h3>" . $result['error_description'];
               exit;
            }
            //通过接口获取用户信息
            $userinfo_url = $this->user_url."?access_token=".$result['access_token'];
            $info  = file_get_contents($userinfo_url);
            $userinfo = json_decode($info,true);
            //echo '<pre>';print_r($userinfo);exit();
            if($userinfo['message'] == 'ok')
            {
                $userdata['openid'] = $result['open_id'];
                $userdata = $this->getUserInfo($userinfo);
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
        $userdata['openid'] = $userinfo['openid']?$userinfo['openid']:' ';
        $userdata['realname'] = $userinfo['data']['uniqname']?$userinfo['data']['uniqname']:' ';
        $userdata['nickname'] = $userinfo['data']['uniqname']?$userinfo['data']['uniqname']:' ';
        $userdata['avatar'] = $userinfo['portrait']?$userinfo['portrait']:' ';
        $userdata['url'] = $userinfo['portrait']?$userinfo['portrait']:' ';
        $userdata['gender'] = $userinfo['sex']?$userinfo['sex']:' ';
        $userdata['address'] = $userinfo['location']?$userinfo['location']:' ';
        $userdata['province'] = $userinfo['province']?$userinfo['province']:' ';
        $userdata['city'] = $userinfo['city']?$userinfo['city']:' ';
        return $userdata;
    }

}