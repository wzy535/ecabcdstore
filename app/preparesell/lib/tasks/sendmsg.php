<?php
class preparesell_tasks_sendmsg extends base_task_abstract implements base_interface_task{

    public function exec($params=null){
        $sdf=array(
            'sendMethod'=>'b2c_messenger_msgbox',
            'tmpl_name'=>'messenger:b2c_messenger_msgbox/prepare-reminded',
            'type'=>'prepare-reminded',
            'sendType'=>'notice',
        );

        if($params && is_array($params)){
            foreach($params as $value){
                $sdf['target'] = $value['member_id'];
                $sdf['data'] = array(
                    'goodsname'=>$value['goodsname'],
                    'goodsurl'=>$value['goodsurl'],
                    'begin_time_final'=>date('Y-m-d H:i',$value['begin_time_final']),
                    'email'=>$value['goal'],
                    'shopname'=>app::get('site')->getConf('site.name'),
                );
                app::get('b2c')->model('member_messenger')->queue_send($sdf);
            }
        }
    }
}

