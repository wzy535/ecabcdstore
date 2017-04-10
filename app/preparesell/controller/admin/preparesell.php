<?php
class preparesell_ctl_admin_preparesell extends desktop_controller
{
	function index()
	{
        $custom_actions[] = array('label'=>app::get('preparesell')->_('添加预售规则'),'href'=>'index.php?app=preparesell&ctl=admin_preparesell&act=add_rule','target'=>'_blank');
        $custom_actions[] = array('label'=>app::get('preparesell')->_('删除规则'),'submit'=>'index.php?app=preparesell&ctl=admin_delprepare&act=del_rules','target'=>'dialog');
        $custom_actions[] = array('label'=>app::get('preparesell')->_('预售订单统计'),'submit'=>'index.php?app=preparesell&ctl=admin_prepare_order&act=prepare_order_number','target'=>'dialog');
		$actions_base['title'] = app::get('preparesell')->_('预售规则');
        $actions_base['actions'] = $custom_actions;
        $actions_base['use_buildin_recycle'] = false;
        $actions_base['use_buildin_filter'] = true;
        $actions_base['use_view_tab'] = true;
        $this->finder('preparesell_mdl_preparesell',$actions_base);
	}


    public function _views()
    {
        $mdl_preparesell=$this->app->model('preparesell');
        $sub_menu = array(
            0=>array('label'=>app::get('preparesell')->_('未开启'),'optional'=>false,'filter'=>array('status'=>'false')),
            1=>array('label'=>app::get('preparesell')->_('未开始'),'optional'=>false,'filter'=>array('begin_time|than'=>time(),'status'=>'true')),
            2=>array('label'=>app::get('preparesell')->_('进行中'),'optional'=>false,'filter'=>array('end_time_final|bthan'=>time(),'begin_time|sthan'=>time(),'status'=>'true')),
            3=>array('label'=>app::get('preparesell')->_('过期'),'optional'=>false,'filter'=>array('end_time_final|sthan'=>time())),

        );

        foreach($sub_menu as $k=>$v){
            if($v['optional']==false){
                $show_menu[$k] = $v;
                if(is_array($v['filter'])){
                    $v['filter'] = array_merge(array(),$v['filter']);
                }else{
                    $v['filter'] = array();
                }
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;

                if($k==$_GET['view']){
                    $show_menu[$k]['newcount'] = true;
                    $show_menu[$k]['addon'] = $mdl_preparesell->count($v['filter']);
                }
                $show_menu[$k]['href'] = 'index.php?app=preparesell&ctl=admin_preparesell&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view']){
                $show_menu[$k] = $v;
            }
        }
        return $show_menu;
    }

	function add_rule()
	{
        //$this->pagedata['return_url'] = app::get('desktop')->router()->gen_url(array('app'=>'preparesell', 'ctl'=>'admin_preparesell', 'act'=>'get_goods_info'));
        $this->pagedata['filter'] = array(
            'goods_type'=>'normal',
            'promotion'=>'prepare',
            'marketable'=>'true',
            'nostore_sell'=>1
        );
        $this->pagedata['callback_ajax_url'] = app::get('desktop')->router()->gen_url(array('app'=>'preparesell', 'ctl'=>'admin_preparesell', 'act'=>'get_goods_spec'));
        $this->_public_data();
        $this->singlepage('admin/preparesell.html');
    }
    //页面异步请求的方法
    public function get_goods_spec () {
        $id = $_POST['id'];
        $arr = app::get('b2c')->model('products')->getList( '*',array('goods_id'=>$id) );
        $this->pagedata['specs'] = $arr;
        $this->display( 'admin/preparesell/spec/spec.html' );
    }
    //页面异步请求的方法
    public function get_goods_info () {
        $data = $_POST['data'];
        $arr = app::get('b2c')->model('goods')->dump_b2c( array('goods_id'=>$data[0]) );
        echo json_encode( array('name'=>$arr['name'],'bn'=>$arr['bn'],'store'=>$arr['store'],'goods_id'=>$arr['goods_id'],'image'=>$arr['image_default_id'], 'brief'=>$arr['brief']) );
    }
    //短信设置
    function _public_data(){

        $this->pagedata['remind_way'] = array(
            'email'=>app::get('preparesell')->_('邮件提醒'),
            'sms'=>app::get('preparesell')->_('手机短信'),
        );
    }
    //添加预售商品
    function save_rule()
    {
        $mdl_preparesell_goods = $this->app->model('preparesell_goods');
    	$mdl_preparesell = $this->app->model('preparesell');
        $mdl_goods = app::get('b2c')->model('goods');
    	$this->begin();
        $postdata = $this->_prepareRuleData($_POST);
        //判断是否是无库存可销售的商品
        $is_prepare=$mdl_goods->getRow('nostore_sell',array('goods_id'=>$postdata['goods_id']));
        if($is_prepare['nostore_sell']!=1)
        {
             $this->end(false,'请选择无库存也可销售的商品作为预售商品！' );
        }

        //添加预售商品以及规则
    	$result = $mdl_preparesell->save($postdata);
    	$this->end($result);

    }
    //数据处理
    function _prepareRuleData($params)
    {
        //预售时间处理
        $hour = $params['_DTIME_']['H'];
        $begin_h = $hour['begin_time'];
        $end_h = $hour['end_time'];
        $begin_h_final = $hour['begin_time_final'];
        $end_h_final = $hour['end_time_final'];
        $rule = $params['ruledata'];
        $rule['remind_time'] = ($params['remind_time'] && $rule['remind'] =="true") ? $params['remind_time'] : 0;
        $rule['timeout'] = $params['timeout'] ? $params['timeout'] : 0;
        if(!is_numeric($rule['timeout']))
        {
            $this->end(false,'提醒时间必须是数字！' );
        }
        if(!is_numeric($rule['remind_time']))
        {
            $this->end(false,'提醒时间必须是数字！' );
        }
        if(empty($rule['remind_time'])&&$rule['remind'] =="true")
        {
            $this->end(false,'提醒时间不能为空！' );
        }

        $rule['begin_time'] = strtotime($params['begin_time'].' '.$begin_h.':00:00');
        $rule['end_time'] = strtotime($params['end_time'].' '.$end_h.':00:00');
        $rule['begin_time_final'] = strtotime($params['begin_time_final'].' '.$begin_h_final.':00:00');
        $rule['end_time_final'] = strtotime($params['end_time_final'].' '.$end_h_final.':00:00');
        if($rule['begin_time'] >= $rule['end_time']){
            $this->end(false,'支付订金开始时间不能大于或等于结束时间！' );
        }
        if($rule['begin_time_final'] >= $rule['end_time_final']){
            $this->end(false,'支付尾款开始时间不能大于或等于支付订金结束时间！' );
        }
        if($rule['begin_time_final'] <= time()){
            $this->end(false,'支付尾款开始时间不能小于或等于当前时间！' );
        }
        if($rule['begin_time_final'] <= $rule['end_time']){
            $this->end(false,'支付尾款开始时间不能小于或等于结束时间！' );
        }
        if ($rule['begin_time'] >= $rule['begin_time_final']) {
            $this->end(false,'支付订金开始时间不能大于或等于支付尾款开始时间！' );
        }
        if ($rule['end_time'] >= $rule['end_time_final']) {
            $this->end(false,'支付订金结束时间不能大于或等于支付尾款结束时间！' );
        }

        //根据货品id获取货品价格预售价格
        //$rule['products'] = $this->getProduct($params);
        $rule['remind_time_send'] = $rule['begin_time_final'] - (strtotime('+'.$rule['remind_time'].' '.'hours')-time());
        $rule['remind_way'][count($rule['remind_way'])] = "msgbox";
        $rule['goods_id'] = $params['goods_id'];
        $rule['preparename'] = $params['preparename'];
        $rule['description'] = $params['description'];
        $rule['status'] = $params['status'];
        $rule['product_id'] = $params['to_prepare'];
        $rule['promotion_type'] = 'prepare';
        $rule['product'] = $params['product'];
        $rule['products'] = $this->getProduct($rule);
        return $rule;
    }
    /*
    *获取货品的信息
    */
    function getProduct($rule)
    {
        $product = app::get('b2c')->model('products');
        $product_detail = $product->getList('product_id,price',array('product_id|in'=>$rule['product_id']));
        foreach ($product_detail as $key => $value)
        {
            if($rule['product'][$value['product_id']]['prepare_price'] > $value['price'])
            {
                $this->end(false,'预售价格不能大于销售价！' );
            }
            if($rule['product'][$value['product_id']]['prepare_price']==null)
            {
                $this->end(false,'预售价格不能为空！' );
            }
            $product_detail[$key]['preparesell_price'] = $rule['product'][$value['product_id']]['prepare_price'] > $value['price'] ? $value['price'] : $rule['product'][$value['product_id']]['prepare_price'];
            $product_detail[$key]['promotion_price'] = $value['price'];
            $product_detail[$key]['preparename'] = $rule['preparename'];
            $product_detail[$key]['description'] = $rule['description'];
            $product_detail[$key]['status'] = $rule['status'];
            $product_detail[$key]['begin_time'] = $rule['begin_time'];
            $product_detail[$key]['end_time'] = $rule['end_time'];
            $product_detail[$key]['begin_time_final'] = $rule['begin_time_final'];
            $product_detail[$key]['end_time_final'] = $rule['end_time_final'];
            $product_detail[$key]['remind_way'] =  $rule['remind_way'];
            $product_detail[$key]['remind_time'] = $rule['remind_time'];
            $product_detail[$key]['remind_time_send'] = $rule['remind_time_send'];
            $product_detail[$key]['timeout'] = $rule['timeout'];
            $product_detail[$key]['promotion_type'] = $rule['promotion_type'];
            $product_detail[$key]['initial_num'] = $rule['product'][$value['product_id']]['initial_num'];
            if(empty($product_detail[$key]['initial_num']))
            {
                $this->end(false,'库存不能为空！' );
            }
        }
        return $product_detail;
    }

    /*
    *修改预售规则
    */
    function edit_rule($id){
        $mdl_preparesell = app::get('preparesell')->model('preparesell');
        $mdl_product = app::get('b2c')->model('products');
        $mdl_preparesell_goods = app::get('preparesell')->model('preparesell_goods');
        $preparesell=$mdl_preparesell->getRow('*',array('prepare_id'=>$id));
        if(!$preparesell['remind_time']){
            $preparesell['remind'] = "false";
            $preparesell['remind_time'] = null;
        }else{
            $preparesell['remind'] = "true";
        }
        $this->pagedata['ruleInfo'] = $preparesell;
        $product=$mdl_preparesell_goods->getList('product_id,preparesell_price,initial_num,status',array('prepare_id'=>$id));
        $product_id=$mdl_product->getList('product_id',array('goods_id'=>$preparesell['goods_id']));
        //获取货品id
        foreach ($product_id as $key => $value) {
            $product_id[$key]=$value['product_id'];
        }

        //为了下面赋价格的直给页面
        foreach ($product as $key => $value) {
            $price[$value['product_id']]['product_id']=$value['product_id'];
            $price[$value['product_id']]['preparesell_price']=$value['preparesell_price'];
            $price[$value['product_id']]['initial_num']=$value['initial_num'];
            $price[$value['product_id']]['status']=$value['status'];
        }
        $arr = $mdl_product->getList('*',array('product_id|in'=>$product_id));
        foreach ($arr as $key => $value) {
            $arr[$key]['prepare_price']=$price[$value['product_id']]['preparesell_price'];
            $arr[$key]['initial_num']=$price[$value['product_id']]['initial_num'];
            $arr[$key]['status']=$price[$value['product_id']]['status'];
        }
        $this->pagedata['specs'] = $arr;
        $this->pagedata['nowtime'] = time();
        $this->_public_data();
        $this->pagedata['filter'] = array(
            'goods_type'=>'normal',
            'promotion'=>'prepare',
            'marketable'=>'true',
            'nostore_sell'=>1
        );
        $this->pagedata['return_url'] = app::get('desktop')->router()->gen_url(array('app'=>'preparesell', 'ctl'=>'admin_preparesell', 'act'=>'get_goods_info'));
        $this->pagedata['callback_ajax_url'] = app::get('desktop')->router()->gen_url(array('app'=>'preparesell', 'ctl'=>'admin_preparesell', 'act'=>'get_goods_spec'));
        if($preparesell['begin_time'] <= time() && time() <= $preparesell['end_time_final']  && $preparesell['status'] == 'true' )
        {
            $this->singlepage('admin/prepare.html');
        }else{
            $this->singlepage('admin/preparesell.html');
        }
    }

    //删除规则
    public function del_rule($id)
    {
        $mdl_preparesell_goods = app::get('preparesell')->model('preparesell_goods');
        $mdl_preparesell = app::get('preparesell')->model('preparesell');
        $prepare=$mdl_preparesell->getRow('begin_time,end_time_final,goods_id,status',array('prepare_id'=>$id));
        $product=$mdl_preparesell_goods->getList('product_id',array('prepare_id'=>$id));
        foreach ($product as $key => $value) {
            $product_id[$key]=$value['product_id'];
        }


        $this->begin();

        //删除条件判断
        if($prepare['begin_time'] >= time() || $prepare['status']=='false' || $prepare['end_time_final'] <= time())
        {
            $del_pre=$mdl_preparesell->delete(array('prepare_id'=>$id),'delete');
            $del_rule=$mdl_preparesell_goods->delete(array('prepare_id'=>$id),'delete');
            $del=array($del_pre,$del_rule);
            $this->end($del,true,'删除成功');
        }else{
            $this->end(false,'活动未结束，不可删除');
        }

    }


    //用于挂件ajax请求获取商品地址
    public function ajax_get_goods_url()
    {
        $obj_preparesell = app::get('preparesell')->model('preparesell');
        $obj_products = app::get('b2c')->model('products');
        $goods_ids = $obj_preparesell->getList('goods_id', $filter);
        foreach($goods_ids as $k=>$v)
        {
            $fmt_goods_ids[$v['goods_id']] = $v['goods_id'];
        }
        $products_filter = array(
                'goods_id|in' => $fmt_goods_ids,
                'is_default' => 'true',
                'disabled' => 'false',
            );
        $products = $obj_products->getList('product_id,name', $products_filter);
        $url_array = array(
            'app'=>'b2c',
            'ctl'=>'site_product',
            'full'=>1,
            'act'=>'index',
        );
        foreach($products as $key=>$product)
        {
            $url_array['arg']=$product['product_id'];
            $url = app::get('site')->router()->gen_url($url_array);
            $products[$key]['url'] = $url;
        }
        $json_products = json_encode($products);
        echo $json_products;
        return;
    }
}
