<?php
final class wap_payment_plugin_lakalachuwap extends ectools_payment_app implements ectools_interface_payment_app {

	/**
	 * @var string 支付方式名称
	 */
    public $name = '拉卡拉境内支付(wap)';
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = 'lakalachuwap';
     /**
     * @var string 支付方式key
     */
    public $app_key = 'lakalachuwap';
	/**
	 * @var string 中心化统一的key
	 */
	public $app_rpc_key = 'lakalachuwap';
	/**
	 * @var string 统一显示的名称
	 */
    public $display_name = '拉卡拉境内支付(wap)';
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
    public $supportCurrency = array("CNY"=>"CNY");
    public $sdk_front_trans_url = 'https://intl.lakala.com:7777/ppayGate/CrossBorderHtml5Pay.do';
    public $version = '1.0.0';


    /**
	 * 构造方法
	 * @param null
	 * @return boolean
	 */
    public function __construct($app){
		parent::__construct($app);
        //$this->callback_url = $this->app->base_url(true)."/apps/".basename(dirname(__FILE__))."/".basename(__FILE__);
		$this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/wap/wap_payment_plugin_lakalachuwap_server', 'callback');
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

		$this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/wap/wap_payment_plugin_lakalachuwap', 'callback');
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
        return '简介内容';
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
                    'mid'=>array(
                        'title'=>app::get('wap')->_('商户代码'),
                        'type'=>'string',
                        'validate_type' => 'required',
                    ),
                    'public_key'=>array(
                        'title'=>app::get('wap')->_('公钥'),
                        'type'=>'string',
                        'validate_type' => 'required',
                    ),
                    'private_key'=>array(
                        'title'=>app::get('wap')->_('私钥'),
                        'type'=>'string',
						'validate_type' => 'required',
                    ),

                    'order_by' =>array(
                        'title'=>app::get('wap')->_('排序'),
                        'type'=>'string',
                        'label'=>app::get('wap')->_('整数值越小,显示越靠前,默认值为1'),
                    ),

                    'pay_brief'=>array(
                        'title'=>app::get('wap')->_('支付方式简介'),
                         'type'=>'textarea',
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
				),
                    'support_cur'=>array(
                        'title'=>app::get('wap')->_('支持币种'),
                        'type'=>'text hidden cur',
                        'options'=>$this->arrayCurrencyOptions,
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
        return '简介内容';
    }

	/**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment){
        //3Des密钥
        $mid = $this->getConf('mid',__CLASS__);
        $ts = date('YmdHis').mt_rand(100000, 999999);
        $merDesStr = md5($mid);
        $encKeyStr = $ts.$merDesStr;
        $encrypted = $maced = '';

        $config['pk'] = $this->rsa_ges($this->getConf('public_key',__CLASS__), 'public');
        $config['rk'] = $this->rsa_ges($this->getConf('private_key',__CLASS__), 'private');

        //公钥加密  
        openssl_public_encrypt($encKeyStr, $encrypted, $config['pk']);

        //密钥密文
        $encKey = strtoupper(bin2hex($encrypted));
        $ver = '1.0.0';
        $reqType = 'B0002';
        $_orderData = array(
          'merOrderId' => $payment['payment_id'],
          'currency' => $payment['currency'],
          'ts' => $ts,
          'orderAmount' => $payment['cur_money'],
          'payeeAmount' => $payment['money'],
          'orderTime' => date('YmdHis',$payment['t_begin']),
          'bizCode' => '122030',
          'orderSummary' => $payment['memo'],
          'timeZone' => 'GMT+8',
          'pageUrl' => $this->callback_url,
          'bgUrl' => $this->notify_url,
          'ext1' => '',
          'ext2' => ''
        );
        $json_init = $this->json_encode_s($_orderData);
        $json = $this->des_encrypt($merDesStr, $json_init);
        $macStr = $mid.$ver.$ts.$reqType.$json;
        $macStr = sha1($macStr);
        openssl_private_encrypt($macStr, $maced, $config['rk']); //私钥加密  
        $mac = strtoupper(bin2hex($maced));

        $data = array(
          'ver' => $ver,
          'merId' => $mid,
          'ts' => $ts,
          'pageUrl' => $this->callback_url,
          'reqType' => $reqType,
          'encKey' => $encKey,
          'encData' => $json,
          'mac' => $mac
        );
        $ppayGateUrl = 'https://intl.lakala.com:7777/ppayGate/CrossBorderHtml5Pay.do';
        //表单提交
        $this->build_form($ppayGateUrl, $data);
        exit;
    }

    /**
     * RSA 公钥私钥格式化
     * @param string $str 无序公钥、私钥
     * @param string $type public OR private
     * @return string 格式化之后的公钥私钥
     * @author chunkuan <urcn@qq.com>
     */
    public function rsa_ges($str, $type = 'public'){
      $publicKeyString = "-----BEGIN ".strtoupper($type)." KEY-----\n";
      $publicKeyString .= wordwrap($str, 64, "\n", true);
      $publicKeyString .= "\n-----END ".strtoupper($type)." KEY-----\n";
      return $publicKeyString;
    }

    /* * **** DES 加密解密库 End ******** */
    /**
     * json数据强制转字符串类型
     * @param array $array 待转换的数组
     * @return string 转换后的json字符串
     * @author chunkuan <urcn@qq.com>
     */
    public function json_encode_s($array){
      foreach($array as &$v){
        $v = (string) $v;
      }
      return json_encode($array);
    }

    /**
     * des加密字符串
     * @param string $key 加密密钥
     * @param string $data 待加密字符串
     * @return 加密后的十六进制数据
     * @author chunkuan <urcn@qq.com>
     */
    public function des_encrypt($key, $data){
      $size = mcrypt_get_block_size('des', 'ecb');
      $data = $this->des_pkcs5_pad($data, $size);
      $td = mcrypt_module_open('des', '', 'ecb', '');
      $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
      @mcrypt_generic_init($td, $key, $iv);
      $data = mcrypt_generic($td, $data);
      mcrypt_generic_deinit($td);
      mcrypt_module_close($td);
      return strtoupper(bin2hex($data));
    }

    /* * **** DES 加密解密库 Start ******** */
    /**
     * des加密用到的临时函数
     */
    public function des_pkcs5_pad($text, $blocksize){
      $pad = $blocksize - (strlen($text) % $blocksize);
      return $text.str_repeat(chr($pad), $pad);
    }
    /**
     * 创建&提交FORM表单
     * @param string $url 需要提交到的地址
     * @param array $data 需要提交的数据
     * @return void
     * @author chunkuan <urcn@qq.com>
     */
    public function build_form($url, $data){
      $sHtml = "<!DOCTYPE html><html><head><title>Waiting...</title>";
      $sHtml.= "<meta http-equiv='content-type' content='text/html;charset=utf-8'></head>
          <body><form id='lakalasubmit' name='lakalasubmit' action='".$url."' method='POST'>";
      foreach($data as $key => $value){
        $sHtml.= "<input type='hidden' name='".$key."' value='".$value."' style='width:90%;'/>";
      }
      $sHtml .= "</form>正在提交订单信息...";
      $sHtml .= "<script>document.forms['lakalasubmit'].submit();</script></body></html>";
      exit($sHtml);
    }

    /**
     * 生成支付表单 - 自动提交
     * @params null
     * @return null
     */
    public function gen_form()
    {
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
	 * 支付后返回后处理的事件的动作
	 * @params array - 所有返回的参数，包括POST和GET
	 * @return null
	 */
    public function callback(&$recv){
        $config['pk'] = $this->rsa_ges($this->getConf('public_key',__CLASS__), 'public');
        $config['rk'] = $this->rsa_ges($this->getConf('private_key',__CLASS__), 'private');
        $mid = $this->getConf('mid',__CLASS__);
        if($recv['retCode'] != '0000'){
          $message = $recv['retMsg'];
          $ret['status'] = 'fail';
        }else{
          $res = $this->decryptReqData($recv, $config['pk'], $config['rk']);
          if(!empty($res['error'])){
            $message = $res['error'];
            $ret['status'] = 'fail';
          }else{
            $ret['payment_id'] = $res['data']['merOrderId'];
            $ret['account'] = $mid;
            $ret['currency'] = $res['data']['currency'];
            $ret['money'] = $res['data']['payRMBAmount'];
            $ret['paycost'] = $res['data']['orderFee'];
            $ret['cur_money'] = $res['data']['payAmount'];
            $ret['t_payed'] = time($res['data']['dealTime']);
            $ret['pay_app_id'] = "lakalacropc";
            $ret['pay_type'] = 'online';
            $ret['status'] = 'succ';
          }
        }
        return $ret;
    }

    /**
     * 支付后同步返回验签结果
     * @param array $data 返回的post数组
     * @param string $pk 公钥
     * @param string $rk 私钥
     * @return array 解密后的支付数据
     * @author chunkuan <urcn@qq.com>
     */
    function decryptReqData($data, $pk, $rk){
      $return_data = array('status' => 0, 'data' => array(), 'error' => '');
      $retCode = $data['retCode'];
      $retMsg = $data['retMsg'];
      $merId = $data['merId'];
      $ver = $data['ver'];
      $ts = $data['ts'];
      $reqType = $data['reqType'];
      $reqEncData = $data['encData'];
      $reqMac = $data['mac'];
      $macData = sha1(mb_convert_encoding($retCode.$retMsg.$merId.$ver.$ts.$reqType.$reqEncData, 'gbk', 'utf-8'));
      $reqMacStr = '';
      openssl_public_decrypt($this->hex2bin(strtolower($reqMac)), $reqMacStr, $pk);
      if($macData != $reqMacStr){
        $return_data['error'] = 'MAC校验失败';
        return $return_data;
      }
      $merKey = md5($merId);
      $reqData = $this->des_decrypt($merKey, $reqEncData);
      if(empty($reqData)){
        $return_data['error'] = '解密业务参数失败';
        return $return_data;
      }
      $return_data['status'] = 1;
      $return_data['data'] = json_decode($reqData, true);
      return $return_data;
    }
    /**
     * 十六进制转二进制
     * @param string $data 待转换的十六进制数据
     * @return string 返回转换后的二进制数据
     * @author chunkuan <urcn@qq.com>
     */
    function hex2bin($data){
      $len = strlen($data);
      $newdata = '';
      for($i = 0; $i < $len; $i+=2){
        $newdata .= pack("C", hexdec(substr($data, $i, 2)));
      }
      return $newdata;
    }
    /**
     * des解密字符串
     * @param string $key 加密密钥
     * @param string $data 待解密字符串
     * @return 解密后的数据
     * @author chunkuan <urcn@qq.com>
     */
    function des_decrypt($key, $data){
      $data = $this->hex2bin(strtolower($data));
      $td = mcrypt_module_open('des', '', 'ecb', '');
      $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
      $ks = mcrypt_enc_get_key_size($td);
      @mcrypt_generic_init($td, $key, $iv);
      $decrypted = mdecrypt_generic($td, $data);
      mcrypt_generic_deinit($td);
      mcrypt_module_close($td);
      $result = $this->des_pkcs5_unpad($decrypted);
      return $result;
    }

    /**
     * 解密用到的临时函数
     */
    function des_pkcs5_unpad($text){
      $pad = ord($text{strlen($text) - 1});
      if($pad > strlen($text)){
        return false;
      }
      if(strspn($text, chr($pad), strlen($text) - $pad) != $pad){
        return false;
      }
      return substr($text, 0, -1 * $pad);
    }
}
