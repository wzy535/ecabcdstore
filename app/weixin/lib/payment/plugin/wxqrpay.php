<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * alipay支付宝手机支付接口
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class weixin_payment_plugin_wxqrpay extends ectools_payment_app implements ectools_interface_payment_app {

    /**
     * @var string 支付方式名称
     */
    public $name = '微信支付扫码支付';
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '微信支付扫码支付';
     /**
     * @var string 支付方式key
     */
    public $app_key = 'wxqrpay';
    /**
     * @var string 中心化统一的key
     */
    public $app_rpc_key = 'wxqrpay';
    /**
     * @var string 统一显示的名称
     */
    public $display_name = '微信支付扫码支付';
    /**
     * @var string 货币名称
     */
    public $curname = 'CNY';
    /**
     * @var string 当前支付方式的版本号
     */
    public $ver = '1.0';
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'ispc';

    /**
     * @var array 扩展参数
     */
    public $supportCurrency = array("CNY"=>"CNY");

    public $init_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder?';

    /**
     * 构造方法
     * @param null
     * @return boolean
     */
    public function __construct($app){
        parent::__construct($app);

        // $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/weixin/weixin_payment_plugin_wxpayjsapi', 'callback');
        $this->notify_url = kernel::base_url(1).'/index.php/openapi/weixin/wxqrpay';
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->notify_url, $matches))
        {
            $this->notify_url = str_replace('http://','',$this->notify_url);
            $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
            $this->notify_url = "http://" . $this->notify_url;
        }
        else
        {
            $this->notify_url = str_replace('https://','',$this->notify_url);
            $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
            $this->notify_url = "https://" . $this->notify_url;
        }
        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/weixin/weixin_payment_plugin_wxpay', 'callback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->callback_url, $matches))
        {
            $this->callback_url = str_replace('http://','',$this->callback_url);
            $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
            $this->callback_url = "http://" . $this->callback_url;
        }
        else
        {
            $this->callback_url = str_replace('https://','',$this->callback_url);
            $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
            $this->callback_url = "https://" . $this->callback_url;
        }
        $this->submit_charset = 'UTF-8';
        $this->signtype = 'sha1';
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function admin_intro(){
        $regIp = isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:$_SERVER['HTTP_HOST'];
        return '<img src="' . app::get('weixin')->res_url . '/payments/images/WXPAY.jpg"><br /><b style="font-family:verdana;font-size:13px;padding:3px;color:#000"><br>微信支付(扫码支付 V3.3.6)是由腾讯公司知名移动社交通讯软件微信及第三方支付平台财付通联合推出的移动支付创新产品，旨在为广大微信用户及商户提供更优质的支付服务，微信的支付和安全系统由腾讯财付通提供支持。</b>
            <br>如果遇到支付问题，请访问：<a href="javascript:void(0)" onclick="top.location = '."'http://bbs.ec-os.net/read.php?tid=1007'".'">http://bbs.ec-os.net/read.php?tid=1007</a>';
    }

     /**
     * 后台配置参数设置
     * @param null
     * @return array 配置参数列表
     */
    public function setting(){
        // 公众账号
        $publicNumbersInfo = app::get('weixin')->model('bind')->getList('appid,name',array('appid|noequal'=>''));
        $publicNumbers = array();
        foreach($publicNumbersInfo as $row){
            $publicNumbers[$row['appid']] = $row['name'];
        }
        return array(
            'pay_name'=>array(
                'title'=>app::get('weixin')->_('支付方式名称'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'appId'=>array(
                'title'=>app::get('weixin')->_('appId'),
                'type'=>'select',
                'options'=>$publicNumbers,
                'validate_type' => 'required',
            ),
            'Mchid'=>array(
                'title'=>app::get('weixin')->_('Mchid'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'Key'=>array(
                'title'=>app::get('weixin')->_('Key'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'support_cur'=>array(
                'title'=>app::get('weixin')->_('支持币种'),
                'type'=>'text hidden cur',
                'options'=>$this->arrayCurrencyOptions,
            ),
            'pay_desc'=>array(
                'title'=>app::get('weixin')->_('描述'),
                'type'=>'html',
                'includeBase' => true,
            ),
            'pay_type'=>array(
                'title'=>app::get('weixin')->_('支付类型(是否在线支付)'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('weixin')->_('否'),'true'=>app::get('weixin')->_('是')),
                'name' => 'pay_type',
            ),
            'status'=>array(
                'title'=>app::get('weixin')->_('是否开启此支付方式'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('weixin')->_('否'),'true'=>app::get('weixin')->_('是')),
                'name' => 'status',
            ),
        );
    }

    /**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function intro(){
        return app::get('weixin')->_('微信支付是由腾讯公司知名移动社交通讯软件微信及第三方支付平台财付通联合推出的移动支付创新产品，旨在为广大微信用户及商户提供更优质的支付服务，微信的支付和安全系统由腾讯财付通提供支持。财付通是持有互联网支付牌照并具备完备的安全体系的第三方支付平台。');
    }

    /**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment)
    {
        $appid      = trim($this->getConf('appId',    __CLASS__));
        $mch_id     = trim($this->getConf('Mchid',    __CLASS__));
        $key        = trim($this->getConf('Key',      __CLASS__));

        $parameters = array(
            'appid'            => strval($appid),
            'body'             => strval( str_replace(' ', '', (isset($payment['body']) && $payment['body']) ? $payment['body'] : app::get('weixin')->_('网店订单') ) ),
            'out_trade_no'     => strval( $payment['payment_id'] ),
            'total_fee'        => bcmul($payment['cur_money'],100,0),
            'notify_url'       => strval( $this->notify_url ),
            'trade_type'       => 'NATIVE',
            'mch_id'           => strval($mch_id),
            'nonce_str'        => weixin_util::create_noncestr(),
            //android版本的微信6.1会报服务器忙的错误，这里临时改为服务器ip（文档为客户端ip），可以规避该错误
            'spbill_create_ip' => strval( $_SERVER['SERVER_ADDR'] ),
        );
        $parameters['sign'] = $this->getSign($parameters, $key);
        $xml                = $this->arrayToXml($parameters);
        logger::info('wxqrpay post to weixin for prepay_id:'.var_export($xml, 1));
        $url                = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $response           = $this->postXmlCurl($xml, $url, 30);
        logger::info('wxqrpay response info from weixin for prepay_id:'.var_export($response, 1));
        $result             = $this->xmlToArray($response);
        $code_url           = $result['code_url'];
        $return_msg         = $result['return_msg'];

        // 用于微信支付后跳转页面传order_id,不作为传微信的字段
        $this->add_field("order_id",        $payment['order_id'] );
        $this->add_field("payment_id",      $payment['payment_id'] );
        $this->add_field('code_url',        $code_url);
        $this->add_field('return_msg',      $return_msg);

        echo $this->get_html();exit;
    }

    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    function callback(&$in){
        $mch_id     = trim($this->getConf('Mchid',    __CLASS__));
        $key        = trim($this->getConf('Key',      __CLASS__));
        $in = $in['weixin_postdata'];
        $insign = $in['sign'];
        unset($in['sign']);

        if( $in['return_code'] == 'SUCCESS' && $in['result_code'] == 'SUCCESS' )
        {
            if( $insign == $this->getSign( $in, $key))
            {
                $objMath = kernel::single('ectools_math');
                $money   = $objMath->number_multiple(array($in['total_fee'], 0.01));
                $ret['payment_id' ] = $in['out_trade_no'];
                $ret['account']     = $mch_id;
                $ret['bank']        = app::get('wap')->_('微信支付扫码支付');
                $ret['pay_account'] = $in['openid'];
                $ret['currency']    = 'CNY';
                $ret['money']       = $money;
                $ret['paycost']     = '0.000';
                $ret['cur_money']   = $money;
                $ret['trade_no']    = $in['transaction_id'];
                $ret['t_payed']     = strtotime($in['time_end']) ? strtotime($in['time_end']) : time();
                $ret['pay_app_id']  = "wxqrpay";
                $ret['pay_type']    = 'online';
                $ret['memo']        = $in['attach'];
                $ret['status']      = 'succ';

            }else{
                $ret['status'] = 'failed';
            }
        }else{
            $ret['status'] = 'failed';
        }
        return $ret;
    }

    /**
     * 支付成功回打支付成功信息给支付网关
     */
    function ret_result($paymentId){
        $ret = array('return_code'=>'SUCCESS','return_msg'=>'');
        $ret = $this->arrayToXml($ret);
        echo $ret;exit;
    }

    /**
     * 校验方法
     * @param null
     * @return boolean
     */
    public function is_fields_valiad(){
        return true;
    }

    /**
     * 生成支付表单 - 自动提交
     * @params null
     * @return null
     */
    public function gen_form(){
        return '';
    }

    protected function get_html(){
        //$qrcode_url = kernel::base_url(1).'/index.php/qrcode.html?text='.$this->fields['code_url'];
        $qrcode_url = app::get('site')->router()->gen_url(array('app'=>'weixin','ctl'=>'site_qrcode','act'=>'index','full'=>1));
        $qrcode_url .= '?text='.urlencode($this->fields['code_url']);
        $check_url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'check_payments','full'=>1,'arg0'=>$this->fields['payment_id']));
		if( $this->fields['order_id'] ){
            $success_url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result_pay','full'=>1,'arg0'=>$this->fields['order_id'],'arg1'=>'true'));
        }else{
            $success_url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'balance','full'=>1));
        }
        $path = app::get('site')->res_full_url;
        $path_b2c = app::get('b2c')->res_full_url;
        $strHtml ="<!DOCTYPE HTML><html><head><meta charset='utf-8'><title>微信扫码支付</title><meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no'/>";
        $strHtml .= "<link rel='stylesheet' href='".$path_b2c."/css_mini/basic.min.css' />";
        $strHtml .= "</head>";
        $strHtml .=
            '<body>
            <div>';
        if( empty($this->fields['code_url']) ){
            $strHtml .= '<div style="height:125px;line-height:125px;text-align:center;color:red;">';
            $strHtml .= 'ERROR：'.$this->fields['return_msg'];
            $strHtml .= '</div>';
        }else{
        $strHtml .=
            '<div id="qrcode">
            <div class="wrapper">
              <img src="'.$qrcode_url.'" alt="微信扫码支付"/>
            </div>
            </div>';
        }
        $strHtml .= '</div>';
        $strHtml .= '</body><script>

            function check_trade(){

                var req = new Request({url: "'.$check_url.'",
                    method:"get",
                    evalScripts:true,
                    onSuccess: function(responseText) {
                        var obj = new Function("return" + responseText)();
                        if(obj.succ == "该支付单已经完成支付")
                            window.location.href = "'.$success_url.'";
                        setTimeout("check_trade()",1000);
                }
                }).send();
                }
                check_trade();
                //setInterval("check_trade()",1000);

</script></html>';

        return $strHtml;
    }

//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓公共函数部分↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

    /**
     *  作用：将xml转为array
     */
    public function xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /**
     *  作用：array转xml
     */
    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
             if (is_numeric($val))
             {
                $xml.="<".$key.">".$val."</".$key.">";

             }
             else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     *  作用：以post方式提交xml到对应的接口url
     */
    public function postXmlCurl($xml,$url,$second=30)
    {
        $res = kernel::single('base_httpclient')->post($url,$xml);
        return $res;
//      //初始化curl
//      $ch = curl_init();
//      //设置超时
//      curl_setopt($ch, CURLOPT_TIMEOUT, $second);
//      //这里设置代理，如果有的话
//      //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
//      //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
//      curl_setopt($ch,CURLOPT_URL, $url);
//      curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
//      curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
//      //设置header
//      curl_setopt($ch, CURLOPT_HEADER, FALSE);
//      //要求结果为字符串且输出到屏幕上
//      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//      //post提交方式
//      curl_setopt($ch, CURLOPT_POST, TRUE);
//      curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
//      //运行curl
//      $data = curl_exec($ch);
//      curl_close($ch);
//      //返回结果
//      if($data)
//      {
//          curl_close($ch);
//          return $data;
//      }
//      else
//      {
//          $error = curl_errno($ch);
//          echo "curl出错，错误码:$error"."<br>";
//          echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
//          curl_close($ch);
//          return false;
//      }
    }

    /**
     *  作用：设置标配的请求参数，生成签名，生成接口参数xml
     */
    function createXml($parameters)
    {
        $this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
        $this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
        $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
        $this->parameters["sign"] = $this->getSign($this->parameters);//签名
        return  $this->arrayToXml($this->parameters);
    }

    /**
     *  作用：post请求xml
     */
    function postXml()
    {
        $xml = $this->createXml();
        $this->response = $this->postXmlCurl($xml,$this->url,$this->curl_timeout);
        return $this->response;
    }

    /**
     *  作用：格式化参数，签名过程需要使用
     */
    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
               $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     *  作用：生成签名
     */
    public function getSign($Obj,$key)
    {
        foreach ($Obj as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$key;
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑公共函数部分↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
}
