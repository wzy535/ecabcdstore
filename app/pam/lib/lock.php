<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class pam_lock {

    private $_pre_key = 'count_error_times';
    private $_lock_times = 10;
    private $_lock_time = 10800;

    public function increase($username)
    {
        $username = $this->_username2memberid($username);
        $key = $this->_pre_key.$username;
        base_kvstore::instance('login_lock')->fetch($key, $times);
        if($times == null)
        {
            $times = 1;
        }else{
            $times += 1;
        }
        base_kvstore::instance('login_lock')->store($key, $times, $this->_lock_time);
        return $times;
    }

    public function checkusername($username, &$msg)
    {
        $username = $this->_username2memberid($username);
        $key = $this->_pre_key . $username;
        base_kvstore::instance('login_lock')->fetch($key, $times);
        if( $times >= $this->_lock_times )
        {
            $msg = app::get('b2c')->_('该账户因为输入密码次数太多被锁定，请3小时后再试。');
            return false;
        }else{
            return true;
        }
    }

    public function flush_lock($username)
    {
        $key = $this->_pre_key . $username;
        base_kvstore::instance('login_lock')->store($key, 0, $this->_lock_time);
    }

    private function _username2memberid($username)
    {
        $obj_pam = app::get('pam')->model('members');
        $pam = $obj_pam->getRow('member_id',array('login_account'=>$username));
        return $pam['member_id'];
    }
}
