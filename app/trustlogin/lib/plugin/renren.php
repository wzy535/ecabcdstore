
<?php
class trustlogin_plugin_renren implements trustlogin_interface_trust
{
    public $name = '人人网';
    public $app_name = 'trustlogin';
    public $ver = '2.0';
    public $view = 'trust/renren.html';

    public $dialog_url = 'https://graph.renren.com/oauth/authorize';
    public $token_url = 'https://graph.renren.com/oauth/token';
    public $user_url = 'https://api.renren.com/v2/user/get';

    public function __construct($app)
    {
        $this->my_url = kernel::openapi_url('openapi.trustlogin_api/parse/' . $this->app->app_id . '/trustlogin_plugin_renren', 'callback');
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
       return app::get('trustlogin')->setConf('trustlogin_plugin_renren', $data['data']);
    }

    public function get_setting()
    {
        $data = app::get('trustlogin')->getConf('trustlogin_plugin_renren');
        return $data;
    }

    //获取appkey
    public function get_appkey()
    {
        $data = app::get('trustlogin')->getConf('trustlogin_plugin_renren');
        return $data['appKey'];
    }
    //获取appSecret
    public function get_appSecret()
    {
        $data = app::get('trustlogin')->getConf('trustlogin_plugin_renren');
        return $data['appSecret'];
    }

    //获取图表和链接
    public function get_logo()
    {
        $status = app::get('trustlogin')->getConf('trustlogin_plugin_renren');
        $data['status'] = $status['status'];
        $data['image'] = $this->app->res_url.'/renren.png';
        $data['url'] = $this->dialog_url.'?client_id='.$this->get_appkey()."&redirect_uri=" .$this->my_url."&response_type=code";
        return $data;
    }

    public function callback($data)
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
        if ($result['error'])
        {
           echo "<h3>error:</h3>" . $result['error'];
           echo "<h3>msg  :</h3>" . $result['error_description'];
           exit;
        }

        $userinfo_url = $this->user_url."?access_token=".$result['access_token']."&userId=".$result['user']['id'];
        $info  = file_get_contents($userinfo_url);

        $userinfo = json_decode($info,true);

        $userdata = $this->getUserInfo($userinfo['response']);

        $datainfo = array(
            'rsp'=>'succ',
            'data'=>$userdata,
            'type'=>'pc',
        );
        return $datainfo;
    }

    public function getUserInfo($userinfo)
    {
        $userdata['openid'] = $userinfo['id'];
        $userdata['realname'] = $userinfo['name'];
        $userdata['nickname'] = $userinfo['screen_name'];
        $userdata['avatar'] = $userinfo['avatar'][0]['url'];
        $userdata['url'] = $userinfo['avatar'][1]['url'];
        //$userdata['birthday'] = $userinfo['year'];
        $userdata['gender'] = $userinfo['basicInformation']['sex'];
        $userdata['address'] = $userinfo['basicInformation']['homeTown'];
        $userdata['province'] = $userinfo['province']?$userinfo['province']:' ';
        $userdata['city'] = $userinfo['city']?$userinfo['city']:' ';
        return $userdata;
    }

}
