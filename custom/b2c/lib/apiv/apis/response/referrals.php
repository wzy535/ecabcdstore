<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * b2c referrals interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_apiv_apis_response_referrals
{

    //接收推荐设置接口
    public function update($params, &$service){
        $result = 'success';

        logger::info('referrals-params:'.var_export($params,true));
        $status = $params['status'];
        $points = $params['points'];

        $obj_policy = kernel::service("referrals.member_policy");
        if(!is_object($obj_policy)){
            return $service->send_user_error('8005', '推荐注册应用不存在！');
        }
        if( !$obj_policy->crm_save($params['points'])){
            return $service->send_user_error('8004', '保存错误');
        }
        return $result;
    }

}
