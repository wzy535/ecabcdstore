<?php
class logisticstrack_service_hqepay {

    function __construct(){
        $this->app = app::get('logisticstrack');
    }
    #绑定华强宝物流
    public function bind() {
        $app_exclusion = app::get('base')->getConf('system.main_app');
        $token = base_shopnode::get('token',$app_exclusion['app_id']);
        $node_id = base_shopnode::node_id($app_exclusion['app_id']);
        $certi_id = base_certificate::get('certificate_id');
        $api_url = $_SERVER['HTTP_HOST'].kernel::base_url()."/index.php/api";
        $params = array(
            'app' => 'app.applyNodeBind',
            'node_id' => $node_id,
            'from_certi_id' => $certi_id,
            'callback' => '',
            'sess_callback' => '',
            'api_url' => $api_url,
            'node_type' => 'hqepay',
            'to_node' => '1227722633',
            'shop_name' => '物流跟踪',
            "api_key"=> "1236217",
            "api_secret"=> "cf98e49d-9ebe-43cb-a690-ad96295b3457",
            // "api_url"=>"http://port.hqepay.com/Ebusiness/EbusinessOrderHandle.aspx", #写死的
        );
        $params['certi_ac']=$this->genSign($params,$token);
        //$api_url2 = 'http://sws.ex-sandbox.com/api.php';
        $api_url2 = 'http://www.matrix.ecos.shopex.cn/api.php';
        $headers = array('Connection' => 5);
        $core_http = kernel::single('base_httpclient');
        $response = $core_http->post($api_url2, $params,$headers);
        $response = json_decode($response,true);
        if($response['res'] == 'succ' || $response['msg']['errorDescription'] == '绑定关系已存在,不需要重复绑定') {
            base_kvstore::instance('ome/bind/hqepay')->store('ome_bind_hqepay', true);
            return true;
        }
        return false;
    }

    public function genSign($params, $token) {
        ksort($params);
        $str = '';
        foreach ($params as $key =>$value) {
            if ($key != 'certi_ac') {
                $str .= $value;
            }
        }
        $signString = md5($str.$token);
        return $signString;
    }
    public function rpc_logistics_hqepay($rpc_data){
        base_kvstore::instance('ome/bind/hqepay')->fetch('ome_bind_hqepay', $is_ome_bind_hqepay);
        if(!$is_ome_bind_hqepay){
            $rs = $this->bind();
            if(!$rs){
                $return_data['rsp'] = 'fail';
                $return_data['err_msg'] = '没有绑定!';
                return  $return_data;
            }
        }

        $app_exclusion = app::get('base')->getConf('system.main_app');
        $params['app_id'] = 'ecos.b2c';
        $params['from_node_id'] = base_shopnode::node_id($app_exclusion['app_id']);
        $params['to_node_id'] = '1227722633';
        $params['method'] = 'logistics.trace.detail.get';
        $params['tid'] = $rpc_data['order_bn']; 
        $params['company_code'] = $rpc_data['logi_code'];
        $params['company_name'] = $rpc_data['company_name'];
        $params['logistic_code'] = $rpc_data['logi_no'];
        $token = base_shopnode::get('token',$app_exclusion['app_id']);
        $params['sign'] = $this->gen_matrix_sign($params,$token);

        //$api_url = 'http://rpc.ex-sandbox.com/sync';
        $api_url ="http://matrix.ecos.shopex.cn/sync";
        $time_out = 5;
        $headers = array(
            'Connection'=>$time_out,
        );
        $core_http = kernel::single('base_httpclient');
        $res = $core_http->post($api_url, $params,$headers);
        $res = json_decode($res,true);
        $return_data = null;
        if($res['rsp'] == 'fail'){
            $return_data['rsp'] = 'fail';
            $return_data['err_msg'] = $res->err_msg;
        }else{
            $return_data['rsp'] = 'succ';
            $_data = json_decode($res['data'],true);
            $return_data['data'] =  $_data['Traces'];
        }
        return $return_data;
    }
    function gen_matrix_sign($params,$token){
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }
    function assemble($params)
    {
        if(!is_array($params)){
            return null;
        }

        ksort($params,SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? assemble($val) : $val);
        }
        return $sign;
    }
}
