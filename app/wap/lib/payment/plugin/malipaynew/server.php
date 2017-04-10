<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
 * alipay notify 异步验证接口
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
class wap_payment_plugin_malipaynew_server extends ectools_payment_app {

    /**
     * @支付宝固定参数
     */
    public $_input_charset  = 'utf-8';//数编码字符集
    public $sign_type = 'RSA';//签名方式
    public $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';//支付宝网关地址
    
	/**
	 * 支付后返回后处理的事件的动作
	 * @params array - 所有返回的参数，包括POST和GET
	 * @return null
	 */
    public function callback(&$recv){
        $this->partner = trim($this->getConf('partner', __CLASS__));
        $this->sdk_verify_cert_dir = DATA_DIR . '/cert/payment_plugin_malipaynew/';
        $this->private_key_path = $this->sdk_verify_cert_dir.trim($this->getConf('private_key_path', __CLASS__));
        $this->ali_public_key_path = $this->sdk_verify_cert_dir.trim($this->getConf('ali_public_key_path', __CLASS__));
        $verify_result = $this->verifyReturn($recv);
        
        if($verify_result){
            $ret['payment_id'] = $recv['out_trade_no'];
            $ret['account'] = $this->partner;
            $ret['bank'] = app::get('wap')->_('手机支付宝');
            $ret['pay_account'] = app::get('wap')->_('付款帐号');
            $ret['currency'] = 'CNY';
            $ret['money'] = $recv['total_fee'];
            $ret['paycost'] = '0.000';
            $ret['cur_money'] = $recv['total_fee'];
            $ret['trade_no'] = $recv['trade_no'];
            $ret['t_payed'] = strtotime($recv['notify_time']) ? strtotime($recv['notify_time']) : time();
            $ret['pay_app_id'] = "malipaynew";
            $ret['pay_type'] = 'online';
            $ret['memo'] = $recv['body'];
            if($recv['trade_status'] == 'TRADE_FINISHED' || $recv['trade_status'] == 'TRADE_SUCCESS') {
                $ret['status'] = 'succ';
            }else {
                $ret['status'] =  'failed';
            }
        }else{
            $message = 'Invalid Sign';
            $ret['status'] = 'invalid';
        }
        return $ret;
    }

    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    public function verifyReturn($recv){
        if(empty($recv)) {
            return false;
        }
        else {
            $isSign = $this->getSignVeryfy($recv, $recv["sign"]);
            if ($isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓公共函数部分↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    public function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
            else    $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

        /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    public function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    public function createLinkstring($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        
        return $arg;
    }
    
    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    public function getSignVeryfy($para_temp, $sign) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);
        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);
        
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);
        
        $isSgin = false;
        switch (strtoupper(trim($this->sign_type))) {
            case "RSA" :
                $isSgin = $this->rsaVerify($prestr, trim($this->ali_public_key_path), $sign);
                break;
            default :
                $isSgin = false;
        }
        
        return $isSgin;
    }

    /**
     * RSA验签
     * @param $data 待签名数据
     * @param $ali_public_key_path 支付宝的公钥文件路径
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    public function rsaVerify($data, $ali_public_key_path, $sign)  {
        $pubKey = file_get_contents($ali_public_key_path);
        $res = openssl_get_publickey($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($sign), $res,OPENSSL_ALGO_DSS1);
        openssl_free_key($res);
        return $result;
    }

//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑公共函数部分↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

}
