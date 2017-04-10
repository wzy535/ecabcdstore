<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_apiv_apis_request_member_point_getlog extends b2c_apiv_extends_request
{
    var $method = 'store.pointlog.get';
    var $callback = array();
    var $title = '获取会员积分日志接口';
    var $timeout = 1;
    var $async = false;

    public function get_params($sdf)
    {
        $data = array();
        $data['member_id'] = app::get('b2c')->model('members')->get_crm_member_id($sdf['member_id']);
        $data['page_size'] = $sdf['page_size'];
        $data['page'] = $sdf['page'];
        return $data;
    }
}
