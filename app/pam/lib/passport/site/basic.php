<?php

/*
 * 前台登录
 * */
class pam_passport_site_basic
{

    /*
     * 前台用户登录验证方法
     *
     * @params $login_account 登录账号
     * @params $login_password 登录密码
     * @params $vcode 验证码
     * */
    public function login($userData,$vcode=false,&$msg){
        $userData = utils::_filter_input($userData);//过滤xss攻击
        if($vcode && !$this->vcode_verify($vcode) ){
            $msg = app::get('pam')->_('验证码错误');
            return false;
        }
        $account = app::get('pam')->model('members')->getList('member_id',array('login_account' => $userData['login_account']));
        if(!$account){
            $msg = app::get('pam')->_('用户名或密码错误');
            return false;
        }
        //判断该账户登陆是否受到限制
        if( !kernel::single('pam_lock')->checkusername($userData['login_account'], $msg) )
        {
            return false;
        }

        //如果指定了登录类型,则不再进行获取(邮箱登录，手机号登录，用户名登录)
        if(!$userData['login_type']){
            $userPassport = kernel::single('b2c_user_passport');
            $userData['login_type'] = $userPassport->get_login_account_type($userData['login_account']);
        }
        $filter = array('login_type'=>$userData['login_type'],'login_account'=>$userData['login_account']);
        $account = app::get('pam')->model('members')->getList('member_id,password_account,login_password,createtime',$filter);
        if(!$account){
            $msg = $this->password_error($userData);
            return false;
        }
        $login_password = pam_encrypt::get_encrypted_password($userData['login_password'],'member',array('createtime'=>$account[0]['createtime'],'login_name'=>$account[0]['password_account']));

        if($account[0]['login_password'] != $login_password ){
            $msg = $this->password_error($userData);
            return false;
        }
        return $account[0]['member_id'];
    }//end function

    /*
     * 密码错误后的处理方法
     * @params $userData
     * */
    private function password_error($userData){

        $left_times=kernel::single('pam_lock')->increase($userData['login_account']);
        $msg = app::get('pam')->_('用户名或密码错误');
        if($left_times>=7)
        {
            if( $left_times >= 10 )
            {
                $msg = $msg.app::get('pam')->_("由于错误次数太多，账号已被锁定，请3小时后再次登录");
            }else{
                $msg = $msg.app::get('pam')->_(",剩余").(10-$left_times).app::get('pam')->_("次机会");
            }
        }

        return $msg;
    }


    /*
     * 登录验证码验证方法
     *
     * @params $vcode 验证码
     * @return bool
     * */
    public function vcode_verify($vcode){
      if(!base_vcode::verify('b2c',$vcode)){
        return false;
      }
      return true;
    }
}
