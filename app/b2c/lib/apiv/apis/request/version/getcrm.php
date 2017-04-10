<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_apiv_apis_request_version_getcrm extends b2c_apiv_extends_request
{
    var $method = 'store.sysinfo.version';
    var $callback = array();
    var $title = 'CRM版本查询接口';
    var $timeout = 1;
    var $async = false;

    public function get_params($arr)
    {
        $data = array();
        return $data;
    }
}
