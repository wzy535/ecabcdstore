<?php
/**
 * ShopEx licence
 * 会员积分接口请求crm路由器
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_apiv_exchanges_request_member_point extends b2c_apiv_exchanges_request
{

    //积分日志推送到crm
    public function changeActive($point_id){
        if($point_id){
            $result = $this->rpc_caller_request($point_id, 'pointchange');
            $result = json_decode($result,true);
            if($result['member_id']){
                $member_point_obj = app::get('b2c')->model('member_point');
                $member_point_obj->update(array('status'=>'true'),array('id'=>$point_id));
            }
        }
    }

    //从crm获取积分日志
    public function getlogActive($sdf){
        $data=array();
        if($sdf['member_id']){
            $result = $this->rpc_caller_request($sdf, 'pointgetlog');
            $result = json_decode($result,true);
            foreach((array)$result['point_log_list'] as $key => $pointlog){
                $data['historys'][$key]['expiretime']=strtotime($pointlog['expired_time']);
                $data['historys'][$key]['addtime']=strtotime($pointlog['op_time']);
                $data['historys'][$key]['change_point']=$pointlog['points'];
                $data['historys'][$key]['reason']=$pointlog['point_desc'];
            }
            $data['total'] = $result['total_result'] ? $result['total_result'] : 0 ;
        }

        return $data;
    }
    //从crm查询会员积分
    public function getActive($member_id){
        $data = array();
        if($member_id){
            $result = $this->rpc_caller_request($member_id, 'pointget');
            $result = json_decode($result,true);
            $data['total'] = $result['shop_point_list']['total_point'];
        }

        return $data;
    }
    //根据推荐码更新积分接口
    public function changeByParentcodeActive($data){
        $register_crm_member_id = 0;
        if($data['register_crm_member_id']){
            $register_crm_member_id = $this->rpc_caller_request($data, 'pointchangebyparentcode');
        }

        return $register_crm_member_id;
    }
}
