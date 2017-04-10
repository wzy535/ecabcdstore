<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_apiv_apis_request_member_point_get extends b2c_apiv_extends_request
{
    var $method = 'store.point.get';
    var $callback = array();
    var $title = '会员积分查询接口';
    var $timeout = 1;
    var $async = false;

    public function get_params($member_id)
    {
        $data = array();
        $data['member_id'] = app::get('b2c')->model('members')->get_crm_member_id($member_id);
        return $data;
    }
}
