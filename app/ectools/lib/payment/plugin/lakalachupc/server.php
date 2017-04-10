<?php
class ectools_payment_plugin_lakalachupc_server extends ectools_payment_app {

    public function callback(){
        $postdata = file_get_contents("php://input");
        if(is_array($postdata)){$postdata = json_encode($postdata);}
        $platPublicKey = $this->rsa_ges($this->getConf('public_key', substr(__CLASS__, 0, strrpos(__CLASS__, '_'))), 'public');
        $merPrivateKey = $this->rsa_ges($this->getConf('private_key', substr(__CLASS__, 0, strrpos(__CLASS__, '_'))), 'private');
        $mid = $this->getConf('mid', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));

        $post = json_decode($postdata, true);
        $ts = $post['ts'];
        $reqType = $post['reqType'];
        $reqEncKey = $post['encKey'];
        $reqEncData = $post['encData'];
        $reqMac = $post['mac'];
        $reqmacStr = sha1($ts.$reqType.$reqEncData);
        $reqMacStr = '';
        openssl_public_decrypt($this->hex2bin(strtolower($reqMac)), $reqMacStr, $platPublicKey);
        if($reqMacStr != $reqmacStr){
          $ret['status'] = 'fail'; 
        }else{
          $merKey = ""; //商户对称密钥
          openssl_private_decrypt($this->hex2bin(strtolower($reqEncKey)), $merKey, $merPrivateKey);
          $merKey = substr($merKey, strlen($merKey) - 32, 32);
          $reqData = $this->des_decrypt($merKey, $reqEncData);
          if($reqData){
            $reqData = json_decode($reqData, true);
            $ret['payment_id'] = $reqData['merOrderId'];
            $ret['account'] = $mid;
            $ret['currency'] = $reqData['currency'];
            $ret['money'] = $reqData['payAmount']*$reqData['exchangeRate'];
            $ret['paycost'] = $reqData['orderFee'];
            $ret['cur_money'] = $reqData['payAmount'];
            $ret['t_payed'] = time($reqData['dealTime']);
            $ret['pay_app_id'] = "lakalachupc";
            $ret['pay_type'] = 'online';
            $ret['status'] = 'succ'; 
          }
        }
        return $ret;
    }
    public function rsa_ges($str, $type = 'public'){
      $publicKeyString = "-----BEGIN ".strtoupper($type)." KEY-----\n";
      $publicKeyString .= wordwrap($str, 64, "\n", true);
      $publicKeyString .= "\n-----END ".strtoupper($type)." KEY-----\n";
      return $publicKeyString;
    }
    function hex2bin($data){
      $len = strlen($data);
      $newdata = '';
      for($i = 0; $i < $len; $i+=2){
        $newdata .= pack("C", hexdec(substr($data, $i, 2)));
      }
      return $newdata;
    }
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
