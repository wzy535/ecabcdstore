<?php
/**
 * ShopEx licence
 * 会员积分接口请求crm路由器
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_apiv_exchanges_request_member_signin extends b2c_apiv_exchanges_request
{

    //积分日志推送到crm
    public function changeActive($params){
        if($params){
            $result = $this->rpc_caller_request($params, 'signinchange');
        }
    }

}
