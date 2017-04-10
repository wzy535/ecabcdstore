<?php
class trustlogin_ctl_trustlogin_trustwap extends wap_frontpage{

    function __construct(&$app){
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        kernel::single('base_session')->start();
    }
    //绑定已有帐号
    public function check_login()
    {
        $postData = $_POST;
        $params['type'] = $postData['type'];
        $params['module'] = $postData['module'];
        $params['redirect'] = $postData['redirect'];
        $params['data'] = json_decode(urldecode($postData['data']),true);
        if($postData['skip'] != 'jump')
        {
            if(kernel::single('b2c_service_vcode')->status() && empty($postData['verifycode']))
            {
                $msg = app::get('trustlogin')->_('请输入验证码!');
                $this->splash('failed',null,$msg,true);exit;
            }
            if($postData['verifycode'] && !$this->vcode_verify($postData['verifycode']) ){
                $msg = app::get('trustlogin')->_('验证码错误');
                $this->splash('failed',null,$msg,true);exit;
            }
        }
        
        if($postData['alreadytype'] == 'alreadytype')
        {
            $data['login_account'] = $postData['uname'];
            $data['login_password'] = $postData['password'];
            $libPam = kernel::single('pam_passport_site_basic');
            $member_id = $libPam->login($data);
            if($member_id)
            {
                $params['member_id'] = $member_id;
                $params['istrue'] = 'yes';
            }
            else
            {
                $msg = app::get('trustlogin')->_('该用户名或密码错误!');
                $this->splash('failed',null,$msg,true);
            }
        }
        $libapi = kernel::single('trustlogin_api');

        echo $libapi->todecirct($params);
    }
    //第一次登陆 设置并绑定的帐号
    public function set_login()
    {
        $postData = $_POST;
        if(preg_match("/^1[34578]{1}[0-9]{9}$/",trim($postData['pam_account']['login_name'])) )
        {
            $msg = app::get('trustlogin')->_('设置用户名暂时不支持手机号!');
            $this->splash('failed',null,$msg,true);
        }
        if(preg_match('/^(?:[a-z\d]+[_\-\+\.]?)*[a-z\d]+@(?:([a-z\d]+\-?)*[a-z\d]+\.)+([a-z]{2,})+$/i',trim($postData['pam_account']['login_name'])) )
        {
            $msg = app::get('trustlogin')->_('设置用户名暂时不支持邮箱!');
            $this->splash('failed',null,$msg,true);
        }
        if(!preg_match('/^[^\x00-\x2d^\x2f^\x3a-\x3f]+$/i', trim($postData['pam_account']['login_name'])) )
        {
            $msg = $this->app->_('该登录账号包含非法字符');
            return false;
        }
        $params['login_account'] = $postData['pam_account']['login_name'];
        $params['login_password'] = $postData['pam_account']['login_password'];
        $params['psw_confirm'] = $postData['pam_account']['psw_confirm'];

        if($params['login_password'] != $params['psw_confirm'])
        {
            $msg = app::get('trustlogin')->_('两次密码不一样!');
            $this->splash('failed',null,$msg);
            //return $msg;
        }
        if(kernel::single('b2c_service_vcode')->status() && empty($postData['verifycode']))
        {
            $msg = app::get('trustlogin')->_('请输入验证码!');
            $this->splash('failed',null,$msg,true);exit;
        }
        if($postData['verifycode'] && !$this->vcode_verify($postData['verifycode']) ){
            $msg = app::get('trustlogin')->_('验证码错误');
            $this->splash('failed',null,$msg,true);exit;
        }
        $account = app::get('pam')->model('members')->getRow('member_id',array('login_account'=>$params['login_account']));
        if($account['member_id'])
        {
            $msg = app::get('trustlogin')->_('该用户名已经存在!');
            $this->splash('failed',null,$msg);
        }
        $params['type'] = $postData['type'];
        $params['module'] = $postData['module'];
        $params['redirect'] = $postData['redirect'];
        $params['data'] = json_decode(urldecode($postData['data']),true);
        $params['isaleady'] = 'noaleady';
        $libapi = kernel::single('trustlogin_api');

        echo $libapi->todecirct($params);
    }
    //绑定帐号的页面
    public function bind_login()
    {
        $data = $_POST;
        $userinfo = json_decode(urldecode($data['data']),true);
        $realname = $userinfo['data']['nickname'] ? $userinfo['data']['nickname'] : $userinfo['data']['realname'];
        $avatar = $userinfo['data']['avatar'];

        if(!$data)
        {
            $url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index','full'=>1));
            $this->redirect( $url );
        }
         //是否开启验证码
        $this->pagedata['show_varycode'] = kernel::single('b2c_service_vcode')->status();
        $this->pagedata['data'] = $data;
        $this->pagedata['realname'] = $realname;
        $this->pagedata['avatar'] = $avatar;
        $this->set_tmpl('passport');
        $this->page('wapbind.html');
    }
    /*
     * 登录验证码验证方法
     *
     * @params $vcode 验证码
     * @return bool
     * */
    public function vcode_verify($vcode)
    {
      if(!base_vcode::verify('b2c',$vcode)){
        return false; 
      }
      return true;
    }
    //pam登录后处理(保存信任登录返回的信息)
    public function post_login($type){

        if($type=='pc')
        {
            $url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        }
        if($type=='wap')
        {
            $url = app::get('wap')->router()->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'index'));
        }
        $userPassport = kernel::single('b2c_user_passport');
        $member_id = $userPassport->userObject->get_member_id();

        if($member_id){
            $b2c_members_model = app::get('b2c')->model('members');
            $member_point_model = app::get('b2c')->model('member_point');
            $member_data = $b2c_members_model->getList( 'member_lv_id,experience,point', array('member_id'=>$member_id) );

            if(!$member_data){
                $this->splash('failed',null,app::get('b2c')->_('数据异常，请联系客服'));
            }
            $member_data = $member_data[0];
            $member_data['order_num'] = app::get('b2c')->model('orders')->count( array('member_id'=>$member_id) );
            
            if(app::get('b2c')->getConf('site.level_switch')==1 && $member_data['experience'])
            {
                $member_data['member_lv_id'] = $b2c_members_model->member_lv_chk($member_data['member_lv_id'],$member_data['experience']);
            }
            if(app::get('b2c')->getConf('site.level_switch')==0 && $member_data['point'])
            {
                $member_data['member_lv_id'] = $member_point_model->member_lv_chk($member_id,$member_data['member_lv_id'],$member_data['point']);
            }
            
            $b2c_members_model->update($member_data,array('member_id'=>$member_id));

            $this->bind_member($member_id);
            app::get('b2c')->model('cart_objects')->setCartNum();
            $url = $userPassport->get_next_page();
            if(!$url && $type=='pc')
            {
                $url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            }
            if(!$url && $type=='wap')
            {
                $url = app::get('wap')->router()->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'index'));
            }
            $this->splash('success',$url);
        }else{
            $this->splash('failed',kernel::base_url(1),app::get('b2c')->_('参数错误'));
        }
    }
}
?>
