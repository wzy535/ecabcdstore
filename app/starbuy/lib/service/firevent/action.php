<?php
class starbuy_service_firevent_action {
    function get_type(){
        $actions = array(
            'special-reminded'=>array('label'=>app::get('starbuy')->_('团购开团提醒'),'level'=>9,'b2c_messenger_sms'=>'true','sendType'=>'notice','varmap'=>app::get('starbuy')->_('商品名称').'&nbsp;<{$goodsname}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('starbuy')->_('开售时间').'&nbsp;<{$begin_time}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('starbuy')->_('商品链接').'&nbsp;<{$goodsurl}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('starbuy')->_('店铺名称').'&nbsp;<{$shopname}>'),
        );
        return $actions;
    }

}
