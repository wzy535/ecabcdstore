<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_tasks_member_point_referrals extends base_task_abstract implements base_interface_task{

    function exec($params=null){
        $register_record = app::get('referrals')->model('register_record');
        $b2c_members_model = app::get('b2c')->model('members');
        $result=$register_record->getList('register_id',array('reference_id'=>$params['member_id']));
        $recommended_member_ids=array();
        foreach($result as $val){
            if($val['register_id']){
                $crm_member_id = $b2c_members_model->getRow('crm_member_id',array('member_id'=>$val['register_id']));
                if(!empty($crm_member_id['crm_member_id'])){
                    $recommended_member_ids[] = $crm_member_id['crm_member_id'];
                }
            }
        }

        if(count($recommended_member_ids)){
            $data = array(
                'referee_member_id'=>$params['crm_member_id'],
                'recommended_member_ids'=>json_encode($recommended_member_ids)
            );
            $member_rpc_obj = kernel::service('b2c_member_rpc_sync');
            $member_rpc_obj->modifyRecommendActive($data);
        }
    }
}


