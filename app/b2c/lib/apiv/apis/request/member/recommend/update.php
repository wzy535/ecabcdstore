<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_apiv_apis_request_member_recommend_update extends b2c_apiv_extends_request
{
    var $method = 'store.members.update_recommend';
    var $callback = array();
    var $title = '推荐关系绑定接口';
    var $timeout = 1;
    var $async = false;

    public function get_params($data)
    {
        $params['referee_member_id'] = $data['referee_member_id'];
        $params['recommended_member_ids'] = $data['recommended_member_ids'];

        return $params;
    }
}
