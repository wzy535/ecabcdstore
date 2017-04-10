<?php
final class wap_payment_plugin_malipaynew extends ectools_payment_app implements ectools_interface_payment_app {

    /**
     * @var string 支付方式名称
     */
    public $name = '新手机支付宝';
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '新手机支付宝';
     /**
     * @var string 支付方式key
     */
    public $app_key = 'malipaynew';
    /**
     * @var string 中心化统一的key
     */
    public $app_rpc_key = 'malipaynew';
    /**
     * @var string 统一显示的名称
     */
    public $display_name = '新手机支付宝';
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
    public $platform = 'iswap';

    /**
     * @var array 扩展参数
     */
    public $supportCurrency = array("CNY"=>"CNY");

    /**
     * @支付宝固定参数
     */
    public $_input_charset  = 'utf-8';//数编码字符集
    public $sign_type = 'RSA';//签名方式
    public $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';//支付宝网关地址

    /**
     * 构造方法
     * @param null
     * @return boolean
     */
    public function __construct($app){
        parent::__construct($app);

        $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/wap/wap_payment_plugin_malipaynew', 'callback');
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
        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/wap/wap_payment_plugin_malipaynew', 'callback');
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
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function admin_intro(){
        $regIp = isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:$_SERVER['HTTP_HOST'];
        return '<img src="' . $this->app->res_url . '/payments/images/ALIPAY.gif"><br /><b style="font-family:verdana;font-size:13px;padding:3px;color:#000"><br>ShopEx联合支付宝推出优惠套餐：无预付/年费，单笔费率低至0.7%-1.2%，无流量限制。</b><div style="padding:10px 0 0 388px"><a  href="javascript:void(0)" onclick="document.ALIPAYFORM.submit();"><img src="' . $this->app->res_url . '/payments/images/alipaysq.png"></a></div><div>如果您已经和支付宝签约了其他套餐，同样可以点击上面申请按钮重新签约，即可享受新的套餐。<br>如果不需要更换套餐，请将签约合作者身份ID等信息在下面填写即可，<a href="http://www.shopex.cn/help/ShopEx48/help_shopex48-1235733634-11323.html" target="_blank">点击这里查看使用帮助</a><form name="ALIPAYFORM" method="GET" action="http://top.shopex.cn/recordpayagent.php" target="_blank"><input type="hidden" name="postmethod" value="GET"><input type="hidden" name="payagentname" value="支付宝"><input type="hidden" name="payagentkey" value="ALIPAY"><input type="hidden" name="market_type" value="from_agent_contract"><input type="hidden" name="customer_external_id" value="C433530444855584111X"><input type="hidden" name="pro_codes" value="6AECD60F4D75A7FB"><input type="hidden" name="regIp" value="'.$regIp.'"><input type="hidden" name="domain" value="'.$this->app->base_url(true).'"></form></div>';
    }

     /**
     * 后台配置参数设置
     * @param null
     * @return array 配置参数列表
     */
    public function setting(){
        return array(
            'pay_name'=>array(
                'title'=>app::get('wap')->_('支付方式名称'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'partner'=>array(
                'title'=>app::get('wap')->_('合作身份者id'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            /*'mer_key'=>array(
                'title'=>app::get('wap')->_('交易安全校验码(key)'),
                'type'=>'string',
                'validate_type' => 'required',
            ),*/
            'seller_id'=>array(
                'title'=>app::get('wap')->_('收款支付宝账号'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'private_key_path'=>array(
                'title'=>app::get('wap')->_('商户的私钥'),
                'type'=>'file',
                'validate_type' => 'required',
                'label'=>app::get('wap')->_('文件后缀名为.pen'),
            ),
            'ali_public_key_path'=>array(
                'title'=>app::get('wap')->_('支付宝公钥'),
                'type'=>'file',
                'validate_type' => 'required',
                'label'=>app::get('wap')->_('文件后缀名为.pen'),
            ),
            'support_cur'=>array(
                'title'=>app::get('wap')->_('支持币种'),
                'type'=>'text hidden cur',
                'options'=>$this->arrayCurrencyOptions,
            ),
            'pay_desc'=>array(
                'title'=>app::get('wap')->_('描述'),
                'type'=>'html',
                'includeBase' => true,
            ),
            'pay_type'=>array(
                'title'=>app::get('wap')->_('支付类型(是否在线支付)'),
                'type'=>'radio',
                'options'=>array('true'=>app::get('wap')->_('是')),
                'name' => 'pay_type',
                'validate_type' => 'requiredradio',
            ),
            'status'=>array(
                'title'=>app::get('wap')->_('是否开启此支付方式'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('wap')->_('否'),'true'=>app::get('wap')->_('是')),
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
        return app::get('wap')->_('支付宝（中国）网络技术有限公司是国内领先的独立第三方支付平台，由阿里巴巴集团创办。支付宝致力于为中国电子商务提供“简单、安全、快速”的在线支付解决方案。').'
<a target="_blank" href="https://www.alipay.com/static/utoj/utojindex.htm">'.app::get('wap')->_('如何使用支付宝支付？').'</a>';
    }

    /**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment)
    {
        $this->partner = trim($this->getConf('partner', __CLASS__));
        //$this->mer_key = trim($this->getConf('mer_key', __CLASS__));
        $this->seller_id = trim($this->getConf('seller_id', __CLASS__));
        $this->sdk_verify_cert_dir = DATA_DIR . '/cert/payment_plugin_malipaynew/';
        $this->private_key_path = $this->sdk_verify_cert_dir.trim($this->getConf('private_key_path', __CLASS__));
        $this->ali_public_key_path = $this->sdk_verify_cert_dir.trim($this->getConf('ali_public_key_path', __CLASS__));
        $this->subject = (isset($payment['subject']) && $payment['subject']) ? $payment['subject'] : ($payment['account'].$payment['payment_id']);
        $this->subject = str_replace("'",'`',trim($this->subject));
        $this->subject = str_replace('"','`',$this->subject);
        $this->subject = str_replace(' ','',$this->subject);

        $parameter = array(
            "service" => "alipay.wap.create.direct.pay.by.user",
            "partner" => trim($this->partner),
            "seller_id" => trim($this->seller_id),
            "payment_type"  => "1",
            "notify_url"    => $this->notify_url,
            "return_url"    => $this->callback_url,
            "out_trade_no"  => $payment['payment_id'],
            "subject"   => $this->subject,
            "total_fee" => number_format($payment['cur_money'],2,".",""),
            "show_url" =>"",
            "body"  => $payment['body'],
            "it_b_pay" =>"",
            "extern_token"=>"",
            "_input_charset"    => trim(strtolower($this->_input_charset))
        );
        $html_text = $this->buildRequestForm($parameter,"get", "确认");
        echo $html_text;exit;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
    public function buildRequestForm($para_temp, $method, $button_name) {
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."_input_charset=".trim(strtolower($this->_input_charset))."' method='".$method."'>";
        while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
        
        $sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
        
        return $sHtml;
    }

    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    public function callback(&$recv)
    {
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
    public function gen_form()
    {
      return '';
    }

//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓公共函数部分↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓


    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
    public function buildRequestPara($para_temp) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);
        //生成签名结果
        $mysign = $this->buildRequestMysign($para_sort);
        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mysign;
        $para_sort['sign_type'] = strtoupper(trim($this->sign_type));
        
        return $para_sort;
    }

    /**
     * 生成签名结果
     * @param $para_sort 已排序要签名的数组
     * return 签名结果字符串
     */
    public function buildRequestMysign($para_sort) {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);
        $mysign = "";
        switch (strtoupper(trim($this->sign_type))) {
            case "RSA" :
                $mysign = $this->rsaSign($prestr, $this->private_key_path);
                break;
            default :
                $mysign = "";
        }
        
        return $mysign;
    }

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
     * RSA签名
     * @param $data 待签名数据
     * @param $private_key_path 商户私钥文件路径
     * return 签名结果
     */
    public function rsaSign($data, $private_key_path) {
        $priKey = file_get_contents($private_key_path);
        $res = openssl_get_privatekey($priKey);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
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
