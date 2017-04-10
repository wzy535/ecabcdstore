<?php

class importexport_data_b2c_analysis_advance {
    public function getIdFilter($filter){
        return $filter;
    }
    public function get_title(){
        $title = array(
            'log_id'        => '日志id(log_id)',
            'member_id'     => '用户名(member_id)' ,
            'local'         => '登陆名（local）' ,
            'mobile'        => '手机（mobile）' ,
            'email'         => '邮箱（email）' ,
            'money'         => '出入金额(money)' ,
            'message'       => '管理备注(message)' ,
            'mtime'         => '交易时间(mtime)' ,
            'payment_id]'   => '支付单号(payment_id)' ,
            'order_id'      => '订单号(order_id)' ,
            'paymethod'     => '支付方式(paymethod)' ,
            'memo'          => '业务摘要(memo)' ,
            'import_money'  => '存入金额(import_money)' ,
            'explode_money' => '支出金额(explode_money)' ,
            'member_advance'=> '当前余额(member_advance)' ,
            'shop_advance'  => '商店余额(shop_advance)' ,
            'disabled'      => '失效(disabled)' ,
        );
        return $title;
    }
    public function get_content_row($row){
        $content = array(
            'log_id'        => null,
            'member_id'     => null,
            'local'         => null,
            'mobile'        => null,
            'email'         => null,
            'money'         => null,
            'message'       => null,
            'mtime'         => null,
            'payment_id'    => null,
            'order_id'      => null,
            'paymethod'     => null,
            'memo'          => null,
            'import_money'  => null,
            'explode_money' => null,
            'member_advance'=> null,
            'shop_advance'  => null,
            'disabled'      => null,
        );
        $content = array_merge($content,$row);
        $obj_account = app::get('pam')->model('members');
        $member_name = $obj_account->getlist('login_account,login_type', array('member_id'=>$row['member_id']));

        foreach($member_name as $val){
            if($val['login_type'] == 'local'){
                $content['local'] = $val['login_account'];
            }
            if($val['login_type'] == 'mobile'){
                $content['mobile'] = $val['login_account'];
            }
            if($val['login_type'] == 'email'){
                $content['email'] = $val['login_account'];
            }
        }

        if(!empty($content['paymethod'])){
            $paymethod = app::get('ectools')->model('payment_cfgs');
            $paymethod_list = $paymethod->getPaymentInfo($content['paymethod']);
            $content['paymethod'] = $paymethod_list['app_display_name'];
        }

        $data[0] = $content;
        return $data;
    }
}
