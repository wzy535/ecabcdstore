<?php
class apiactionlog_router_logging{

    private static $_res_service=array(
        'store.trade.add'=>'b2c_apiv_apis_request_order_create',
        'store.trade.update'=>'b2c_apiv_apis_request_order_update',
        'store.trade.aftersale.add'=>'b2c_apiv_apis_request_order_aftersales',
        'store.trade.refund.add'=>'apiactionlog_router_refund',//退款单创建是直接post请求到矩阵，没有走api2.0机制，因此log需要单独创建文件来记录
        'store.user.add'=>'b2c_apiv_apis_request_member_create',
        'store.user.update'=>'b2c_apiv_apis_request_member_update',
        'store.point.update'=>'b2c_apiv_apis_request_member_point_change',
        'store.pointlog.get'=>'b2c_apiv_apis_request_member_point_getlog',
        'store.point.get'=>'b2c_apiv_apis_request_member_point_get',
        'store.member.signin'=>'b2c_apiv_apis_request_member_signin_change',
        'store.sysinfo.version'=>'b2c_apiv_apis_request_version_getcrm',
        'store.point.update_by_parent_code'=>'b2c_apiv_apis_request_member_point_changebyparentcode',
        'store.members.update_recommend'=>'b2c_apiv_apis_request_member_recommend_update',
    );

    public function save_log($service,$method,$data){
        $logData = $this->get_response_log($service, $method, $data);
        if( $logData ){
            $api_mdl = app::get('apiactionlog')->model('apilog');
            $result = $api_mdl->save($logData); 
            return $result;
        }
    }

    private function get_response_log($service, $method, $data){
        if( !empty($data['params']['order_bn']) ){
            $original_bn = $data['params']['order_bn'];
        }

        $api_module = app::get('base')->getConf($service.'.'.$method);
        if( empty($api_module) ) return false;
        $task_name = $api_module['title'];
        $params = array(
            'original_bn' => $original_bn ? $original_bn : '',
            'task_name' => trim($task_name) ? trim($task_name) : $_REQUEST['method'],
            'status' => 'running',
            'log_type'=> $api_module['apiType'],
            'createtime'=>time(),
            'last_modified'=>time(),
        );
        $logData = array_merge($data,$params);
        return $logData;
    }

    public function request_log($method,$params,$rpc_id){
        $class = isset(self::$_res_service[$method]) ? self::$_res_service[$method] : '';
        $api_mdl = app::get('apiactionlog')->model('apilog');
        if($class){
            $obj = kernel::single($class);
            $title = $obj->get_title();
            $time = time();
            $original_bn = $params['tid'];
            if(is_null($rpc_id)){
                $microtime = utils::microtime();
                $rpc_id = str_replace('.','',strval($microtime));
                $randval = uniqid('', true);
                $rpc_id .= strval($randval);
                $rpc_id = md5($rpc_id);
                $data = array(
                    'apilog'=>$rpc_id,
                    'calltime'=>$time,
                    'params'=>$params,
                    'api_type'=>'request',
                    'msg_id'=>'',
                    'worker'=>$method,
                    'original_bn'=>$original_bn,
                    'task_name'=>$title,
                    'log_type'=>'order',
                    'createtime'=>$time,
                    'last_modified'=>$time,
                    'retry'=>$retry?$retry:0,
                );

            }else{
                $arr_pk = explode('-', $rpc_id);
                $rpc_id = $arr_pk[0];
                $tmp = $api_mdl->getList('*', array('apilog'=>$rpc_id));
                if($tmp && $tmp[0]['status'] !='sending'){
                    $retry =$tmp[0]['retry']+1;
                }
                $data = array(
                    'apilog_id'=>$tmp[0]['apilog_id'],
                    'apilog'=>$rpc_id,
                    'calltime'=>$time,
                    'api_type'=>'request',
                    'worker'=>$method,
                    'original_bn'=>$original_bn,
                    'task_name'=>$title,
                    'log_type'=>'order',
                    'createtime'=>$time,
                    'last_modified'=>$time,
                );

            }
            $result = $api_mdl->save($data); 
            $rpc_id = $rpc_id."-".$time;
            return $rpc_id;

        }

    }

    function update($data,$id,$time){
        $api_mdl = app::get('apiactionlog')->model('apilog');
        $api_mdl->update($data,array('apilog'=>$id,'calltime'=>$time,'api_type'=>'request'));
        return true;
    }
}
