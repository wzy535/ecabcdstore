<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_site_member extends b2c_frontpage{

    function __construct(&$app){
        parent::__construct($app);
        $shopname = app::get('site')->getConf('site.name');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('会员中心').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('会员中心_').'_'.$shopname;
            $this->description = app::get('b2c')->_('会员中心_').'_'.$shopname;
        }
        $this->header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->verify_member();
        $this->pagesize = 10;
        $this->action = $this->_request->get_act_name();
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";
        $this->member = $this->get_current_member();
        /** end **/
    }

    /*
     *会员中心左侧菜单栏
     * */
    private function get_cpmenu(){
        // 判断是否开启预存款
        $mdl_payment_cfgs = app::get('ectools')->model('payment_cfgs');
        $payment_info = $mdl_payment_cfgs->getPaymentInfo('deposit');
        $arr_blance = array();
        $arr_recharge_blance = array();
        $arr_point_history = array();
        $arr_point_coupon_exchange = array();
        $this->pagedata['point_usaged'] = "false";

        if ($payment_info['app_staus'] == app::get('ectools')->_('开启'))
        {
            $this->pagedata['deposit_status'] = 'true';
            $arr_blance = array('label'=>app::get('b2c')->_('我的预存款'),'app'=>'b2c','ctl'=>'site_member','link'=>'balance');
            $arr_recharge_blance = array('label'=>app::get('b2c')->_('预存款充值'),'app'=>'b2c','ctl'=>'site_member','link'=>'deposit');
        }

        $site_get_policy_method = $this->app->getConf('site.get_policy.method');
        if ($site_get_policy_method != '1')
        {
            $arr_point_history = array('label'=>app::get('b2c')->_('积分历史'),'app'=>'b2c','ctl'=>'site_member','link'=>'point_history');
            $arr_point_coupon_exchange = array('label'=>app::get('b2c')->_('积分兑换优惠券'),'app'=>'b2c','ctl'=>'site_member','link'=>'couponExchange');
            $this->pagedata['point_usaged'] = "true";
        }

        $this->pagedata['comment_switch_discuss'] = $this->app->getConf('comment.switch.discuss');
        $this->pagedata['comment_switch_ask'] = $this->app->getConf('comment.switch.ask');

        if($this->pagedata['comment_switch_discuss'] == 'on'){
            //未评论商品
            $nodiscuss = array('label'=>app::get('b2c')->_('未评论商品'),'app'=>'b2c','ctl'=>'site_member','link'=>'nodiscuss');
            $comment_tab = array('label'=>app::get('b2c')->_('我的评论'),'app'=>'b2c','ctl'=>'site_member','link'=>'comment');
        }
        if($this->pagedata['comment_switch_discuss'] == 'on' && $this->pagedata['comment_switch_ask'] == 'on'){
            $comment_tab = array('label'=>app::get('b2c')->_('评论与咨询'),'app'=>'b2c','ctl'=>'site_member','link'=>'comment');
        }elseif($this->pagedata['comment_switch_ask'] == 'on'){
            $comment_tab = array('label'=>app::get('b2c')->_('我的咨询'),'app'=>'b2c','ctl'=>'site_member','link'=>'ask');
        }

        if($this->pagedata['comment_switch_ask'] == 'on' || $this->pagedata['comment_switch_discuss'] == 'on'){
            $comment['label'] = app::get('b2c')->_('评论咨询管理');
            $comment['mid'] = 2;
            if($nodiscuss){
                $comment['items'][] = $nodiscuss;
            }
            $comment['items'][] = $comment_tab;
        }

        $arr_bases = array(
            array(
                'label'=>app::get('b2c')->_('交易管理'),
                'mid'=>0,
                'items'=>array(
                    array('label'=>app::get('b2c')->_('我的订单'),'app'=>'b2c','ctl'=>'site_member','link'=>'orders'),
                )
            ),
            //评论咨询
            $comment,
            array(
                'label'=>app::get('b2c')->_('我的账户'),
                'mid'=>1,
                'items'=>array(
                    array('label'=>app::get('b2c')->_('商品收藏'),'app'=>'b2c','ctl'=>'site_member','link'=>'favorite'),
                    array('label'=>app::get('b2c')->_('到货通知'),'app'=>'b2c','ctl'=>'site_member','link'=>'notify'),
                    array('label'=>app::get('b2c')->_('我的优惠券'),'app'=>'b2c','ctl'=>'site_member','link'=>'coupon'),
                    $arr_blance,//预存款充值
                    $arr_recharge_blance,//预存款充值
                    $arr_point_coupon_exchange,//积分兑换优惠券
                ),
            ),
            array(
                'label'=>app::get('b2c')->_('个人信息管理'),
                'mid'=>4,
                'items'=>array(
                    array('label'=>app::get('b2c')->_('站内信'),'app'=>'b2c','ctl'=>'site_member','link'=>'inbox'),
                    array('label'=>app::get('b2c')->_('个人信息'),'app'=>'b2c','ctl'=>'site_member','link'=>'setting'),
                    array('label'=>app::get('b2c')->_('安全中心'),'app'=>'b2c','ctl'=>'site_member','link'=>'securitycenter'),
                    array('label'=>app::get('b2c')->_('收货地址'),'app'=>'b2c','ctl'=>'site_member','link'=>'receiver'),
                ),
            ),
        );
        if(empty($arr_bases[1]) ){
            unset($arr_bases[1]);
        }

        $obj_menu_extends = kernel::servicelist('b2c.member_menu_extends');
        if ($obj_menu_extends)
        {
            foreach ($obj_menu_extends as $obj)
            {
                if (method_exists($obj, 'get_extends_menu'))
                    $obj->get_extends_menu($arr_bases, array('0'=>'b2c', '1'=>'site_member', '2'=>'index'));
            }
        }

        return $arr_bases;
    }

    /*
     *会员中心页面统一输出
     * */
    protected function output($app_id='b2c'){
        $this->pagedata['member'] = $this->member;
        $this->pagedata['cpmenu'] = $this->get_cpmenu();
        $this->pagedata['current'] = $this->action;
        if( $this->pagedata['_PAGE_'] ){
            $this->pagedata['_PAGE_'] = 'site/member/'.$this->pagedata['_PAGE_'];
        }else{
           $this->pagedata['_PAGE_'] = 'site/member/'.$this->action_view;
        }
        foreach(kernel::servicelist('member_index') as $service){
            if(is_object($service)){
                if(method_exists($service,'get_member_html')){
                    $aData[] = $service->get_member_html();
                }
            }
        }
        $this->pagedata['app_id'] = $app_id;
        $this->pagedata['_MAIN_'] = 'site/member/main.html';
        $this->pagedata['get_member_html'] = $aData;
        $this->set_tmpl('member');
        $this->page('site/member/main.html');
    }

    /*
     *本控制器公共分页函数
     * */
    function pagination($current,$totalPage,$act,$arg='',$app_id='b2c',$ctl='site_member'){
        if (!$arg)
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>array(($tmp = time())))),
                'token'=>$tmp,
                );
        else
        {
            $arg = array_merge($arg, array(($tmp = time())));
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>$arg)),
                'token'=>$tmp,
                );
        }
    }

    function get_start($nPage,$count){
        $maxPage = ceil($count / $this->pagesize);
        if($nPage > $maxPage) $nPage = $maxPage;
        $start = ($nPage-1) * $this->pagesize;
        $start = $start<0 ? 0 : $start;
        $aPage['start'] = $start;
        $aPage['maxPage'] = $maxPage;
        return $aPage;
    }

    /*
     *会员中心首页
     * */
    public function index() {

        $userObject = kernel::single('b2c_user_object');
        //面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $GLOBALS['runtime']['path'] = $this->path;

        #会员基本信息,已在construct()内获取过。

        #获取会员等级
        $obj_mem_lv = $this->app->model('member_lv');
        $levels = $obj_mem_lv->getList('name,disabled',array('member_lv_id'=>$this->member['member_lv']));
        if($levels[0]['disabled']=='false'){
            $this->member['levelname'] = $levels[0]['name'];
        }
        $oMem_lv = $this->app->model('member_lv');
        $this->pagedata['switch_lv'] = $oMem_lv->get_member_lv_switch($this->member['member_lv']);

        //交易提醒
        $msgAlert = $this->msgAlert();
        $this->member = array_merge($this->member,$msgAlert);

        //订单列表
        $oRder = $this->app->model('orders');//--11sql
        $aData = $oRder->fetchByMember($this->app->member_id,$nPage=1,array(),5); //--141sql优化点
        //获取预售信息主要是时间219-231
        $prepare_order=kernel::service('prepare_order');
        if($prepare_order)
        {
            $pre_order=$prepare_order->get_prepare_info($aData['data']);
            foreach ($aData['data'] as $key => $value) {
                if($value['promotion_type']=='prepare')
                {
                    $aData['data'][$key]['prepare']=$pre_order[$value['order_id']];
                }

            }
        }
        foreach ($aData['data'] as $key => $value) {
            $aData['data'][$key]['url'] = $this->gen_url(array('app'=>'b2c','ctl'=>"site_member",'act'=>"receive",'arg0'=>$value['order_id']));;
        }
        $this->get_order_details($aData, 'member_latest_orders');//--177sql 优化点
        $this->pagedata['orders'] = $aData['data'];

        //收藏列表
        $obj_member = $this->app->model('member_goods');
        $aData_fav = $obj_member->get_favorite($this->app->member_id,$this->member['member_lv'],$page=1,$num=4);//201sql
        $this->pagedata['favorite'] = $aData_fav['data'];
        #默认图片
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];

        $member_signin_obj = $this->app->model('member_signin');
        $this->pagedata['signin_status'] = $member_signin_obj->exists_signin($this->app->member_id,date('Y-m-d'));
        $this->pagedata['site_checkout_login_point_open'] = $this->app->getConf('site.checkout.login_point.open');
        $this->pagedata['site_login_point_num'] = $this->app->getConf('site.login_point.num');
        //输出
        $this->pagedata['member'] = $this->member;
        $this->set_tmpl('member');
        $this->output();
    }

    /*
     *会员中心首页交易提醒 (未付款订单,到货通知，未读的评论咨询回复)
     * */
    private function msgAlert(){
        //获取待付款订单数
        $oRder = $this->app->model('orders');//--11sql
        $un_pay_orders = $oRder->count(array('member_id' => $this->member['member_id'],'pay_status' => 0,'status'=>'active','promotion_type' => 'normal'));
        $member['un_pay_orders'] = $un_pay_orders;
        //获取预售订单数
        $prepare_pay_orders = $oRder->count(array('member_id' => $this->member['member_id'],'promotion_type' => 'prepare'));
        $member['prepare_pay_orders'] = $prepare_pay_orders;
        //到货通知
        $member_goods = $this->app->model('member_goods');
        $member['sto_goods_num'] = $member_goods->get_goods($this->app->member_id);

        //评论咨询回复
        $mem_msg = $this->app->model('member_comments');
        $object_type = array('discuss','ask');
        $aData = $mem_msg->getList('*',array('to_id' => $this->app->member_id,'object_type'=> $object_type,'mem_read_status' => 'false','display' => 'true'));
        $un_readAskMsg = 0;
        $un_readDiscussMsg = 0;
        foreach($aData as $val){
            if($val['object_type'] == 'ask'){
                $un_readAskMsg += 1;
            }else{
                $un_readDiscussMsg += 1;
            }
        }
        $member['un_readAskMsg'] = $un_readAskMsg;
        $member['un_readDiscussMsg'] = $un_readDiscussMsg;
        return $member;
    }



    function setting(){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('个人信息'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $userObject = kernel::single('b2c_user_object');
        $membersData = $userObject->get_pam_data('*',$this->app->member_id);
        $this->pagedata['mem'] = $membersData;

        //修改用户名埋点
      //if(app::get('pam')->model('members')->is_openIdNameByName($this->pagedata['mem']['local']) == true) $this->pagedata['allowtochangeusername'] = true;

        $attr = kernel::single('b2c_user_passport')->get_signup_attr($this->app->member_id);
        $this->pagedata['attr'] = $attr;
        $this->output();
    }

    function save_setting(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>"site_member",'act'=>"setting"));
        $member_model = $this->app->model('members');
        $userPassport = kernel::single('b2c_user_passport');
        $_POST = $this->check_input($_POST);
        if($_POST['local_name'] && !$userPassport->set_local_uname($_POST,$msg)){
            $this->splash('failed',null , $msg,true);
        }

        foreach($_POST as $key=>$val){
            if(strpos($key,"box:") !== false){
                $aTmp = explode("box:",$key);
                $_POST[$aTmp[1]] = serialize($val);
            }
        }


        //--防止恶意修改
        $arr_colunm = array('contact','profile','pam_account','currency');
        $attr = $this->app->model('member_attr')->getList('attr_column');
        foreach($attr as $attr_colunm){
            $colunm = $attr_colunm['attr_column'];
            $arr_colunm[] = $colunm;
        }
        foreach($_POST as $post_key=>$post_value){
            if( !in_array($post_key,$arr_colunm) ){
                unset($_POST[$post_key]);
            }
        }
        //---end

        $_POST['member_id'] = $this->app->member_id;
        if($member_model->save($_POST)){

            //增加会员同步 2012-05-15
            if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
                $member_rpc_object->modifyActive($_POST['member_id']);
            }

            $this->splash('success', $url , app::get('b2c')->_('提交成功'),true);
        }else{
            $this->splash('failed',null , app::get('b2c')->_('提交失败'),true);
        }
    }

    //修改用户名埋点
  //public function change_member_name(){
  //    $member_id    = $this->member['member_id'];
  //    $member_name  = $_POST['local_name'];
  //    $userPassport = kernel::single('b2c_user_passport');
  //    $url = $this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'setting'));
  //    if($userPassport->change_member_name($member_id, $member_name, $msg))
  //        $this->splash('success', $url, app::get('b2c')->_('提交成功'), true);
  //    $this->splash('failed', null, $msg, true);
  //  //$this->splash('failed', null,$userPassport->change_member_name($member_id, $member_name, $msg), true);
  //}

    /**
     * Member order list datasource
     * @params int equal to 1
     * @return null
     */
    public function orders($pay_status='all', $nPage=1)
    {
         $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('我的订单'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $order = $this->app->model('orders');
        if ($pay_status == 'all')
        {
            $aData = $order->fetchByMember($this->app->member_id,$nPage);
        }
        else
        {
            $order_status = array();
            if ($pay_status == 'nopayed')
            {   $order_status['promotion_type'] = 'normal';
                $order_status['pay_status'] = 0;
                $order_status['status'] = 'active';
            }
            if($pay_status == 'prepare')
            {
                //$order_status['pay_status'] = 1;
                //$order_status['status'] = 'active';
                $order_status['promotion_type'] = 'prepare';
            }
            $aData = $order->fetchByMember($this->app->member_id,$nPage-1,$order_status);
            //echo '<pre>';print_r($aData);exit();
        }
        $this->get_order_details($aData,'member_orders');
        $oImage = app::get('image')->model('image');
        $oGoods = app::get('b2c')->model('goods');
        $imageDefault = app::get('image')->getConf('image.set');
        foreach($aData['data'] as $k => &$v) {
            foreach($v['goods_items'] as $k2 => &$v2) {
                $spec_desc_goods = $oGoods->getList('spec_desc',array('goods_id'=>$v2['product']['goods_id']));
                // 此处是获取购买的有规格的货品的缩略图，无规格的商品没有spec_desc，没有货品，跳过该商品。
                if( !is_array($v2['product']['products']['spec_desc']['spec_private_value_id']) ){
                    continue;
                }
                $select_spec_private_value_id = reset($v2['product']['products']['spec_desc']['spec_private_value_id']);
                if( !is_array($spec_desc_goods[0]['spec_desc']) ){
                    continue;
                }
                $spec_desc_goods = reset($spec_desc_goods[0]['spec_desc']);
                if($spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']){
                    list($default_product_image) = explode(',', $spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']);
                    $v2['product']['thumbnail_pic'] = $default_product_image;
                }else{
                    if( !$v2['product']['thumbnail_pic'] && !$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                        $v2['product']['thumbnail_pic'] = $imageDefault['S']['default_image'];
                    }
                }
            }
        }
        // echo '<pre>';print_r( $aData['data']);exit();
         //获取预售信息主要是时间390-398
        $prepare_order=kernel::service('prepare_order');
        if($prepare_order)
        {
            $pre_order=$prepare_order->get_prepare_info($aData['data']);
            foreach ($aData['data'] as $key => $value) {
                if($value['promotion_type']=='prepare')
                {
                    $aData['data'][$key]['prepare']=$pre_order[$value['order_id']];
                }
            }
        }
        $obj_return_policy = kernel::service("aftersales.return_policy");
        (!isset($obj_return_policy) || !is_object($obj_return_policy)) ? $is_obj_return_policy =0 : $is_obj_return_policy=1;
        foreach ($aData['data'] as $key => $value) {
            $aData['data'][$key]['url'] = $this->gen_url(array('app'=>'b2c','ctl'=>"site_member",'act'=>"receive",'arg0'=>$value['order_id']));;
            if($is_obj_return_policy){
                $aData['data'][$key]['is_aftersales'] = $obj_return_policy->is_order_aftersales($value['order_id'],$this->app->member_id);
            }
        }
        $this->pagedata['orders'] = $aData['data'];
        $arr_args = array($pay_status);
        $this->pagination($nPage,$aData['pager']['total'],'orders',$arr_args);
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['is_orders'] = "true";

        $this->output();
    }

    /**
     * Member order list datasource
     * @params int equal to 1
     * @return null
     */
    public function archive_orders($pay_status='all', $nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的订单'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $order = $this->app->model('archive_orders');
        $aData = $order->fetchByMember($this->app->member_id,$nPage-1);

        $this->get_order_details($aData,'member_orders');
        $oImage = app::get('image')->model('image');
        $imageDefault = app::get('image')->getConf('image.set');
        foreach($aData['data'] as $k=>$v) {
            foreach($v['goods_items'] as $k2=>$v2) {
                if( !$v2['product']['thumbnail_pic'] && !$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aData['data'][$k]['goods_items'][$k2]['product']['thumbnail_pic'] = $imageDefault['S']['default_image'];
                }
            }
        }
        foreach ($aData['data'] as $key => $value) {
            $aData['data'][$key]['url'] = $this->gen_url(array('app'=>'b2c','ctl'=>"site_member",'act'=>"receive",'arg0'=>$value['order_id']));;
        }
        $this->pagedata['orders'] = $aData['data'];

        $arr_args = array('all');
        $this->pagination($nPage,$aData['pager']['total'],'archive_orders',$arr_args);
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['is_orders'] = "true";
        $this->pagedata['archive'] = true;
        $this->action_view = 'orders.html';
        $this->output();
    }

    /**
     * 得到订单列表详细
     * @param array 订单详细信息
     * @param string tpl
     * @return null
     */
    protected function get_order_details(&$aData,$tml='member_orders')
    {
        if (isset($aData['data']) && $aData['data'])
        {
            $objMath = kernel::single('ectools_math');
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }

            foreach ($aData['data'] as &$arr_data_item)
            {
                $this->get_order_detail_item($arr_data_item,$tml);
            }
        }
    }

    /**
     * 得到订单列表详细
     * @param array 订单详细信息
     * @param string 模版名称
     * @return null
     */
    protected function get_order_detail_item(&$arr_data_item,$tpl='member_order_detail')
    {
        if (isset($arr_data_item) && $arr_data_item)
        {
            $objMath = kernel::single('ectools_math');
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }


            $arr_data_item['goods_items'] = array();
            $obj_specification = $this->app->model('specification');
            $obj_spec_values = $this->app->model('spec_values');
            $obj_goods = $this->app->model('goods');
            if (isset($arr_data_item['order_objects']) && $arr_data_item['order_objects'])
            {
                foreach ($arr_data_item['order_objects'] as $k=>$arr_objects)
                {
                    $index = 0;
                    $index_adj = 0;
                    $index_gift = 0;
                    $image_set = app::get('image')->getConf('image.set');
                    if ($arr_objects['obj_type'] == 'goods')
                    {
                        foreach ($arr_objects['order_items'] as $arr_items)
                        {
                            if (!$arr_items['products'])
                            {
                                $o = $this->app->model('order_items');
                                $tmp = $o->getList('*', array('item_id'=>$arr_items['item_id']));
                                $arr_items['products']['product_id'] = $tmp[0]['product_id'];
                            }

                            if ($arr_items['item_type'] == 'product')
                            {
                                if ($arr_data_item['goods_items'][$k]['product'])
                                    $arr_data_item['goods_items'][$k]['product']['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k]['product']['quantity']));
                                else
                                    $arr_data_item['goods_items'][$k]['product']['quantity'] = $arr_items['quantity'];

                                $arr_data_item['goods_items'][$k]['product'] = $arr_items;
                                $arr_data_item['goods_items'][$k]['product']['name'] = $arr_items['name'];
                                $arr_data_item['goods_items'][$k]['product']['goods_id'] = $arr_items['goods_id'];
                                $arr_data_item['goods_items'][$k]['product']['price'] = $arr_items['price'];
                                $arr_data_item['goods_items'][$k]['product']['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k]['product']['quantity']);
                                $arr_data_item['goods_items'][$k]['product']['amount'] = $arr_items['amount'];
                                $arr_goods_list = $obj_goods->getList('image_default_id', array('goods_id' => $arr_items['goods_id']));
                                $arr_goods = $arr_goods_list[0];
                                if (!$arr_goods['image_default_id'])
                                {
                                    $arr_goods['image_default_id'] = $image_set['S']['default_image'];
                                }
                                $arr_data_item['goods_items'][$k]['product']['thumbnail_pic'] = $arr_goods['image_default_id'];
                                $arr_data_item['goods_items'][$k]['product']['link_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$arr_items['products']['product_id']));;
                                if ($arr_items['addon'])
                                {
                                    $arrAddon = $arr_addon = unserialize($arr_items['addon']);
                                    if ($arr_addon['product_attr'])
                                        unset($arr_addon['product_attr']);
                                    $arr_data_item['goods_items'][$k]['product']['minfo'] = $arr_addon;
                                }else{
                                    unset($arrAddon,$arr_addon);
                                }
                                if ($arrAddon['product_attr'])
                                {
                                    foreach ($arrAddon['product_attr'] as $arr_product_attr)
                                    {
                                        $arr_data_item['goods_items'][$k]['product']['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                    }
                                }

                                if (isset($arr_data_item['goods_items'][$k]['product']['attr']) && $arr_data_item['goods_items'][$k]['product']['attr'])
                                {
                                    if (strpos($arr_data_item['goods_items'][$k]['product']['attr'], " ") !== false)
                                    {
                                        $arr_data_item['goods_items'][$k]['product']['attr'] = substr($arr_data_item['goods_items'][$k]['product']['attr'], 0, strrpos($arr_data_item['goods_items'][$k]['product']['attr'], $this->app->_(" ")));
                                    }
                                }
                            }
                            elseif ($arr_items['item_type'] == 'adjunct')
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_items['item_type']];
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);


                                if ($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj])
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity']));
                                else
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity'] = $arr_items['quantity'];

                                if (!$arrGoods['image_default_id'])
                                {
                                    $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                }
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj] = $arr_items;
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['name'] = $arr_items['name'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity']);
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['goods_id'] = $arr_items['goods_id'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['price'] = $arr_items['price'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['link_url'] = $arrGoods['link_url'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['amount'] = $arr_items['amount'];

                                if ($arr_items['addon'])
                                {
                                    $arr_addon = unserialize($arr_items['addon']);

                                    if ($arr_addon['product_attr'])
                                    {
                                        foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                        {
                                            $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                        }
                                    }
                                }

                                if (isset($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr']) && $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'])
                                {
                                    if (strpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], $this->app->_(" ")) !== false)
                                    {
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'] = substr($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], 0, strrpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], $this->app->_(" ")));
                                    }
                                }

                                $index_adj++;
                            }
                            else
                            {
                                // product gift.
                                if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                                {
                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_items['item_type']];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);

                                    if ($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift])
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']));
                                    else
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity'] = $arr_items['quantity'];

                                    if (!$arrGoods['image_default_id'])
                                    {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift] = $arr_items;
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['name'] = $arr_items['name'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['goods_id'] = $arr_items['goods_id'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['price'] = $arr_items['price'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity']);
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['link_url'] = $arrGoods['link_url'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['amount'] = $arr_items['amount'];

                                    if ($arr_items['addon'])
                                    {
                                        $arr_addon = unserialize($arr_items['addon']);

                                        if ($arr_addon['product_attr'])
                                        {
                                            foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                            {
                                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                            }
                                        }
                                    }

                                    if (isset($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr']) && $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'])
                                    {
                                        if (strpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], $this->app->_(" ")) !== false)
                                        {
                                            $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'] = substr($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], 0, strrpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], $this->app->_(" ")));
                                        }
                                    }
                                }
                                $index_gift++;
                            }
                        }
                    }
                    else
                    {
                        if ($arr_objects['obj_type'] == 'gift')
                        {
                            //积分兑换赠品
                            $gift_key = '';
                            if( $arr_objects['obj_alias'] == app::get('b2c')->_('商品区块') ){
                                $gift_key = 'gift';//积分兑换赠品
                            }else{
                                $gift_key = 'order'; //订单送赠品，包含优惠券送赠品
                            }
                            if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                            {
                                foreach ($arr_objects['order_items'] as $arr_items)
                                {
                                    if (!$arr_items['products'])
                                    {
                                        $o = $this->app->model('order_items');
                                        $tmp = $o->getList('*', array('item_id'=>$arr_items['item_id']));
                                        $arr_items['products']['product_id'] = $tmp[0]['product_id'];
                                    }

                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_objects['obj_type']];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);

                                    if (!isset($arr_items['products']['product_id']) || !$arr_items['products']['product_id'])
                                        $arr_items['products']['product_id'] = $arr_items['goods_id'];

                                    if ($arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']])
                                        $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']));
                                    else
                                        $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity'] = $arr_items['quantity'];

                                    if (!$arrGoods['image_default_id'])
                                    {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }

                                    $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['name'] = $arr_items['name'];
                                    $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['goods_id'] = $arr_items['goods_id'];
                                    $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['price'] = $arr_items['price'];
                                    $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                    $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['point'] = intval($arr_items['score']*$arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']);
                                    $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['nums'] = $arr_items['quantity'];
                                    $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['link_url'] = $arrGoods['link_url'];
                                    $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['amount'] = $arr_items['amount'];

                                    if ($arr_items['addon'])
                                    {
                                        $arr_addon = unserialize($arr_items['addon']);

                                        if ($arr_addon['product_attr'])
                                        {
                                            foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                            {
                                                $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                            }
                                        }
                                    }

                                    if (isset($arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr']) && $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'])
                                    {
                                        if (strpos($arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], $this->app->_(" ")) !== false)
                                        {
                                            $arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'] = substr($arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], 0, strrpos($arr_data_item[$gift_key][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], $this->app->_(" ")));
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                            {

                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_objects['obj_type']];
                                $arr_data_item['extends_items'][] = $str_service_goods_type_obj->get_order_object($arr_objects, $arr_Goods,$tpl);
                            }
                        }
                    }
                }
            }

        }
    }

    /**
     * Generate the order detail
     * @params string order_id
     * @return null
     */
    public function orderdetail($order_id=0, $archive=false)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的订单'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'orders','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('订单详情'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        if (!isset($order_id) || !$order_id)
        {
            $this->begin(array('app' => 'b2c','ctl' => 'site_member', 'act'=>'index'));
            $this->end(false, app::get('b2c')->_('订单编号不能为空！'));
        }

        $objOrder = $archive ? $this->app->model('archive_orders') : $this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf_order = $objOrder->dump(array('order_id'=>$order_id), '*', $subsdf);
        $objMath = kernel::single("ectools_math");

        if(!$sdf_order||$this->app->member_id!=$sdf_order['member_id']){
            kernel::single('site_router')->http_status(404);return;
        }
        if($sdf_order['member_id']){
            $member = $this->app->model('members');
            $aMember = $member->dump($sdf_order['member_id'], 'email');
            $sdf_order['receiver']['email'] = $aMember['contact']['email'];
        }

        // 处理收货人地区
        $arr_consignee_area = array();
        $arr_consignee_regions = array();
        if (strpos($sdf_order['consignee']['area'], ':') !== false)
        {
            $arr_consignee_area = explode(':', $sdf_order['consignee']['area']);
            if ($arr_consignee_area[1])
            {
                if (strpos($arr_consignee_area[1], '/') !== false)
                {
                    $arr_consignee_regions = explode('/', $arr_consignee_area[1]);
                }
            }

            $sdf_order['consignee']['area'] = (is_array($arr_consignee_regions) && $arr_consignee_regions) ? $arr_consignee_regions[0] . $arr_consignee_regions[1] . $arr_consignee_regions[2] : $sdf_order['consignee']['area'];
        }
        $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');
        // 订单的相关信息的修改
        $obj_other_info = kernel::servicelist('b2c.order_other_infomation');
        if ($obj_other_info)
        {
            foreach ($obj_other_info as $obj)
            {
                $this->pagedata['discount_html'] = $obj->gen_point_discount($sdf_order);
            }
        }
        $this->pagedata['order'] = $sdf_order;
        $order_items = array();
        $gift_items = array();
        $this->get_order_detail_item($sdf_order,'member_order_detail');
        $this->pagedata['order'] = $sdf_order;
        /** 将商品促销单独剥离出来 **/
        if ($this->pagedata['order']['order_pmt'])
        {
            foreach ($this->pagedata['order']['order_pmt'] as $key=>$arr_pmt)
            {
                if ($arr_pmt['pmt_type'] == 'goods')
                {
                    $this->pagedata['order']['goods_pmt'][$arr_pmt['product_id']][$key] =  $this->pagedata['order']['order_pmt'][$key];
                    unset($this->pagedata['order']['order_pmt'][$key]);
                }
            }
        }
        /** end **/

        // 得到订单留言.
        $oMsg = kernel::single("b2c_message_order");
        $arrOrderMsg = $oMsg->getList('*', array('order_id' => $order_id, 'object_type' => 'order'), $offset=0, $limit=-1, 'time DESC');

        $this->pagedata['ordermsg'] = $arrOrderMsg;
        $this->pagedata['res_url'] = $this->app->res_url;

        //我已付款
        $$timeHours = array();
        for($i=0;$i<24;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeHours[$v] = $v;
        }
        $timeMins = array();
        for($i=0;$i<60;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeMins[$v] = $v;
        }
        $this->pagedata['timeHours'] = $timeHours;
        $this->pagedata['timeMins'] = $timeMins;

        // 生成订单日志明细
        //$oLogs =$this->app->model('order_log');
        //$arr_order_logs = $oLogs->getList('*', array('rel_id' => $order_id));
        $arr_order_logs = $objOrder->getOrderLogList($order_id);
        $this->pagedata['orderlogs'] = $arr_order_logs['data'];

        // 取到支付单信息
        $obj_payments = app::get('ectools')->model('payments');
        $this->pagedata['paymentlists'] = $obj_payments->get_payments_by_order_id($order_id);

        // 支付方式的解析变化
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        $arr_payments_cfg = $obj_payments_cfgs->getPaymentInfo($this->pagedata['order']['payinfo']['pay_app_id']);
        $this->pagedata['order']['payment'] = $arr_payments_cfg;

        // 添加html埋点
        foreach( kernel::servicelist('b2c.order_add_html') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'fetchHtml') ) {
                    $services->fetchHtml($this,$order_id,'site/invoice_detail.html');
                }
            }
        }
        $this->pagedata['controller'] = "orders";
        $this->pagedata['archive'] = $archive;
        // 预售订单信息
        $prepare_order=kernel::service('prepare_order');
        if($prepare_order)
        {
            $pre_order=$prepare_order->get_order_info($order_id);
            if(!empty($pre_order))
            {
                $pre_order['prepare_type']='prepare';
                $this->pagedata['prepare']=$pre_order;
            }
        }

        //echo '<pre>';print_r($prepare_order);exit();
        $this->output();
    }

    function deposit(){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('预存款充值'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $oCur = app::get('ectools')->model('currency');
        $currency = $oCur->getDefault();
        $this->pagedata['currencys'] = $currency;
        $this->pagedata['currency'] = $currency['cur_code'];
        $opay = app::get('ectools')->model('payment_cfgs');
        $aOld = $opay->getListByCode($currency['cur_code']);
        #获取默认的货币
        $obj_currency = app::get('ectools')->model('currency');
        $arr_def_cur = $obj_currency->getDefault();
        $this->pagedata['def_cur_sign'] = $arr_def_cur['cur_sign'];

        $aData = array();
        foreach($aOld as $val){
            if(($val['app_id']!='deposit') && ($val['app_id']!='offline')){
                $aData[] = $val;
            }
        }
        foreach ($aData as $key=>$arrPayments){
            //银行数据
            // if($arrPayments['app_id'] == $this->pagedata['order']['payinfo']['pay_app_id'])
            // {
                $pay_class = $arrPayments['app_class'];
                $ectools = app::get('ectools');
                $pay_app = new $pay_class($ectools);
                $pay_app_setting = $pay_app->setting();

                if(isset($pay_app_setting['support_bank']))
                {
                    $pay_app_config = app::get('ectools')->getConf($pay_class);
                    $pay_app_config = unserialize($pay_app_config);
                    $pay_app_config_bank = $pay_app_config['setting']['support_bank'];
                    $pay_app_support_bank = $pay_app_setting['support_bank']['options'];
                    foreach($pay_app_support_bank as $bank_type_key=>$bank_type_value)
                    {
                        foreach($bank_type_value as $k=>$v)
                        {
                            if(!$pay_app_config_bank[$k])
                            {
                                unset($pay_app_support_bank[$bank_type_key][$k]);
                            }
                        }
                        if(count($pay_app_support_bank[$bank_type_key]) == 0)
                        {
                            unset($pay_app_support_bank[$bank_type_key]);
                        }
                    }
                    $aData[$key]['support_bank'] = $pay_app_support_bank;
                    $this->pagedata['support_bank'] = $pay_app_support_bank;
                }
            // }
        }
        $this->pagedata['bankimg'] = app::get('ectools')->res_url.'/images/bank';

        $membersData = kernel::single('b2c_user_object')->get_members_data(array('members'=>'advance'));
        $this->pagedata['total'] = $membersData['members']['advance'];
        $this->pagedata['payments'] = $aData;
        $this->pagedata['member_id'] = $this->app->member_id;
        $this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'balance'));

        $this->output();
    }

    public function balance($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的预存款'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $member = $this->app->model('members');
        $mem_adv = $this->app->model('member_advance');
        $items_adv = $mem_adv->get_list_bymemId($this->app->member_id);
        $count = count($items_adv);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $mem_adv->getList('*',array('member_id' => $this->app->member_id),$aPage['start'],$this->pagesize,'mtime desc');
        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'balance');
        $this->pagedata['browser'] = $this->get_browser();
        $this->pagedata['advlogs'] = $params['data'];
        $data = $member->dump($this->app->member_id,'advance');
        $this->pagedata['total'] = $data['advance']['total'];
        // errorMsg parse.
        $this->pagedata['errorMsg'] = json_decode($_GET['errorMsg']);
        $this->output();
    }

    /*
     *会员中心 商品收藏
     * */
    function favorite($nPage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('商品收藏'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $membersData = kernel::single('b2c_user_object')->get_members_data(array('members'=>'member_lv_id'));
        $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$membersData['members']['member_lv_id'],$nPage,$this->pagesize);
        $imageDefault = app::get('image')->getConf('image.set');
        foreach ((array)$aData['data'] as $key => $value) {
            $aData['data'][$key]['url'] = $this->gen_url(array('app'=>'b2c','ctl'=>"site_member",'act'=>"receive",'arg0'=>$value['order_id']));;
        }
        $aProduct = $aData['data'];
        foreach((array)$aProduct as $k=>$v){
            if($v['nostore_sell']){
                $aProduct[$k]['store'] = 999999;
                $aProduct[$k]['product_id'] = $v['spec_desc_info'][0]['product_id'];
            }else{
                foreach((array)$v['spec_desc_info'] as $value){
                    $aProduct[$k]['product_id'] = $value['product_id'];
                    $aProduct[$k]['spec_info'] = $value['spec_info'];
                    $aProduct[$k]['price'] = $value['price'];
                    if(is_null($value['store']) ){
                        $aProduct[$k]['store'] = 999999;
                        break;
                    }elseif( ($value['store']-$value['freez']) > 0 ){
                        $aProduct[$k]['store'] = $value['store']-$value['freez'];
                        break;
                    }else{
                        $aProduct[$k]['store'] = false;
                    }
                }
            }
        }
        $this->pagedata['browser'] = $this->get_browser();
        $this->pagedata['favorite'] = $aProduct;
        $this->pagination($nPage,$aData['page'],'favorite');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $this->pagedata['current_page'] = $nPage;
        /** 接触收藏的页面地址 **/
        $this->pagedata['fav_ajax_del_goods_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'ajax_del_fav','args'=>array('goods')));
        $this->output();
    }

    /*
     *删除商品收藏
     * */
     function ajax_del_fav($gid=null,$object_type='goods'){
        if(!$gid){
            $this->splash('error',null,app::get('b2c')->_('参数错误！'),true);
        }
        if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,$object_type,$gid,$maxPage)){
            $this->splash('error',null,app::get('b2c')->_('移除失败！'));
        }else{
            $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);

            $current_page = $_POST['current'];
            if ($current_page > $maxPage){
                $current_page = $maxPage;
                $reload_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'favorite','args'=>array($current_page)));
                $this->splash('success',$url,app::get('b2c')->_('成功移除！'),true);
            }
            $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$this->member['member_lv'],$current_page,$this->pagesize);
            foreach ((array)$aData['data'] as $key => $value) {
                $aData['data'][$key]['url'] = $this->gen_url(array('app'=>'b2c','ctl'=>"site_member",'act'=>"receive",'arg0'=>$value['order_id']));;
            }
            $aProduct = $aData['data'];

            $oImage = app::get('image')->model('image');
            $imageDefault = app::get('image')->getConf('image.set');
            foreach((array)$aProduct as $k=>$v) {
                if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aProduct[$k]['image_default_id'] = $imageDefault['S']['default_image'];
                }
            }
            $this->pagedata['favorite'] = $aProduct;
            $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
            $this->splash('success',$url,app::get('b2c')->_('成功移除！'),array('request'=>true));
        }
    }

    function ajax_fav() {
        $object_type = $_POST['type'];
        $nGid = $_POST['gid'];
        if (!kernel::single('b2c_member_fav')->add_fav($this->app->member_id,$object_type,$nGid)){
            $this->splash('failed', app::get('b2c')->_('商品收藏添加失败！'), '', '', true);
        }else{
            $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);
            $this->splash('success',$url,app::get('b2c')->_('商品收藏添加成功'),array('request'=>true));
        }
    }

    /*
     *获取未读信息数目
     * */
    function get_unreadmsg_num(){
        $oMsg = kernel::single('b2c_message_msg');
        $num  = $oMsg->count(array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
        $data['inbox_num'] = $num ? $num : 0;
        echo json_encode($data);
    }
    /*
     *获取收件箱未读信息数量
     * */
    function get_msg_num(){
        $oMsg = kernel::single('b2c_message_msg');
        $row = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
        $this->pagedata['inbox_num'] = count($row)?count($row):0;
        $row = $oMsg->getList('*',array('has_sent' => 'false','author_id' => $this->app->member_id));
        $this->pagedata['outbox_num'] = count($row)?count($row):0;
    }
    //收件箱
    function inbox($nPage=1) {
        $this->get_msg_num();
        $oMsg = kernel::single('b2c_message_msg');
        $row = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
        $this->pagedata['inbox_num'] = count($row)?count($row):0;

        $row = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true'));
        $aData['data'] = $row;
        $aData['total'] = count($row);
        $count = count($row);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' =>'true'),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagedata['message'] = $params['data'];
        $this->pagedata['total_msg'] = $aData['total'];
        $this->pagination($nPage,$params['page'],'inbox');
        $this->output();
    }

    //草稿箱
    function outbox($nPage=1) {
        $this->get_msg_num();
        $oMsg = kernel::single('b2c_message_msg');
        $row = $oMsg->getList('*',array('has_sent' => 'false','author_id' => $this->app->member_id));
        $aData['data'] = $row;
        $aData['total'] = count($row);
        $count = count($row);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $oMsg->getList('*',array('has_sent' => 'false','author_id' => $this->app->member_id),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagedata['message'] = $params['data'];
        $this->pagedata['total_msg'] = $aData['total'];
        $this->pagination($nPage,$params['page'],'outbox');
        $this->pagedata['controller'] = "inbox";
        $this->output();
    }

    //已发送
    function track($nPage=1) {
        $this->get_msg_num();
        $oMsg = kernel::single('b2c_message_msg');
        $row = $oMsg->getList('*',array('author_id' => $this->app->member_id,'has_sent' => 'true','track' => 'true'));
        $aData['data'] = $row;
        $aData['total'] = count($row);
        $count = count($row);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $oMsg->getList('*',array('author_id' => $this->app->member_id,'has_sent' => 'true','track' => 'true'),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagedata['message'] = $params['data'];
        $this->pagedata['total_msg'] = $aData['total'];
        $this->pagination($nPage,$params['page'],'track');
        $this->pagedata['controller'] = "inbox";
        $this->output();
    }

    function view_msg(){
        $nMsgId = $_POST['comment_id'];
        $objMsg = kernel::single('b2c_message_msg');
        $aMsg = $objMsg->getList('comment',array('comment_id' => $nMsgId,'for_comment_id' => 'all','to_id'=>$this->app->member_id));
        if($aMsg[0]&&($aMsg[0]['author_id']!=$this->app->member_id&&$aMsg[0]['to_id']!=$this->app->member_id)){
            header('Content-Type:text/html; charset=utf-8');
            echo app::get('b2c')->_('对不起，您没有权限查看这条信息！');exit;
        }
        $objMsg->setReaded($nMsgId);
        $objAjax = kernel::single('b2c_view_ajax');
        echo $objAjax->get_html(htmlspecialchars_decode($aMsg[0]['comment']),'b2c_ctl_site_member','view_msg');
        exit;

    }

    function deleteMsg() {
        if(!empty($_POST['delete'])){
            $objMsg = kernel::single('b2c_message_msg');
            if($objMsg->check_msg($_POST['delete'],$this->app->member_id)){
                $objMsg->delete(array('object_type' => 'msg','comment_id' =>$_POST['delete']));
                $this->splash('success','reload',app::get('b2c')->_('删除成功'),$_POST['response_json']);
            }else{
                $this->splash('failed',null,app::get('b2c')->_('参数提交错误'),$_POST['response_json']);
            }
        }else{
            $this->splash('failed',null,app::get('b2c')->_('没有选中任何记录'),$_POST['response_json']);
        }
    }

    function send($nMsgId=false,$type='') {
        $this->get_msg_num();
        if($nMsgId){
            $objMsg = kernel::single('b2c_message_msg');
            $init =  $objMsg->dump($nMsgId);
            if($type == 'reply'){
                $objMsg->setReaded($nMsgId);
                $init['to_uname'] = $init['author'];
                $init['subject'] = "Re:".$init['title'];
                $init['comment'] = '';
                $this->pagedata['is_reply'] = true;
            }
            else{
                $init['subject'] = $init['title'];
            }
            $this->pagedata['init'] = $init;
            $this->pagedata['comment_id'] = $nMsgId;
        }
        $this->pagedata['controller'] = "inbox";
        $this->output();
    }

    function message($nMsgId=false, $status='send') { //给管理员发信件
        $this->get_msg_num();
        if($nMsgId){
            $objMsg = kernel::single('b2c_message_msg');
            $init =  $objMsg->dump($nMsgId);
            if($init['author_id'] == $this->app->member_id)
            {
                $this->pagedata['init'] = $init;
                $this->pagedata['msg_id'] = $nMsgId;
            }
        }
        if($status === 'reply'){
            $this->pagedata['reply'] = 1;
        }
        $this->pagedata['controller'] = "inbox";
        $this->output();
    }

    /*
     *发送站内信
     * */
    function send_msg(){
    	
    	//判断当前时间与session时间是否在5秒内
    	if (isset($_SESSION['last_send']) and (time()-$_SESSION['last_send']) <= 5){
    		return false;
    	}
		
        if(!isset($_POST['msg_to']) || $_POST['msg_to'] == '管理员'){
            $_POST['to_type'] = 'admin';
            $_POST['msg_to'] = 0;
        }else{
            $userObject = kernel::single('b2c_user_object');
            $to_id = $userObject->get_id_by_uname($_POST['msg_to']);
            if(!$to_id){
                $this->splash('failed',null,app::get('b2c')->_('收件人不存在'),$_POST['response_json']);
            }
            $_POST['to_id'] = $to_id;
        }
        if($_POST['subject'] && $_POST['comment']) {
            $objMessage = kernel::single('b2c_message_msg');
            $_POST['has_sent'] = $_POST['has_sent'] == 'false' ? 'false' : 'true';
            $_POST['member_id'] = $this->app->member_id;
            $_POST['uname'] = $this->member['uname'];
            $_POST['contact'] = $this->member['email'];
            $_POST['ip'] = $_SERVER["REMOTE_ADDR"];
            $_POST['subject'] = strip_tags($_POST['subject']);
            $_POST['comment'] = strip_tags($_POST['comment']);
            if($_POST['comment_id']){
                //$data['comment_id'] = $_POST['comment_id'];
                $_POST['comment_id'] = '';//防止用户修改comment_id
            }
			
			//设置session时间
			$_SESSION['last_send']=time();
			
            if( $objMessage->send($_POST) ) {
            if($_POST['has_sent'] == 'false'){
                $this->splash('success','reload',app::get('b2c')->_('保存到草稿箱成功'),$_POST['response_json']);
				//发送成功后释放session
				unset($_SESSION['last_send']);
            }else{
                $this->splash('success','reload',app::get('b2c')->_('发送成功'),$_POST['response_json']);
				unset($_SESSION['last_send']);
            }
            } else {
                $this->splash('failed',null,app::get('b2c')->_('发送失败'),$_POST['response_json']);
				unset($_SESSION['last_send']);
            }
        }
        else {
            $this->splash('failed',null,app::get('b2c')->_('必填项不能为空'),$_POST['response_json']);
        }
    }

    /*
     *会员中心 修改密码页面
     * */
    function security($type = ''){
        $member = $this->member;
        $obj_pam_members = app::get('pam')->model('members');
        $this->pagedata['is_nopassword'] = $obj_pam_members->is_nopassword($member['member_id']);
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('修改密码'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->output();
    }

    /*
     *保存修改密码
     * */
    function save_security(){
        $member = $this->member;
        $obj_pam_members = app::get('pam')->model('members');
        $passport_login = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'login'));
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'logout','arg0'=>$passport_login));
        $userPassport = kernel::single('b2c_user_passport');
        if($obj_pam_members->is_nopassword($member['member_id']) == 'true')
        {
            $result = $userPassport->reset_passport($this->app->member_id,$_POST['passwd']);
            if($result)
            {
                $this->splash('success',null,app::get('b2c')->_('修改成功'),true);
            }else{
                $this->splash('failed',null,app::get('b2c')->_('修改失败'),true);
            }
        }
        $result = $userPassport->save_security($this->app->member_id,$_POST,$msg);
        if($result){
            $this->splash('success',$url,$msg,true);
        }else{
            $this->splash('failed',null,$msg,true);
        }
    }

    /*
     *会员中心收货地址
     * */
    function receiver(){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('收货地址'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $objMem = $this->app->model('members');
        $this->pagedata['browser'] = $this->get_browser();
        $this->pagedata['receiver'] = $objMem->getMemberAddr($this->app->member_id);
        $this->pagedata['is_allow'] = (count($this->pagedata['receiver'])<10 ? 1 : 0);
        $this->pagedata['num'] = count($this->pagedata['receiver']);
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->output();
    }


    /*
     * 设置和取消默认地址，$disabled 2为设置默认1为取消默认
     */
    function set_default($addrId=null,$disabled=2){
        // $addrId = $_POST['addr_id'];
        if(!$addrId) $this->splash('failed',null, app::get('b2c')->_('参数错误'),true);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'receiver'));
        $obj_member = $this->app->model('members');
        $member_id = $this->app->member_id;
        if($obj_member->check_addr($addrId,$member_id)){
            if($obj_member->set_to_def($addrId,$member_id,$message,$disabled)){
                $this->splash('success',$url,$message,true);
            }else{
                $this->splash('error',null,$message,true);
            }
        }else{
            $this->splash('error',null, app::get('b2c')->_('参数错误'),true);
        }
    }

    /*
     *添加、修改收货地址
     * */
    function modify_receiver($addrId=null){
        // $addrId = $_POST['addr_id'];
        if(!$addrId){
            echo  app::get('b2c')->_("参数错误");exit;
        }
        $obj_member = $this->app->model('members');
        if($obj_member->check_addr($addrId,$this->app->member_id)){
            if($aRet = $obj_member->getAddrById($addrId)){
                $aRet['defOpt'] = array('0'=>app::get('b2c')->_('否'), '1'=>app::get('b2c')->_('是'));
                 $this->pagedata = $aRet;
            }else{
                echo  app::get('b2c')->_("修改的收货地址不存在");exit;
            }
            echo $this->fetch('site/member/modify_receiver.html');exit;
        }else{
            echo  app::get('b2c')->_("参数错误");exit;
        }
    }

    /*
     *保存收货地址
     * */
    function save_rec(){
        if(!$_POST['def_addr']){
            $_POST['def_addr'] = 0;
        }
        $save_data = kernel::single('b2c_member_addrs')->purchase_save_addr($_POST,$this->app->member_id,$msg);
        if(!$save_data){
            $this->splash('error',null,$msg,true);
        }
        $this->splash('success',$this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'receiver')),app::get('b2c')->_('保存成功'),true);
    }

    /*
     *删除收货地址
     * */
    function del_rec($addrId=null){
        // $addrId = $_POST['addr_id'];
        if(!$addrId) $this->splash('failed', null, app::get('b2c')->_('参数错误'),true);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'receiver'));
        $obj_member = $this->app->model('members');
        if($obj_member->check_addr($addrId,$this->app->member_id)){
            if($obj_member->del_rec($addrId,$message,$this->app->member_id)){
                $this->splash('success',$url,$message,true);
            }else{
                $this->splash('failed',$url,$message,true);
            }
        }else{
            $this->splash('failed',$url, app::get('b2c')->_('参数错误'),true);
        }
    }

    function exchange($cpnsId=null) {
        //积分设置的用途
        $site_point_usage = app::get('b2c')->getConf('site.point_usage');
        if($site_point_usage != '1'){
            $this->splash('failed',$url,app::get('b2c')->_('积分只用于抵扣，不能兑换'),true);
        }
        if(!$cpnsId) $this->splash('failed',$url, app::get('b2c')->_('参数错误'),true);
        $oExchangeCoupon = kernel::single('b2c_coupon_mem');
        $memberId = intval($this->app->member_id);//会员id号
        if($memberId){
            $membersData = $this->get_current_member();
            $cur_coupon_nums = $this->app->model('member_coupon')->count(array('cpns_id'=>$cpnsId,'member_id'=>$memberId));
            $coupons = $this->app->model('coupons');
            $cur_coupon = $coupons->dump($cpnsId);
            if($cur_coupon['cpns_max_num'] > 0 ){  //兼容老数据处理老数据还是无限制兑换
                if($cur_coupon_nums >= $cur_coupon['cpns_max_num']){
                    $this->splash('failed',$url,app::get('b2c')->_('您的兑换次数已达上限！'),true);
                }
            }
            if($membersData['point'] < $cur_coupon['cpns_point']){
                $this->splash('failed',$url,app::get('b2c')->_('您的积分不足！'),true);
            }
            if ($oExchangeCoupon->exchange($cpnsId,$memberId,$membersData['point'],$params)){
                $cpns_point = $params['cpns_point'];
                $member_point = $this->app->model('member_point');
                if($member_point->change_point($this->member['member_id'],-$cpns_point,$msg,'exchange_coupon',2,$memberId,$memberId,'exchange')){
                    $change_nums = $cur_coupon['cpns_max_num'] - $cur_coupon_nums -1;
                    $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'coupon'));
                    if($cur_coupon['cpns_max_num'] > 0 ){
                        $this->splash('success',$url,app::get('b2c')->_('兑换成功,您还可以兑换'.$change_nums.'张'),true);
                    }
                    else{
                        $this->splash('success',$url,app::get('b2c')->_('兑换成功'),true);
                    }
                }else{
                    $oExchangeCoupon->exchange_delete($params);
                    $this->splash('failed',$url,$msg,array('request'=>true));
                }
            }
        }else{
            $this->splash('failed',$url,app::get('b2c')->_('没有登录'),true);
        }
        $this->splash('failed',$url,app::get('b2c')->_('积分不足或兑换购物券无效'),true);
     }

    function download_ddvanceLog(){
        $charset = kernel::single('base_charset');
        $obj_member = $this->app->model('member_advance');
        $aData = $obj_member->get_list_bymemId($this->app->member_id);
        header('Pragma: no-cache, no-store');
        header("Expires: Wed, 26 Feb 1997 08:21:57 GMT");
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=advance_".date("Ymd").".csv");
        $out = app::get('b2c')->_("事件,存入金额,支出金额,当前余额,时间\n");
        foreach($aData as $v){
            $out .= $v['message'].",".$v['import_money'].",".$v['explode_money'].",".$v['member_advance'].",".date("Y-m-d H:i",$v['mtime'])."\n";
        }
        echo $charset->utf2local($out,'zh');
        exit;
    }

    /**
     * 添加留言
     * @params string order_id
     * @params string message type
     */
    public function addMsgData(){
        $timeHours = array();
        for($i=0;$i<24;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeHours[$v] = $v;
        }
        $timeMins = array();
        for($i=0;$i<60;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeMins[$v] = $v;
        }
        $this->pagedata['orderId'] = $order_id;
        $this->pagedata['msgType'] = $msgType;
        $this->pagedata['timeHours'] = $timeHours;
        $this->pagedata['timeMins'] = $timeMins;

        $this->output();
    }

    /**
     * 订单留言提交
     * @params null
     * @return null
     */
    public function toadd_order_msg()
    {
        if(!$_POST['msg']['orderid']){
            $this->splash(false,app::get('b2c')->_('参数错误'),true);
        }
        $this->begin(array('app'=>'b2c','ctl'=>'site_member','act'=>'orderdetail','arg0'=>$_POST['msg']['orderid']));
        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);

        $_POST['to_type'] = 'admin';
        $_POST['author_id'] = $this->app->member_id;
        $_POST['author'] = $this->member['uname'];
        $is_save = true;
        $obj_order_message = kernel::single("b2c_order_message");
        if ($obj_order_message->create($_POST))
            $this->end(true,app::get('b2c')->_('留言成功'),null,false,true);
        else
            $this->end(false,app::get('b2c')->_('留言失败'),null,false,true);
    }

    /*
        过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
    */
    function check_input($data){
        $aData = $this->arrContentReplace($data);
        return $aData;
    }

    function arrContentReplace($array){
        if (is_array($array)){
            foreach($array as $key=>$v){
                $array[$key] =     $this->arrContentReplace($array[$key]);
            }
        }
        else{
            $array = strip_tags($array);
        }
        return $array;
    }

    function set_read($comment_id=null,$object_type='ask'){
        if(!$comment_id) return ;
        $comment = kernel::single('b2c_message_disask');
        $comment->type = $object_type;
        $reply_data = $comment->getList('comment_id',array('for_comment_id' => $comment_id));
        foreach($reply_data as $v){
            $comment->setReaded($v['comment_id']);
        }

    }


    /*
     * 获取评论咨询的数据
     *
     * */
    public function getComment($nPage=1,$type='discuss'){
        //获取评论咨询基本数据
        $comment = kernel::single('b2c_message_disask');
        $aData = $comment->get_member_disask($this->app->member_id,$nPage,$type,$this->pagesize);
        $gids = array();
        $productGids = array();
        $comment_ids = array();
        foreach((array)$aData['data'] as $k => $v){
            $comment_ids[] = $v['comment_id'];
            if($v['type_id'] && !in_array($v['type_id'],$gids) ){
                $gids[] = $v['type_id'];
            }
            if(!$v['product_id'] && !in_array($v['type_id'],$productGids) ){
                $productGids[] = $v['type_id'];
            }

            if($v['items']){//统计回复未读的数量
                $unReadNum = 0;
                foreach($v['items'] as $val){
                    if($val['mem_read_status'] == 'false' ){
                        $unReadNum += 1;
                    }
                }
            }
            $aData['data'][$k]['unReadNum'] = $unReadNum;
        }

        //获取货品ID
        $productData = $productGids ? $this->app->model('products')->getList('goods_id,product_id',array('goods_id'=>$productGids,'is_default'=>'true')) : array();
        foreach((array) $productData as $p_row){
            $productList[$p_row['goods_id']] = $p_row['product_id'];
        }
        $this->pagedata['productList'] = $productList;

        //评论咨询商品信息
        $goodsData = $gids ? $this->app->model('goods')->getList('goods_id,name,price,thumbnail_pic,udfimg,image_default_id',array('goods_id'=>$gids)) : null;
        if($goodsData){
            foreach($goodsData as $row){
                $goodsList[$row['goods_id']] = $row;
            }
        }
        $this->pagedata['goodsList'] = $goodsList;

        //评论咨询私有的数据
        if($type == 'discuss'){
            $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
            if($this->pagedata['point_status'] == 'on'){//如果开启评分则获取评论评分
                $objPoint = $this->app->model('comment_goods_point');
                $goods_point = $objPoint->get_comment_point_arr($comment_ids);
                $this->pagedata['goods_point'] = $goods_point;
            }
        }else{
            $gask_type = unserialize($this->app->getConf('gask_type'));//咨询类型
            foreach((array)$gask_type as $row){
                $gask_type_list[$row['type_id']] = $row['name'];
            }
            $this->pagedata['gask_type'] = $gask_type_list;
        }
        return $aData;
    }

    function comment($nPage=1){
        if($this->app->getConf('comment.switch.discuss') == 'off'){
            kernel::single('site_router')->http_status(404);return;
        }
        //面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('评论与咨询'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $comment = $this->getComment($nPage,'discuss');
        $this->pagedata['commentList'] = $comment['data'];
        $this->pagination($nPage,$comment['page'],'comment');
        $this->output();
    }

    function ask($nPage=1){
        if($this->app->getConf('comment.switch.ask') == 'off'){
            kernel::single('site_router')->http_status(404);return;
        }
        //面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('评论与咨询'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $this->pagedata['controller'] = "comment";
        $comment = $this->getComment($nPage,'ask');
        $this->pagedata['commentList'] = $comment['data'];
        $this->pagedata['commentType'] = 'ask';
        $this->pagination($nPage,$comment['page'],'ask');
        $this->action_view = 'comment.html';
        $this->output();
    }

    /*
     *未评论商品
     **/
    public function nodiscuss($nPage=1){
        if($this->app->getConf('comment.switch.discuss') == 'off'){
            kernel::single('site_router')->http_status(404);return;
        }
        //面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('未评论商品'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        //获取会员已发货的商品日志
        $sell_logs = $this->app->model('sell_logs')->getList('order_id,product_id,goods_id',array('member_id'=>$this->app->member_id));
        //获取会员已评论的商品
        $comments = $this->app->model('member_comments')->getList('order_id,product_id',array('author_id'=>$this->app->member_id,'object_type'=>'discuss','for_comment_id'=>'0'));
        $data = array();
        if($comments){
            foreach((array)$comments as $row){
                if($row['order_id'] && $row['product_id']){
                    $data[$row['order_id']][$row['product_id']] = $row['product_id'];
                }
            }
        }

        foreach((array)$sell_logs as $key=>$log_row){
            if($data && $data[$log_row['order_id']][$log_row['product_id']] == $log_row['product_id']){
                unset($sell_logs[$key]);
            }else{
                $filter['order_id'][] = $log_row['order_id'];
                $filter['product_id'][] = $log_row['product_id'];
                $filter['item_type|noequal'] = 'gift';
            }
        }

        $orderItemModel = app::get('b2c')->model('order_items');
        $limit = $this->pagesize;
        $start = ($nPage-1)*$limit;
        $i = 0;
        $nogift = $orderItemModel->getList('order_id,product_id',$filter);
        if($nogift){
            foreach($nogift as $row){
                $tmp_nogift_order_id[] = $row['order_id'];
                $tmp_nogift_product_id[] = $row['product_id'];
            }
        }
        foreach((array)$sell_logs as $key=>$log_row){
            if(in_array($log_row['order_id'],$tmp_nogift_order_id) && in_array($log_row['product_id'],$tmp_nogift_product_id) ){//剔除赠品,赠品不需要评论
                if($i >= $start && $i < ($nPage*$limit) ){
                    $sell_logs_data[] = $log_row;
                    $gids[] = $log_row['goods_id'];
                }
                if($nogift){
                    $i += 1;
                }
            }
        }
        $totalPage = ceil($i/$limit);
        if($nPage > $totalPage) $nPage = $totalPage;

        $this->pagedata['list'] = $sell_logs_data;
        $this->pagination($nPage,$totalPage,'nodiscuss');

        if($gids){
            //获取商品信息
            $goodsData = $this->app->model('goods')->getList('goods_id,name,image_default_id',array('goods_id'=>$gids));
            $goodsList = array();
            foreach((array)$goodsData as $goods_row){
                $goodsList[$goods_row['goods_id']]['name'] = $goods_row['name'];
                $goodsList[$goods_row['goods_id']]['image_default_id'] = $goods_row['image_default_id'];
            }
            $this->pagedata['goods'] = $goodsList;

            $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
            $this->pagedata['verifyCode'] = $this->app->getConf('comment.verifyCode');
            if($this->pagedata['point_status'] == 'on'){
                //评分类型
                $comment_goods_type = $this->app->model('comment_goods_type');
                $this->pagedata['comment_goods_type'] = $comment_goods_type->getList('*');
                if(!$this->pagedata['comment_goods_type']){
                    $sdf['type_id'] = 1;
                    $sdf['name'] = app::get('b2c')->_('商品评分');
                    $addon['is_total_point'] = 'on';
                    $sdf['addon'] = serialize($addon);
                    $comment_goods_type->insert($sdf);
                    $this->pagedata['comment_goods_type'] = $comment_goods_type->getList('*');
                }
            }

            $this->pagedata['submit_comment_notice'] = $this->app->getConf('comment.submit_comment_notice.discuss');
        }
        $this->output();
    }

     ##缺货登记
    function notify($nPage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('到货通知'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        foreach ($aData['data'] as $key => $value) {
            $aData['data'][$key]['url'] = $this->gen_url(array('app'=>'b2c','ctl'=>"site_member",'act'=>"receive",'arg0'=>$value['order_id']));;
        }
        $membersData = kernel::single('b2c_user_object')->get_members_data(array('members'=>'member_lv_id'));
        $oMem = $this->app->model('member_goods');
        $aData = $oMem->get_gnotify($this->app->member_id,$membersData['members']['member_lv_id'],$nPage);
        $this->pagedata['browser'] = $this->get_browser();
        $this->pagedata['notify'] = $aData['data'];
        $this->pagination($nPage,$aData['page'],'notify');
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['setting'] = $setting;
        $this->pagedata['member_id'] = $this->app->member_id;
        $this->output();
    }

    ##删除缺货登记
    function del_notify($pid=null){
        $member_id = $this->app->member_id;
        if(!$pid || !$member_id) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'),true);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'notify'));
        $member_goods= $this->app->model('member_goods');
        if($member_goods->getList('gnotify_id',array('product_id' => $pid,'member_id' => $this->app->member_id))){
            if($member_goods->delete(array('product_id'=>$pid,'member_id'=>$this->app->member_id))){
                $this->splash('success',$url,app::get('b2c')->_('移除成功'),true);
            }else{
                $this->splash('failed',null,app::get('b2c')->_('没有选中任何记录'),true);
            }
        }else{
            $this->splash('failed',null,app::get('b2c')->_('没有选中任何记录'),true);
        }

    }

    function coupon($nPage=1) {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的优惠券'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $oCoupon = kernel::single('b2c_coupon_mem');
        $aData = $oCoupon->get_list_m($this->app->member_id,$nPage);
        if ($aData) {
            foreach ($aData as $k => $item) {
                if ($item['coupons_info']['cpns_status'] !=1) {
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('此种优惠券已取消');
                    continue;
                }

                $member_lvs = explode(',',$item['time']['member_lv_ids']);
                if (!in_array($this->member['member_lv'],(array)$member_lvs)) {
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('本级别不准使用');
                    continue;
                }

                $curTime = time();
                if ($curTime>=$item['time']['from_time'] && $curTime<$item['time']['to_time']) {
                    if ($item['memc_used_times']<$this->app->getConf('coupon.mc.use_times')){
                        if ($item['coupons_info']['cpns_status']){
                            $aData[$k]['memc_status'] = app::get('b2c')->_('可使用');
                        }else{
                            $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券已作废');
                        }
                    }else{
                        $aData[$k]['coupons_info']['cpns_status'] = false;
                        if($item['disabled'] == 'busy'){
                            $aData[$k]['memc_status'] = app::get('b2c')->_('使用中');
                        }else{
                            $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券次数已用完');
                        }
                    }
                }else{
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('还未开始或已过期');
                }
            }
        }

        $total = $oCoupon->get_list_m($this->app->member_id);
        $this->pagination($nPage,ceil(count($total)/$this->pagesize),'coupon');
        $this->pagedata['browser'] = $this->get_browser();
        $this->pagedata['coupons'] = $aData;
        $this->output();
    }

    /*
     * 积分兑换优惠卷
     * */
    function couponExchange($page=1) {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('积分兑换优惠卷'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $pageLimit = $this->pagesize;
        $oExchangeCoupon = kernel::single('b2c_coupon_mem');
        $filter = array('ifvalid'=>1);
        $site_point_usage = $this->app->getConf('site.point_usage');
        $this->pagedata['browser'] = $this->get_browser();
        $this->pagedata['site_point_usage'] = ($site_get_policy_method != '1' && $site_point_usage == '1') ? 'true' : 'false';
        if ($aExchange = $oExchangeCoupon->get_list()) {
            foreach ($aExchange as $k => $item) {
                $member_lvs = explode(',',$item['time']['member_lv_ids']);
                if (!in_array($this->member['member_lv'],(array)$member_lvs)) {
                    unset($aExchange[$k]);
                    continue;
                }
            }
            $this->pagedata['couponList'] = $aExchange;
        }
        $this->output();
    }

    /**
     * 重新购买
     * @param  int  $order_id [description]
     * @param  boolean $archive  [description]
     */
    public function reAddCart($order_id,$archive=false){
        $url = $this->gen_url(array('app'=>'b2c', 'ctl'=>'site_cart'));
        if(!$order_id){
            $this->splash('error',$url);
        }
        $orderItemObj = $archive ? app::get('b2c')->model('archive_order_items') : app::get('b2c')->model('order_items');
        $ordersData = $orderItemObj->getList('*',array('order_id'=>$order_id));
        foreach($ordersData as $row){
            $product_id[] = $row['product_id'];
            if($row['item_type'] == 'product'){
                $cartData[$row['obj_id']]['goods']['goods_id'] = $row['goods_id'];
                $cartData[$row['obj_id']]['goods']['product_id'] = $row['product_id'];
                $cartData[$row['obj_id']]['goods']['num'] = 1;
                $cartData[$row['obj_id']][0] = 'goods';
            }elseif($row['item_type'] == 'adjunct'){
                $cartData[$row['obj_id']]['goods']['adjunct'][0][$row['product_id']] = 1;
            }
        }
        //预售商品进入再次购买的判断
        $prepare=kernel::service('prepare_goods');
        if($prepare)
        {
            $pre=$prepare->get_product_buyagain($product_id);

            foreach ($pre as $key => $value) {
                $prep[] = $value['promotion_type'];
            }
             //echo '<pre>';print_r($prep);exit();
            if (!empty($prep))
            {
                $msg = app::get('b2c')->_('预售商品不能加入购物车！');
                if(in_array('prepare', $prep)){
                    $this->splash('error',$url,$msg);
                }
            }
        }
        $obj_cart_object = kernel::single('b2c_cart_objects');
        $obj_goods = kernel::single('b2c_cart_object_goods');
        foreach($cartData as $goods){
            $obj_ident = $obj_cart_object->add_object($obj_goods, $goods, $msg);
        }

        $this->splash('error',$url);

    }

    public function securitycenter(){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('安全中心'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $member_id = $this->app->member_id;

        $MemberData = app::get('b2c')->model('members')->getRow('*',array('member_id'=>$member_id));

        $pamMemberData = app::get('pam')->model('members')->getList('*',array('member_id'=>$member_id));
        if($MemberData['pay_password']){
            $verify['pay_password'] = true;
        }
        $deposit = app::get('ectools')->model('payment_cfgs')->getPaymentInfo('deposit');
        if($deposit['pay_status'] == 'true'){
            $verify['pay_status'] = true;
        }
        foreach($pamMemberData as $row){
        if($row['login_type'] == 'mobile' && $row['disabled'] == 'false'){
          $data['mobile'] = $row['login_account'];
          $verify['mobile'] = true;
        }

        if($row['login_type'] == 'email' && $row['disabled'] == 'false'){
          $data['email'] = $row['login_account'];
          $verify['email'] = true;
        }
      }

      $this->pagedata['data'] = $data;
      $this->pagedata['verify'] = $verify;
      $this->output();
    }

    function verify($verifyType='verifymobile') {
      unset($_SESSION['vcodeVerifykey']['activation']);
      $member_id = $this->app->member_id;
      $pamMemberData = app::get('pam')->model('members')->getList('*',array('member_id'=>$member_id));
      foreach($pamMemberData as $row){
        if($row['login_type'] == 'mobile' && $row['disabled'] == 'false'){
          $data['mobile'] = $row['login_account'];
          $mobile = mb_substr($row['login_account'],0,3).'****'.mb_substr($row['login_account'],7,4);
          $show['mobile'] =$mobile;
          $verify['mobile'] = true;
        }
        if($row['login_type'] == 'email' && $row['disabled'] == 'false'){
          $data['email'] = $row['login_account'];
          $email_array = explode('@',$row['login_account']);
          $email = mb_substr($email_array[0],0,1).'***@'.$email_array[1];
          $show['email'] = $email;
          $verify['email'] = true;
        }
      }
      $MemberErrDate = app::get('b2c')->model('members_error')->getRow('*',array('member_id'=>$member_id,'type'=>'possword'));
      $datetime = date('Y-m-d',time());
      if($datetime == date('Y-m-d',$MemberErrDate['etime']) && $MemberErrDate['error_num']>=3){
        $this->pagedata['show_varycode'] = true;
      }
      $this->pagedata['site_sms_valide'] = $this->app->getConf('site.sms_valide');
      $this->pagedata['verifyType'] = $verifyType;
      $this->pagedata['verify'] = $verify;
      $this->pagedata['data'] = $data;
      $this->pagedata['show'] =$show;
      $this->output();
    }

    public function verify_vcode(){
      $send_type = $_POST['send_type'];
      if($_POST['verifyType'] == 'cancelpaypassword'){
        $act_name = 'verify3';
        if(app::get('b2c')->model('members')->update(array('pay_password'=>null),array('member_id'=>$this->app->member_id))){
            app::get('b2c')->model('members_error')->update(array('error_num'=>'0'),array('member_id'=>$this->app->member_id,'type'=>'check'));
        }
      }else{
        $act_name = 'verify2';
      }
      if(!empty($_POST['show_varycode']) && !base_vcode::verify('b2c',$_POST['verifycode'])){
        $msg = app::get('b2c')->_('验证码错误');
        $this->splash('failed',null,$msg,true);exit;
      }
      if( isset($_POST['password']) ){
        $pamMembersModel = app::get('pam')->model('members');
        $pamData = $pamMembersModel->getList('login_password,password_account,createtime',array('member_id'=>$this->app->member_id));
        $use_pass_data['login_name'] = $pamData[0]['password_account'];
        $use_pass_data['createtime'] = $pamData[0]['createtime'];
        $login_password = pam_encrypt::get_encrypted_password(trim($_POST['password']),'member',$use_pass_data);
        if($login_password !== $pamData[0]['login_password']){
          $msg=app::get('b2c')->_('您输入的密码与账号不匹配');
          $MemberErrDate = app::get('b2c')->model('members_error')->getRow('*',array('member_id'=>$this->app->member_id,'type'=>'possword'));
          if(!$MemberErrDate){
            $datetime = time();
            $error_msg = array('member_id'=>$this->app->member_id,'etime'=>$datetime,'error_num'=>1,'type'=>'possword');
            app::get('b2c')->model('members_error')->save($error_msg);
          }else{
            $datetime = date('Y-m-d',time());
            if($datetime == date('Y-m-d',$MemberErrDate['etime'])){
              $error_num = $MemberErrDate['error_num']+1;
            }else{
              $error_num = 1;
            }
            app::get('b2c')->model('members_error')->update(array('error_num'=>$error_num,'etime'=>time()),array('member_id'=>$this->app->member_id,'type'=>'possword'));
          }
          if($error_num ==3){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'verify','arg0'=>$_POST['verifyType']));
            $this->splash('success',$url);exit;
          }
          $this->splash('failed',null,$msg,true);exit;
        }else{
          app::get('b2c')->model('members_error')->update(array('error_num'=>'0'),array('member_id'=>$this->app->member_id,'type'=>'possword'));
          $_SESSION['vcodeVerifykey']['activation'] = 'true';
          $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>$act_name,'arg0'=>$_POST['verifyType']));
          $this->splash('success',$url);
        }
      }
      $userVcode = kernel::single('b2c_user_vcode');
      if( !$userVcode->verify($_POST['vcode'][$send_type],$_POST[$send_type],'activation')){
        $msg = app::get('b2c')->_('验证码错误');
        $this->splash('failed',null,$msg,true);exit;
      }

      $_SESSION['vcodeVerifykey']['activation'] = 'true';
      $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>$act_name,'arg0'=>$_POST['verifyType']));
      $this->splash('success',$url);
    }

    function verify2($verifyType){
        if( !$_SESSION['vcodeVerifykey']['activation'] ){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'verify','arg0'=>$verifyType));
            $this->redirect($url);
        }
        $userPassport = kernel::single('b2c_user_passport');
        $member_id = $userPassport->userObject->get_member_id();
        $arr_colunms = $userPassport->userObject->get_pam_data('login_account',$member_id);
        $this->pagedata['site_sms_valide'] = $this->app->getConf('site.sms_valide');
        $this->pagedata['verifyType'] = $verifyType;
        $this->pagedata['data'] = $arr_colunms;
        $this->output();
    }

    function verify_vcode2($verifyType){
      unset($_SESSION['vcodeVerifykey']['activation']);
      if($verifyType == 'setpaypassword' || $verifyType == 'verifypaypassword'){
        $pay_password = $_POST['pay_password'];
        $re_pay_password = $_POST['re_pay_password'];
        if($pay_password !== $re_pay_password){
          $msg = app::get('b2c')->_('您输入的密码不一致');
          $this->splash('failed',null,$msg,true);exit;
        }
        if(mb_strlen($pay_password)<6 || mb_strlen($re_pay_password)<6){
          $msg = app::get('b2c')->_('请输入正确格式的密码');
          $this->splash('failed',null,$msg,true);exit;
        }
        $password = pam_encrypt::get_encrypted_password(trim($pay_password),pam_account::get_account_type($this->app->app_id),$use_pass_data);
        if(app::get('b2c')->model('members')->update(array('pay_password'=>$password),array('member_id'=>$this->app->member_id))){
            app::get('b2c')->model('members_error')->update(array('error_num'=>'0'),array('member_id'=>$this->app->member_id,'type' => 'check'));
        }
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'verify3','arg0'=>$verifyType));
        $this->splash('success',$url);
      }
      else{
        $send_type = $_POST['send_type'];
        $userVcode = kernel::single('b2c_user_vcode');
        if( !$userVcode->verify($_POST['vcode'],$_POST['uname'],$send_type)){
          $msg = app::get('b2c')->_('验证码错误');
          $this->splash('failed',null,$msg,true);exit;
        }

        $userPassport = kernel::single('b2c_user_passport');
        $accountType = $userPassport->get_login_account_type($_POST['uname']);
        if($_POST['send_type'] == 'reset'){
          if( !$userPassport->set_new_account($this->app->member_id,trim($_POST['uname']),$msg) ){
            $msg = $msg ? $msg : app::get('b2c')->_('修改信息失败');
            $this->splash('failed',null,$msg,true);exit;
          }
        }else{
          if( !app::get('pam')->model('members')->update(array('login_account'=>$_POST['uname'],'disabled'=>'false'),array('member_id'=>$this->app->member_id,'login_type'=>$accountType)) ){
            $msg = app::get('b2c')->_('重置信息失败');
            $this->splash('failed',null,$msg,true);exit;
          }
        }
        //增加会员同步 2012-05-15
        if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
            $member_rpc_object->modifyActive($this->app->member_id);
        }
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'verify3','arg0'=>$verifyType));
        $this->splash('success',$url);
      }
    }
    function verify3($verifyType){
        $this->pagedata['verifyType'] = $verifyType;
        $this->output();
    }

    function cancel($order_id){
        $this->pagedata['cancel_order_id'] = $order_id;
        $this->page('site/member/order_cancel_reason.html');

    }

    function docancel(){
        $arrMember = kernel::single('b2c_user_object')->get_current_member(); //member_id,uname
        //开启事务处理
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();

        $order_cancel_reason = $_POST['order_cancel_reason'];
        if($order_cancel_reason['reason_type'] == 7 && !$order_cancel_reason['reason_desc'])
        {
            $this->splash('error','','请输入详细原因',true);
        }
        if(strlen($order_cancel_reason['reason_desc'])>150)
        {
            $this->splash('error','','详细原因过长，请输入50个字以内',true);
        }
        if($order_cancel_reason['reason_type'] != 7 && strlen($order_cancel_reason['reason_desc']) > 0)
        {
            $order_cancel_reason['reason_desc'] = '';
        }
        $order_cancel_reason = utils::_filter_input($order_cancel_reason);
        $order_cancel_reason['cancel_time'] = time();
        $mdl_order = app::get('b2c')->model('orders');
        $sdf_order_member_id = $mdl_order->getRow('member_id', array('order_id'=>$order_cancel_reason['order_id']));
        if($sdf_order_member_id['member_id'] != $arrMember['member_id'])
        {
            $db->rollback();
            $this->splash('error','',"请勿取消别人的订单",true);
            return;
        }

        $mdl_order_cancel_reason = app::get('b2c')->model('order_cancel_reason');
        $result = $mdl_order_cancel_reason->save($order_cancel_reason);
        if(!$result)
        {
            $db->rollback();
            $this->splash('error','',"订单取消原因记录失败",true);
        }
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_cancel($order_cancel_reason['order_id'],'',$message))
        {
            $db->rollback();
            $this->splash('error','',$message,true);
        }

        $sdf['order_id'] = $order_cancel_reason['order_id'];
        $sdf['op_id'] = $arrMember['member_id'];
        $sdf['opname'] = $arrMember['uname'];
        $sdf['account_type'] = 'member';

        $b2c_order_cancel = kernel::single("b2c_order_cancel");
        if ($b2c_order_cancel->generate($sdf, $this, $message))
        {
            if($order_object = kernel::service('b2c_order_rpc_async')){
                $order_object->modifyActive($sdf['order_id']);
            }
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            $db->commit($transaction_status);
            $obj_coupon = kernel::single("b2c_coupon_order");
            if( $obj_coupon ){
                $obj_coupon->use_c($sdf['order_id']);
            }
            $this->splash('success',$url,"订单取消成功",true);
        }
        else
        {
            $db->rollback();
            $this->splash('error','',"订单取消失败",true);
        }
    }
    
    public function signin(){
        $site_checkout_login_point_open = $this->app->getConf('site.checkout.login_point.open');
        $site_login_point_num = $this->app->getConf('site.login_point.num');
        if($site_checkout_login_point_open == 'false')
        {
            $msg = '未开启签到送积分功能';
            $this->splash('error','',$msg,true);
        }

        $signin_obj = $this->app->model('member_signin');
        $member_id = $this->app->member_id;
        $signin_date = date('Y-m-d');

        if($signin_obj->exists_signin($member_id,$signin_date))
        {
            $msg = '您今天已经签到过';
            $this->splash('error','',$msg,true);
        }
        $data = array(
            'member_id' => $member_id,
            'signin_date' => $signin_date,
            'signin_time' => time(),
            'point' => $site_login_point_num
        );
        $result = kernel::single('b2c_member_signin')->sign($data);

        if($result){
            $msg = '签到成功，获得'.$site_login_point_num.'积分';
            $this->splash('success','',$msg,true);
        }else{
            $msg = '签到失败';
            $this->splash('error','',$msg,true);
        }
    }
    
    function receive($order_id){
        $arrMember = kernel::single('b2c_user_object')->get_current_member();
        $mdl_order = app::get('b2c')->model('orders');
        $sdf_order_member_id = $mdl_order->getRow('member_id', array('order_id'=>$order_id));
        $sdf_order_member_id['member_id'] = (int) $sdf_order_member_id['member_id'];
        if($sdf_order_member_id['member_id'] != $arrMember['member_id'])
        {
            $this->splash('error',null,'请勿操作别人的收货',true);exit;
        }else{
            $arr_updates = array('order_id'=>$order_id,'received_status' =>'1','received_time'=>time());
            $mdl_order->save($arr_updates);
            $delivery_mdl = app::get('b2c')->model('order_delivery_time');
            $delivery_mdl->delete(array('order_id' => $order_id));
            $orderLog = $this->app->model("order_log");
            $log_text = serialize($log_text);
            $sdf_order_log = array(
                'rel_id' => $order_id,
                'op_id' => $arrMember['member_id'],
                'op_name' => (!$arrMember['member_id']) ? app::get('b2c')->_('顾客') : $arrMember['uname'],
                'alttime' => time(),
                'bill_type' => 'order',
                'behavior' => 'receive',
                'result' => 'SUCCESS',
                'log_text' => '用户已确认收货！',
            );
            if($orderLog->save($sdf_order_log)){
                $this->splash('success',null,'已完成收货',true);exit;
            }else{
                $this->splash('error',null,'收货失败',true);exit;
            }
        }
    }
}
