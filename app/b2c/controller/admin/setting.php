<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_setting extends desktop_controller{

    var $require_super_op = true;

    public function __construct($app){
        parent::__construct($app);
        $this->ui = new base_component_ui($this);
        $this->app = $app;
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    public function index(){
        $this->basic();
    }

    function basic(){
        $all_settings = array(
            app::get('b2c')->_('商店基本设置')=>array(
                'site.logo',
                'system.shopname',
                'site.loginlogo',
                // 'store.shop_url',
                // 'system.enable_network',
            ),
            app::get('b2c')->_('店家信息')=>array(
                'store.site_owner',
                'store.contact',
                'store.telephone',
                'store.mobile',
                'store.email',
                'store.qq',
                'store.wangwang',
                'store.address',
                'store.zip_code',
            ),
            app::get('b2c')->_('登录注册设置')=>array(
                'site.login_type',
                'site.login_close_autocomplete',
                'site.register_valide',
                'site.login_valide',
                'site.sms_valide',
            ),
           app::get('b2c')->_('积分设置')=>array(
                'site.get_policy.method',
                'site.get_rate.method',
                'site.level_switch',
                'site.point_promotion_method',
                'site.checkout.login_point.open',
                'site.login_point.num',
                //'site.level_point',
            ),
           app::get('b2c')->_('购物设置')=>array(
                #'security.guest.enabled',
                //'site.storage.enabled',
                //'site.delivery_time',
                // 'site.rsc_rpc',
                //'system.goods.fastbuy',
                //'site.min_order',
                //'site.min_order_amount',
                'system.money.decimals',
                'system.money.operation.carryset',
                'site.buy.target',
                'cart.show_order_sales.type',
                'site.checkout.zipcode.required.open',
                'site.trigger_tax', //是否开启发票
                'site.personal_tax_ratio',
                'site.company_tax_ratio',
                'site.tax_content',
                'site.checkout.receivermore.open',
                'site.checkout.special',
                'site.checkout.hasnight',
                'site.checkout.shortest',
                'site.combination.pay',//组合支付
                'site.trigger_cancelorder', //是否开启取消订单
                'site.cancelorder_timelimit', //订单创建多少小时后，未支付取消订单
            ),
          app::get('b2c')->_(商品列表页设置)=>array(
                //'system.category.showgoods',
                //'site.show_storage',
                //'site.promotion.display',
                //'cart.show_order_sales.total_limit',
                //'site.retail_member_price_display',
                //'site.wholesale_member_price_display',
                //'selllog.display.switch',
                //'selllog.display.limit',
                //'selllog.display.listnum',
                //'storeplace.display.switch',
                //'goodsprop.display.switch',
                //'gallery.display.grid.colnum',
                //'site.associate.search',
                //'site.property.select',
                'gallery.default_view',
                'gallery.open_type',
                'gallery.display.listnum',
                'gallery.display.pagenum',
                'gallery.display.store_status',
                'gallery.store_status.num',
                'gallery.display.stock_goods',
                'site.cat.select',
                'gallery.display.price',
                'gallery.display.tag.goods',
                'gallery.display.tag.promotion',
                'gallery.display.promotion',
                'gallery.deliver.time',
                'gallery.comment.time',
                'gallery.display.buynum',
             ),
         app::get('b2c')->_('商品详情页设置')=>array(
                'site.isfastbuy_display',
                'goods.show_order_sales.type',
                'goods.recommend',
                'site.imgzoom.show',
                'site.imgzoom.width',
                'site.imgzoom.height',
                'site.show_mark_price',
                'site.market_price',
                'site.market_rate',
                'selllog.display.switch',
                'selllog.display.limit',
                'selllog.display.listnum',
                'selllog.display.member.price',
                'site.member_price_display',
                'site.save_price',
                'site.show_storage',
                'goodsprop.display.position',
                'goodsbn.display.switch',
                'productsbn.display.switch',
         ),
            app::get('b2c')->_('其他设置')=>array(
                // 'site.certtext',
                'system.product.alert.num',
                'system.goods.freez.time',
                //'system.admin_verycode',
                // 'system.upload.limit',
                //'system.product.zendlucene',
				//'site.order.send_type',
            ),
        );

        // set service for extension settings.
        $obj_extension_services = kernel::servicelist('b2c_extension_settings');
        if ($obj_extension_services)
        {
            foreach ($obj_extension_services as $obj_ext_service)
            {
                $obj_ext_service->settings($all_settings);
            }
        }

        $html= $this->_process($all_settings);
        echo $html;
    }

    function _process($all_settings){
        $setting = new base_setting($this->app);
        $setlib = $setting->source();
        $obj_b2c_shop = $this->app->model('shop');
        $cnt = $obj_b2c_shop->count(array('status'=>'bind','node_type'=>'ecos.ome'));

        // 发票高级配置埋点
        foreach( kernel::servicelist('invoice_setting') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'addHtml') ) {
                    $addHtml = $services->addHtml();
                }
            }
        }
        if (isset($addHtml) && !empty($addHtml)) {
            $setlib = array_merge($setlib, $addHtml);
        }
        $typemap = array(
            SET_T_STR=>'text',
            SET_T_INT=>'number',
            SET_T_ENUM=>'select',
            SET_T_BOOL=>'bool',
            SET_T_TXT=>'text',
            SET_T_FILE=>'file',
            SET_T_IMAGE=>'image',
            SET_T_DIGITS=>'number',
        );
        $tabs = array_keys($all_settings);
        $html = $this->ui->form_start(array('tabs'=>$tabs,'method'=>'POST','id'=>'setting_form'));
        $input_style = false;
        $arr_js = array();
        foreach($tabs as $tab=>$tab_name){
            foreach($all_settings[$tab_name] as $set){
                $current_set = $pre_set = $this->app->getConf($set);
                if($set == 'system.shopname'){
                    $current_set = app::get('site')->getConf('site.name');
                }
                if($_POST['set'] && array_key_exists($set,$_POST['set'])){

                    if($current_set!==$_POST['set'][$set]){
                        $current_set = $_POST['set'][$set];
                        $this->app->setConf($set,$_POST['set'][$set]);
                        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                        if($obj_operatorlogs = kernel::service('operatorlog.system')){
                            if(method_exists($obj_operatorlogs,'logSystemConfigInfo')){
                                $obj_operatorlogs->logSystemConfigInfo($setlib[$set]['desc'], $pre_set, $_POST['set'][$set]);
                            }
                        }
                        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
                    }
                }


                $input_type = $typemap[$setlib[$set]['type']];

                $form_input = array(
                    'title'=>$setlib[$set]['desc'],
                    'type'=>$input_type,
                    'name'=>"set[".$set."]",
                    'tab'=>$tab,
                    'helpinfo'=>$setlib[$set]['helpinfo'],
                    'value'=>$current_set,
                    'options'=>$setlib[$set]['options'],
                    'vtype' => $setlib[$set]['vtype'],
                    'class' => $setlib[$set]['class'],
                    'id' => $setlib[$set]['id'],
                    'default' => $setlib[$set]['default'],
                );
                if ($input_type=='select')
                    $form_input['required'] = true;
        if($cnt>0){
             if($form_input['name']=="set[system.goods.freez.time]"){
                if($current_set!='1'){
                    $current_set=1;
                }
                if($current_set=='1'){
                    $form_input['disabled'] ="disabled";
                }
             }
        }
                if (isset($setlib[$set]['extends_attr']) && $setlib[$set]['extends_attr'] && is_array($setlib[$set]['extends_attr']))
                {
                    foreach ($setlib[$set]['extends_attr'] as $_key=>$extends_attr)
                    {
                        $form_input[$_key] = $extends_attr;
                    }
                }

                $arr_js[] = $setlib[$set]['javascript'];

                $html.=$this->ui->form_input($form_input);
            }
        }

        if (!$_POST)
        {
            $this->pagedata['_PAGE_CONTENT'] = $html .= $this->ui->form_end() . '<script type="text/javascript">window.addEvent(\'domready\',function(){';

            $str_js = '';
            if (is_array($arr_js) && $arr_js)
            {
                foreach ($arr_js as $str_javascript)
                {
                    $str_js .= $str_javascript;
                }
            }

            $str_js .= '$("main").addEvent("click",function(el){
                el = el.target || el;
                if ($(el).get("id")){
                    var _id = $(el).get("id");
                    var _class_name = "";
                    if (_id.indexOf("-t") > -1){
                        _class_name = _id.substr(0, _id.indexOf("-t"));
                        $$("."+_class_name).getParent("tr").show();
                    }
                    if (_id.indexOf("-f") > -1){
                        _class_name = _id.substr(0, _id.indexOf("-f"));
                        var _destination_node = $$("."+_class_name);
                        _destination_node.getParent("tr").hide();
                        _destination_node.each(function(item){if (item.getNext(".caution") && item.getNext(".caution").hasClass("error")) item.getNext(".caution").remove();});
                    }
                }
            });
            $$("[name=set[site.sms_valide]]").addEvent("click",function(){
                var that = this;
                if(that.value=="false"){
                    if(!confirm("取消该项会使网站更易受到短信轰炸")){
                        that.checked = "";
                        that.getSiblings("[name=set[site.sms_valide]]")[0].checked = "checked";
                    }
                }
            });';

            $this->pagedata['_PAGE_CONTENT'] .= $str_js . '});</script>';
            $this->page();
        }
        else
        {
            $this->begin();
            app::get('site')->setConf('site.name',$_POST['set']['system.shopname']);
            $this->end(true, app::get('b2c')->_('当前配置修改成功！'));
        }
    }

    function licence(){
        $this->sidePanel();
        echo '<iframe width="100%" height="100%" src="'.constant('URL_VIEW_LICENCE').'" ></iframe>';
    }

    function imageset(){
        $ctl = new image_ctl_admin_manage($this->app);
        $ctl->imageset();
    }

}

