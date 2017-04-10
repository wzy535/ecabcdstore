<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class trustlogin_passport_trust{


    function get_name(){
        return null;
    }

    function get_login_form($auth, $appid, $view, $ext_pagedata=array()){
        return null;
    }

    function login($usrdata){

        $userPassport = kernel::single('b2c_user_passport');
        if( $userPassport->userObject->is_login() && $usrdata['data']['type'] == 'pc')
        {
            $url = array('app'=>'b2c','ctl'=>'site_member','act'=>'index');
            kernel::single('site_controller')->splash('success',$url,app::get('b2c')->_('您已经是登录状态，不需要重新登录'));
        }
        if( $userPassport->userObject->is_login() && $usrdata['data']['type'] == 'wap')
        {
            $url = array('app'=>'b2c','ctl'=>'wap_member','act'=>'index');
            kernel::single('site_controller')->splash('success',$url,app::get('b2c')->_('您已经是登录状态，不需要重新登录'));
        }
        
        if($usrdata['member_id'] && $usrdata['istrue']=='yes')
        {
            $saveData['pam_account'] = $this->pre_pam_members_data($usrdata['data']);

            $row = app::get('pam')->model('auth')->getRow('auth_id,module_uid,account_id',array('module_uid'=>$saveData['pam_account']['login_account'],'account_id'=>$usrdata['member_id']));
            
            if(!empty($row['auth_id']))
            {
                return $saveData['pam_account']['login_account'];
            }
            else
            {
                $db = kernel::database();
                $db->beginTransaction();
                $authData = array(
                    'account_id'=>$usrdata['member_id'],
                    'module_uid'=>$saveData['pam_account']['login_account'],
                    'module'=>'trustlogin_passport_trust',
                    'data'=>serialize($usrdata['data']['data']),
                );
                $usrdata['data']['data']['member_id']=$usrdata['member_id'];
                if( !app::get('trustlogin')->model('trustinfo')->save($usrdata['data']['data']) ){
                    $db->rollBack();
                    return false;
                }

                if(!app::get('pam')->model('auth')->save($authData) ){
                    $db->rollBack();
                    return false;
                }
                $db->commit();

                return $saveData['pam_account']['login_account'];
            }
        }
        else
        {
            if($usrdata['data']['rsp'] == 'succ')
            {
                $login_name = $this->save_login_data($usrdata);
            }else{
                //提示会是参数错误
                $usrdata['data']['log_data'] = $data['err_msg'];
                $usrdata['data']['login_name'] = false;
            }
            if(!$login_name){
                $usrdata['data']['log_data'] = app::get('b2c')->_('保存失败，请重试');
                $usrdata['data']['login_name'] = false;
            }else{
              $usrdata['data']['login_name'] = $login_name;
            }
            return $usrdata['data']['login_name'];
        }

    }

    function loginout($auth,$backurl="index.php"){
        unset($_SESSION['account'][$this->type]);
        unset($_SESSION['last_error']);
    }
    //设置绑定信息的数据处理方法
    function pre_b2c_members($result)
    {
        $lv_model = app::get('b2c')->model('member_lv');
        $member_lv_id = $lv_model->get_default_lv();
        $data['member_lv_id'] = $member_lv_id;
        $arrDefCurrency = app::get('ectools')->model('currency')->getDefault();
        $data['currency'] = $arrDefCurrency['cur_code'];
        $data['email'] = $result['data']['data']['email'];
        $data['name'] = $result['login_account']?$result['login_account']:' ';
        $data['addr'] = $result['data']['data']['address'];
        $data['sex'] = $this->gender($result['data']['data']['gender']);
        $data['trust_name'] = $result['login_account']?$result['login_account']:' ';
        $data['regtime'] = time();
        return $data;
    }
    //设置绑定信息的数据处理方法
    function pre_pam_members($result)
    {
        $login_name = $result['login_account'];
        $login_password = $result['login_password'];
        $return = array(
            'login_type' => 'local',
            'login_account' => $login_name,
            'login_password' => $this->getPassword($login_password,$login_name,time()),
            'password_account' => $login_name, //登录密码加密账号
            'disabled' =>  'false',
            'createtime' => time() 
        );
        return $return;
    }

    //生成密码只为设置心用户并绑定使用
    /**
    * 获取加密类型后的密文
    * @param string $source_str 加密明文
    * @param string $username 用户名
    * @param time $createtime 当前时间
    * @return string 返回加密密文
    */
    function getPassword($source_str,$username,$createtime)
    {
        $string_md5 = md5(md5($source_str).$username.$createtime);
        $front_string = substr($string_md5,0,31);
        $end_string = 's'.$front_string;
        return $end_string;
    }

    //保存数据到相应的数据表 pam_members  pam_auth trustinfo b2c_members表中
    function save_login_data($result,&$msg){
        if($result['isaleady']!='noaleady')
        {
            $saveData['b2c_members'] = $this->pre_b2c_members_data($result['data']);
            $saveData['pam_account'] = $this->pre_pam_members_data($result['data']);
        }
        else
        {
            $saveData['b2c_members'] = $this->pre_b2c_members($result);
            $saveData['pam_account'] = $this->pre_pam_members($result);
        }
        if(empty($result['data']['data']['realname']))
        {
            $module_uid = $result['data']['data']['nickname'].'_'.$result['data']['data']['openid'];
        }
        else
        {
            $module_uid = $result['data']['data']['realname'].'_'.$result['data']['data']['openid'];
        }
        $row = app::get('pam')->model('auth')->getList('auth_id,module_uid',array('module_uid'=>$module_uid));

        $account = app::get('pam')->model('members')->getList('member_id',array('login_account'=>$saveData['pam_account']['login_account']));
        if($row && $account){//已有信息不用再次保存
          return $module_uid;
        }

        $member_model = app::get('b2c')->model('members');
        $db = kernel::database();
        $db->beginTransaction();
        //保存到b2c members
        if( $member_model->insert($saveData['b2c_members']) ){
            $member_id = $saveData['b2c_members']['member_id'];
            if(!(kernel::single('b2c_user_passport')->save_attr($member_id,$saveData['b2c_members'],$msg))){
                $db->rollBack();
                return false;
            }

            $saveData['pam_account']['member_id'] = $member_id;
            if(!app::get('pam')->model('members')->save($saveData['pam_account'])){
                $db->rollBack();
                return false;
            }

            $authData = array(
              'account_id'=>$member_id,
              'module_uid'=>$module_uid,
              'module'=>'trustlogin_passport_trust',
              'data'=>serialize($result['data']['data']),
            );
            if($row[0]['auth_id']){
              $authData['auth_id'] = $row[0]['auth_id'];
            }
            if( !app::get('pam')->model('auth')->save($authData) ){
                $db->rollBack();
                return false;
            }

            $result['data']['data']['member_id']=$member_id;
            if( !app::get('trustlogin')->model('trustinfo')->save($result['data']['data']) ){
                $db->rollBack();
                return false;
            }

        }else{
            return false;
        }
        $db->commit();

        if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
            $member_rpc_object->modifyActive($member_id);
        }
        foreach(kernel::servicelist('b2c_register_after') as $object) {
            $object->registerActive($member_id);                                                                                                                   
        }

        return $module_uid;
    }
    //绑定现有信息的数据处理方法
    public function pre_b2c_members_data($result){
        $lv_model = app::get('b2c')->model('member_lv');
        $member_lv_id = $lv_model->get_default_lv();
        $data['member_lv_id'] = $member_lv_id;
        $arrDefCurrency = app::get('ectools')->model('currency')->getDefault();
        $data['currency'] = $arrDefCurrency['cur_code'];
        $data['email'] = $result['data']['email'];
        $data['name'] = empty($result['data']['realname']) ? $result['data']['nickname'] : $result['data']['realname'];
        $data['addr'] = $result['data']['address'];
        $data['sex'] = $this->gender($result['data']['gender']);
        $data['trust_name'] = empty($result['data']['realname'])?$result['data']['nickname']:$result['data']['realname'];
        $data['regtime'] = time();
        return $data;
    }
    //绑定现有信息的数据处理方法
    public function pre_pam_members_data($result){
        $data = $result['data'];
        if(empty($data['realname'])){
          $login_name = $data['nickname'].'_'.$data['openid'];
        }else{
          $login_name = $data['realname'].'_'.$data['openid'];
        }

        $return = array(
            'login_type' => 'local',
            'login_account' => $login_name,
            'login_password' => md5(time().$login_name),
            'password_account' => $login_name, //登录密码加密账号
            'disabled' => 'false',
            'loginstyle'=> 'trustlogin',
            'createtime' => time() 
        );
        return $return;
    }
    //男女判别方法
    public function gender($data)
    {
        if($data=='男')
        {
            return '1';
        }
        elseif($data=='女')
        {
            return '2';
        }
        else
        {
            return '0';
        }
    }

}

