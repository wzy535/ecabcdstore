<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class starbuy_tasks_specialremind extends base_task_abstract implements base_interface_task{

    public function exec($params=null){
        $special_remind_mdl = app::get('starbuy')->model('special_remind');
        $special = $special_remind_mdl->getList("*",array('remind_time|sthan'=>time()));
        if($special){
            foreach($special as $key=>$val){
                if($val['member_id' == 0] && $val['remind_way'] == "msgbox"){
                    continue;
                }
                if($val['remind_way'] == "email" || $val['remind_way'] == "msgbox"){
                    $val['goodsurl'] = app::get('site')->router()->gen_url(array('app'=>'starbuy','ctl'=>'site_team','act'=>'index','arg0'=>$val['product_id'],'full'=>true,));
                }
                $sdf[$val['remind_way']][] = $val;
            }
            foreach($sdf as $key=>$value){
                if($key == "email"){
                    $class = "starbuy_tasks_sendemail";
                    system_queue::instance()->publish($class, $class, $value);
                }elseif($key == "sms"){
                    $class = "starbuy_tasks_sendsms";
                    system_queue::instance()->publish($class, $class, $value);
                }elseif($key == "msgbox"){
                    $class = "starbuy_tasks_sendmsg";
                    system_queue::instance()->publish($class, $class, $value);
                }
                //app::get('starbuy')->model('special_remind')->delete($value);
            }
            foreach($special as $key=>$val){
                app::get('starbuy')->model('special_remind')->delete(array('remind_id'=>$val['remind_id']));
            }

        }
    }

}
