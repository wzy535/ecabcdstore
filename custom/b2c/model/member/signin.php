<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class b2c_mdl_member_signin extends dbeav_model{
    /**
     * @ 判断会员是否签到
     * @access public
     * @param $member_id 会员id
     * @param $date 签到日期
     * @return bool 是否已经签到
     */
    public function exists_signin($member_id,$date){
        $signin = $this->getRow('signin_time',array('member_id'=>$member_id,'signin_date'=>$date));
        if($signin['signin_time'])
        {
            return true;
        }else
        {
            return false;
        }
    }
}
