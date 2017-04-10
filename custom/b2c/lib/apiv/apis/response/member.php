<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * b2c member interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_apiv_apis_response_member
{

    //初始化会员信息
    public function init($params, &$service){
        $membersModel = app::get('b2c')->model('members');

        //默认分页码为1,分页大小为20
        $params['page_no'] = intval($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = intval($params['page_size']) ? $params['page_size'] : 20;
        $page_no = intval($params['page_no']) - 1;
        $limit  = intval($params['page_size']);
        $offset = $page_no * $limit;

        //返回总数
        $rows = $membersModel->count();
        $data['item_total'] = $rows;

        $membersData = $membersModel->getList('*',array(),$offset,$limit);
        $data['list'] = $this->format_member_data($membersData);
        return $data;
    }

    public function get_member_filter($params, &$service)
    {
        $filter = array();
        if( isset($params['member_lv_ids']) && $params['member_lv_ids'] != null )
        {
            $member_lv_ids = json_decode($params['member_lv_ids']);
            $filter['member_lv_id|in'] = $member_lv_ids;
        }

        if( isset($params['member_regtime_begin']) && $params['member_lv_ids'] != null )
        {
            $member_regtime_begin = $params['member_regtime_begin'];
        }else{
            $member_regtime_begin = 0;
        }

        if( isset($params['member_regtime_end']) && $params['member_regtime_end'] != null )
        {
            $member_regtime_end = $params['member_regtime_end'];
        }else{
            $member_regtime_end = time();
        }
        $filter['regtime|between'] = array($member_regtime_begin, $member_regtime_end);

        $membersModel = app::get('b2c')->model('members');

        $membersData = $membersModel->getList('*', $filter);
        return $this->format_member_data($membersData);
    }

    /**
     *获取到会员等级列表
     */
    public function get_member_lv_list($params,&$service){
        $memberLvModel = app::get('b2c')->model('member_lv');
        $memberLvData = $memberLvModel->getList('*');
        $data = array();
        foreach( (array)$memberLvData as $k=>$row){
            $data[$k]['member_lv_id'] = intval($row['member_lv_id']);
            $data[$k]['name']         = $row['name'];
            $data[$k]['default_lv']   = ($row['default_lv'] == '1') ? 'true' : 'false';
        }
        return $data;
    }

    /**
     * 获取会员优惠券
     */
    public function get_member_coupon($params,&$service){
        if($params['member_id']){
            $member_obj = app::get('b2c')->model('members');
            $oCoupon = kernel::single('b2c_coupon_mem');

            $data = array();
            $crm_member_id = $params['member_id'];
            $member = $member_obj->getRow('member_id,member_lv_id',array('crm_member_id'=>$crm_member_id));
            $member_id = $member['member_id'];
            $member_lv_id = $member['member_lv_id'];
            $aData = $oCoupon->get_list_m($member_id);
            $source = array('a'=>'全体优惠券','b'=>'会员优惠券','c'=>'ShopEx优惠券');

            if ($aData) {
                foreach ($aData as $k => $item) {
                    $data[$k]['memc_code'] = $item['memc_code'];
                    $data[$k]['memc_name'] = $item['coupons_info']['cpns_name'];
                    $data[$k]['memc_source'] = $source[$item['memc_source']];
                    $data[$k]['memc_used'] = 'false';
                    $data[$k]['from_time'] = $item['time']['from_time'];
                    $data[$k]['to_time'] = $item['time']['to_time'];
                    $data[$k]['conditions'] = kernel::single($item['time']['c_template'])->tpl_name;
                    $data[$k]['valid'] = 'false';
                    if ($item['coupons_info']['cpns_status'] !=1) {
                        $data[$k]['memo'] = app::get('b2c')->_('此种优惠券已取消');
                    }

                    $member_lvs = explode(',',$item['time']['member_lv_ids']);
                    if (!in_array($member_lv_id,(array)$member_lvs)) {
                        $data[$k]['memo'] = app::get('b2c')->_('本级别不准使用');
                    }

                    $curTime = time();
                    if ($curTime>=$item['time']['from_time'] && $curTime<$item['time']['to_time']) {
                        if ($item['memc_used_times']<app::get('b2c')->getConf('coupon.mc.use_times')){
                            if ($item['coupons_info']['cpns_status']){
                                $data[$k]['valid'] = 'true';
                                $data[$k]['memo'] = app::get('b2c')->_('可使用');
                            }else{
                                $data[$k]['memo'] = app::get('b2c')->_('本优惠券已作废');
                            }
                        }else{
                            $data[$k]['memc_codememc_used'] = 'true';
                            if($item['disabled'] == 'busy'){
                                $data[$k]['memo'] = app::get('b2c')->_('使用中');
                            }else{
                                $data[$k]['memo'] = app::get('b2c')->_('本优惠券次数已用完');
                            }
                        }
                    }else{
                        $data[$k]['memo'] = app::get('b2c')->_('还未开始或已过期');
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 更新会员签到状态接口
     */
    public function update_member_signin($params,&$service){
        $crm_member_id = $params['member_id'];

        if( $crm_member_id ){
            $member_obj = app::get('b2c')->model('members');
            $member_signin_obj = app::get('b2c')->model('member_signin');

            $member = $member_obj->getRow('member_id',array('crm_member_id'=>$crm_member_id));
            $member_id = $member['member_id'];
            $signin_time = $params['signin_time'];
            $signin_date = date('Y-m-d',$signin_time);

            if(!$member_signin_obj->exists_signin($member_id,$signin_date)){
                $data = array('member_id'=>$member_id,'signin_date'=>$signin_date,'signin_time'=>$signin_time);
                logger::info('signin-data:'.var_export($data,true));
                $member_signin_obj->insert($data);
            }
        }

        return $crm_member_id;
    }
    private function format_member_data($membersData)
    {
        $userPassport = kernel::single('b2c_user_passport');
        $list = array();
        foreach( (array)$membersData as $k=>$row ){
            //获取到用户名，手机号, 邮箱
            $pam_colunms = $userPassport->userObject->get_pam_data('*',$row['member_id']);

            //获取到注册项数据
            $attrData = $userPassport->get_signup_attr($row['member_id']);
            $attr = array();
            foreach( (array)$attrData  as $attr_k=>$attr_colunm){
                $attr[$attr_k]['attr_name'] = $attr_colunm['attr_name'];
                $attr[$attr_k]['attr_column'] = $attr_colunm['attr_column'];
                $attr[$attr_k]['attr_value'] = $attr_colunm['attr_value'];
            }
            $list[$k]['member_id'] = intval($row['member_id']);
            $list[$k]['member_lv_id'] = intval($row['member_lv_id']);
            $list[$k]['login_name'] = $pam_colunms['local'];
            $list[$k]['mobile'] = $pam_colunms['mobile'];
            $list[$k]['email'] = $pam_colunms['email'];
            $list[$k]['reg_ip'] = $row['reg_ip'];
            $list[$k]['regtime'] = $row['regtime'];
            $list[$k]['attr'] = $attr;
            $list[$k]['last_modify'] = '';
        }
        return $list;
    }
}
