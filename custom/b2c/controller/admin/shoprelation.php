<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_ctl_admin_shoprelation extends desktop_controller
{
    var $workground = 'desktop_other';
    var $certcheck = false;

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index()
    {
        $callback_url = '';
        $callback = urlencode(kernel::openapi_url('openapi.b2c.callback.shoprelation','callback'));
        $api_url = kernel::base_url(1).kernel::url_prefix().'/api';
        $ceti_id = base_certificate::get('certificate_id');
        $node_id = base_shopnode::node_id($this->app->app_id);
        $obj_user = kernel::single('desktop_user');
        $user_id = $obj_user->user_data['user_id'];
        $user_name = $obj_user->user_data['name'];
        $api_v = $this->app->getConf("api.local.version");
        $nodes_obj = app::get('b2c')->model('shop');
        $nodes = $nodes_obj->count( array('node_type'=>'ecos.taocrm','status'=>'bind'));

        $obj_policy = kernel::service("referrals.member_policy");
        if(is_object($obj_policy)){
            $is_referrals=true;
        }

        if($nodes > 0 && $is_referrals)
        {
            $this->finder('b2c_mdl_shop',array(
                'title'=>app::get('b2c')->_('数据互联') . app::get('b2c')->_('证书：') . $ceti_id . ', ' . app::get('b2c')->_('节点：') . $node_id,
                'actions' => array(
                    array('label'=>app::get('b2c')->_('新建绑定关系'),'icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_shoprelation&act=addnew','target'=>'_blank'),
                    array('label'=>app::get('b2c')->_('查看绑定情况'),'icon'=>'add.gif','onclick'=>'new Request({evalScripts:true,url:\'index.php?ctl=shoprelation&act=index&p[0]=accept&p[1]=' . $this->app->app_id . '&p[2]=' . $callback . '&p[3]=' . $api_url.'&p[4]=' . $user_id . '&p[5]=' . $user_name . '&p[6]=' . $api_v . '\'}).get()'),
                    array('label'=>app::get('b2c')->_('初始化用户数据到CRM'),'icon'=>'add.gif','onclick'=>'return confirm_init_point(\'index.php?app=b2c&ctl=admin_shoprelation&act=init_member\')'),
                    array('label'=>app::get('b2c')->_('同步未连通的用户数据到CRM'),'icon'=>'add.gif','onclick'=>'return confirm_init_point(\'index.php?app=b2c&ctl=admin_shoprelation&act=init_member&p[0]=1\')'),
                    array('label'=>app::get('b2c')->_('初始化积分数据到CRM'),'icon'=>'add.gif','onclick'=>'return confirm_init_point(\'index.php?app=b2c&ctl=admin_shoprelation&act=init_member_point\')'),
                    array('label'=>app::get('b2c')->_('初始化推荐关系'),'icon'=>'add.gif','onclick'=>'return confirm_init_point(\'index.php?app=b2c&ctl=admin_shoprelation&act=init_member_referrals\')'),
                ),
            ));
        }elseif($nodes > 0){
            $this->finder('b2c_mdl_shop',array(
                'title'=>app::get('b2c')->_('数据互联') . app::get('b2c')->_('证书：') . $ceti_id . ', ' . app::get('b2c')->_('节点：') . $node_id,
                'actions' => array(
                    array('label'=>app::get('b2c')->_('新建绑定关系'),'icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_shoprelation&act=addnew','target'=>'_blank'),
                    array('label'=>app::get('b2c')->_('查看绑定情况'),'icon'=>'add.gif','onclick'=>'new Request({evalScripts:true,url:\'index.php?ctl=shoprelation&act=index&p[0]=accept&p[1]=' . $this->app->app_id . '&p[2]=' . $callback . '&p[3]=' . $api_url.'&p[4]=' . $user_id . '&p[5]=' . $user_name . '&p[6]=' . $api_v . '\'}).get()'),
                    array('label'=>app::get('b2c')->_('初始化用户数据到CRM'),'icon'=>'add.gif','onclick'=>'return confirm_init_point(\'index.php?app=b2c&ctl=admin_shoprelation&act=init_member\')'),
                    array('label'=>app::get('b2c')->_('同步未连通的用户数据到CRM'),'icon'=>'add.gif','onclick'=>'return confirm_init_point(\'index.php?app=b2c&ctl=admin_shoprelation&act=init_member&p[0]=1\')'),
                    array('label'=>app::get('b2c')->_('初始化积分数据到CRM'),'icon'=>'add.gif','onclick'=>'return confirm_init_point(\'index.php?app=b2c&ctl=admin_shoprelation&act=init_member_point\')'),
                ),
            ));

        }else{
            $this->finder('b2c_mdl_shop',array(
                'title'=>app::get('b2c')->_('数据互联') . app::get('b2c')->_('证书：') . $ceti_id . ', ' . app::get('b2c')->_('节点：') . $node_id,
                'actions' => array(
                    array('label'=>app::get('b2c')->_('新建绑定关系'),'icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_shoprelation&act=addnew','target'=>'_blank'),
                    array('label'=>app::get('b2c')->_('查看绑定情况'),'icon'=>'add.gif','onclick'=>'new Request({evalScripts:true,url:\'index.php?ctl=shoprelation&act=index&p[0]=accept&p[1]=' . $this->app->app_id . '&p[2]=' . $callback . '&p[3]=' . $api_url.'&p[4]=' . $user_id . '&p[5]=' . $user_name . '&p[6]=' . $api_v . '\'}).get()'),
                ),
            ));
        }
    }

    public function addnew()
    {
        if ($_POST['shop'])
        {
            $this->begin();

            if ($_POST['shop']['shop_id'])
                $arr_data['shop_id'] = $_POST['shop']['shop_id'];
            $arr_data['name'] = $_POST['shop']['name'];
            foreach ($_POST['shop'] as $key=>$value)
            {
                $arr_data[$key] = $value;
            }
            $obj_shop = $this->app->model('shop');

            if ($obj_shop->save($arr_data))
            {
                $this->end(true, app::get('b2c')->_('添加成功！'));
            }
            else
            {
                $this->end(false,app::get('b2c')->_('添加失败！'));
            }
        }
        else
        {
            $this->singlepage('admin/page_shoprelation.html');
        }
    }

    public function init_member($not_all)
    {
        $this->begin();
        $member_obj = app::get('b2c')->model('members');
        if($not_all == null)
        {
            $members = $member_obj->getList('member_id');
        }else{
            $members = $member_obj->getList('member_id',array('crm_member_id'=>'0'));
        }
        $worker = 'b2c_tasks_member_createActive';
        foreach($members as $member)
        {
            system_queue::instance()->publish($worker, $worker, $member);
        }
        $this->end(true, app::get('b2c')->_('待初始化数据已全部加入队列'));
    }

    public function init_member_point($not_all)
    {
        $this->begin();
        $member_obj = app::get('b2c')->model('member_point');

        if($not_all == null)
        {
            $expired_time = strtotime(date('Y-m-d'));
            $sql = 'select id from sdb_b2c_member_point where status = "false"';
            $points = $member_obj->db->select($sql);
            $member_obj->tidy_data($points, '*');
        }else{
            //            $points = $member_obj->getList('member_id',array('crm_member_id'=>'0'));
        }
        $worker = 'b2c_tasks_member_point_changeActive';
        foreach($points as $point)
        {
            system_queue::instance()->publish($worker, $worker, $point);
        }
        $this->end(true, app::get('b2c')->_('待初始化数据已全部加入队列'));
    }

    public function init_member_referrals()
    {
        $this->begin();
        $worker = 'b2c_tasks_member_point_referrals';
        $b2c_members_model = app::get('b2c')->model('members');
        $result = $b2c_members_model->getList('member_id,crm_member_id');
        foreach($result as $val)
        {
            if(!empty($val['crm_member_id'])){
              system_queue::instance()->publish($worker, $worker, $val);
            }
        }
        $this->end(true, app::get('b2c')->_('待初始化数据已全部加入队列'));

    }


    public function showEdit($shop_id=0)
    {
        $obj_shop = $this->app->model('shop');
        $arr_shop = $obj_shop->dump($shop_id);

        if ($arr_shop)
        {
            $this->pagedata['shoprelation'] = $arr_shop;
        }

        $this->singlepage('admin/page_shoprelation.html');
    }
}
