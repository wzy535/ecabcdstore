<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * alipay纯网关交易接口（国内）
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class ectools_payment_plugin_alipaygateway_server extends ectools_payment_app {

    public function callback(&$in){
        $mer_id = $this->getConf('mer_id', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));
        $mer_key = $this->getConf('mer_key', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));
        if($this->is_return_vaild($in, $mer_key))
        {
            $ret['payment_id'] = $in['out_trade_no'];
			$ret['account'] = $mer_id;
			$ret['bank'] = app::get('ectools')->_('支付宝');
			$ret['pay_account'] = app::get('ectools')->_('付款帐号');
			$ret['currency'] = 'CNY';
			$ret['money'] = $in['total_fee'];
			$ret['paycost'] = '0.000';
			$ret['cur_money'] = $in['total_fee'];
            $ret['trade_no'] = $in['trade_no'];
			$ret['t_payed'] = strtotime($in['notify_time']) ? strtotime($in['notify_time']) : time();
			$ret['pay_app_id'] = "alipaygateway";
			$ret['pay_type'] = 'online';
            $ret['memo'] = $in['body'];
            if($in['trade_status'] == 'TRADE_SUCCESS')
            {
                $ret['status'] = 'succ';
            }else{
                $ret['status'] = 'failed';
            }
        }else{
            $ret['status'] = 'failed';
        }

        return $ret;
    }

    public function is_return_vaild($form,$key)
	{
        ksort($form);
        foreach($form as $k=>$v){
            if($k!='sign'&&$k!='sign_type'&&$v!=''){
                $signstr .= "&$k=$v";
            }
        }

        $signstr = ltrim($signstr,"&");
        $signstr = $signstr.$key;

        if($form['sign']==md5($signstr)){
            return true;
        }
        #记录返回失败的情况
        logger::error(app::get('ectools')->_('支付单号：') . $form['out_trade_no'] . app::get('ectools')->_('签名验证不通过，请确认！')."\n");
        logger::error(app::get('ectools')->_('本地产生的加密串：') . $signstr);
        logger::error(app::get('ectools')->_('支付宝传递打过来的签名串：') . $form['sign']);
	    $str_xml .= "<alipayform>";
        foreach ($form as $key=>$value)
        {
            $str_xml .= "<$key>" . $value . "</$key>";
        }
        $str_xml .= "</alipayform>";

        return false;
    }
}
