<?php
class preparesell_tasks_preparemind extends base_task_abstract implements base_interface_task {
    public function exec($params=null)
    {
        $prepare_order_mdl=app::get('preparesell')->model('prepare_order');
        $prepare_order=$prepare_order_mdl->getList("*",array('is_send'=>0));
    	//error_log(print_r($prepare_order,1),3,DATA_DIR.'/1.LOG');
        if($prepare_order)
    	{  
            $timenow = time();
            foreach ($prepare_order as $key => $val) {
                if($val['remind_time_send'] < $timenow)
                {
                    foreach ($val['remind_way'] as  $v) {
                         $sdf[$v][] = $prepare_order[$key];
                    }
                    $order_id[] = $val['order_id'];
                }
                
            }
            //error_log(print_r($order_id,1),3,DATA_DIR.'/1.LOG');
            foreach($sdf as $key=>$value){
                if($key == "email"){
                    $class = "preparesell_tasks_sendemail";
                    system_queue::instance()->publish($class, $class, $value);
                }
                if($key == "sms"){
                    $class = "preparesell_tasks_sendsms";
                    system_queue::instance()->publish($class, $class, $value);
                }
                if($key == "msgbox"){
                    $class = "preparesell_tasks_sendmsg";
                    system_queue::instance()->publish($class, $class, $value);
                }
                //error_log(print_r($value,1),3,DATA_DIR.'/3.LOG');
                //$prepare_order->update($value);
            }
            $prepare_order_mdl->update(array('is_send'=>1),array('order_id'=>$order_id));
        }
    }
}
