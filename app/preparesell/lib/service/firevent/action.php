<?php
class preparesell_service_firevent_action {
    function get_type(){
        $actions = array(
            'prepare-reminded'=>array('label'=>app::get('preparesell')->_('预售尾款支付提醒'),'level'=>10,'b2c_messenger_sms'=>'true','sendType'=>'notice','varmap'=>app::get('preparesell')->_('商品名称').'&nbsp;<{$goodsname}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('preparesell')->_('尾款支付开售时间').'&nbsp;<{$begin_time_final}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('preparesell')->_('商品链接').'&nbsp;<{$goodsurl}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('preparesell')->_('店铺名称').'&nbsp;<{$shopname}>'),
        );
        return $actions;
    }

}
