<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_apiv_apis_request_member_signin_change extends b2c_apiv_extends_request
{
    var $method = 'store.member.signin';
    var $callback = array();
    var $title = '推送会员签到接口';
    var $timeout = 1;
    var $async = false;

    public function get_params($params)
    {
        $data = array();

        if( $params['member_id']){
            $data['member_id'] = app::get('b2c')->model('members')->get_crm_member_id($params['member_id']);
            $data['signin_time'] = $params['expiretime'];
//            $data['signin_date'] = 0;
        }
        return $data;
    }
}
