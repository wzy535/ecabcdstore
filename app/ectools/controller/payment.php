<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class ectools_ctl_payment extends desktop_controller{

    var $workground = 'ectools_ctl_admin_order';
	
	public function __construct($app)
	{
		parent::__construct($app);
		header("cache-control: no-store, no-cache, must-revalidate");
	}
	
    function index(){
        $this->finder('ectools_mdl_payments',array(
            'title'=>app::get('ectools')->_('支付单'),
            ));
    }
    
    /**
     * 新建支付订单
     * @params array - 订单详细内容
     * @return boolean - 订单成功与否
     */
    public function addnew($arrPayments=array())
    {
        echo __FILE__.':'.__LINE__;
    }

    public function addnew_list(){
        $payment = $this->app->model('payment_list');
        $this->ui = new base_component_ui($this);
        
        if($_POST)
        {
            $this->begin();
            $result = $payment->save($_POST);
            if (isset($_GET['action']) && $_GET['action'] && $_GET['action'] == 'edit')
                $this->end($result, app::get('ectools')->_('收款帐号修改成功！'));
            else
            {
                $this->end($result, app::get('ectools')->_('收款帐号添加成功！'));
            }
        }
        else
        {
            $this->display('payments/add_pay_list.html');
        }
    }

    public function edit_list()
    {
        $payment = $this->app->model('payment_list');
        $rows = $payment->getList('*', array('list_id'=>$_GET['list_id']));
        $pay = $rows[0];
        $this->pagedata['account'] = $pay['account'];
        $this->pagedata['bank'] = $pay['bank'];
        $this->pagedata['list_id'] = $pay['list_id'];
        $this->display('payments/edit_pay_list.html');
    }

    public function edit_save()
    {
        $this->begin();
        if (!$_POST)
            $this->end(false, app::get('ectools')->_('收款帐号失败！'));
        $payment = $this->app->model('payment_list');
        $result = $payment->update($_POST,array('list_id'=>$_POST['list_id']));
        $this->end($result, app::get('ectools')->_('收款帐号修改成功！'));
    }
}
