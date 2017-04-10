<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_apiv_apis_request_member_point_changebyparentcode extends b2c_apiv_extends_request
{
    var $method = 'store.point.update_by_parent_code';
    var $callback = array();
    var $title = '根据推荐码更新积分接口';
    var $timeout = 1;
    var $async = false;

    public function get_params($params)
    {
        $data = array();

        $data['register_crm_member_id'] = $params['register_crm_member_id'];
        $data['parent_code'] = $params['parent_code'];
        $data['point'] = $params['point'];

        return $data;
    }
}
