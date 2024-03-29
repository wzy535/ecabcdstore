<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class expire_ctl_admin_product extends desktop_controller{
    var $workground = 'expire.wrokground.product';
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    public function index(){
       /* $nextWeek = strtotime('+1 week');
        $this->finder('b2c_mdl_goods',array(
            'title' => app::get('b2c')->_('临期商品列表'),
            'allow_detail_popup'    => true,
            'base_filter'   => array(
                'order_refer'   => 'local',
                'disabled'      => 'false',
                'filter_sql'    => 'expire_date<' . $nextWeek,
                //               'filter_sql'    => 'goods_id<4'
            ),
            'use_buildin_export'    => true,
            'use_buildin_set_tag'   => true,
            'use_buildin_recycle'   => false,
            'use_buildin_filter'    => true,
            'use_view_tab'          => true,
        ));*/
        $this->finder('gift_mdl_ref',array(
            'title'=>app::get('gift')->_('赠品'),
            'actions'=>array(
                array('label'=>app::get('gift')->_('添加赠品'),'icon'=>'add.gif','href'=>'index.php?app=gift&ctl=admin_gift&act=add', 'target'=>"_blank"),
            ),//'finder_aliasname'=>'gift_mdl_goods','finder_cols'=>'cat_id',
            'object_method' => array('count'=>'count_finder','getlist'=>'get_list_finder'),
            'use_view_tab'  => true,
        ));
    }
    public function _views()
    {
        var_dump(11111);
        $sub_menu = array(
            0 => array(
                'label' => app::get('b2c')->_('百度'),
                'optional'  => false,
                'filter'    => '',
                'addon'     => 3,
                'href'      => 'http://www.baidu.com',
            ),
            1 => array(
                'label' => app::get('b2c')->_('网易'),
                'optional'  => false,
                'filter'    => '',
                'addon'     => 3,
                'href'      => 'http://www.163.com',
            )
        );

        $sub_menu = array(
            0=>array('label'=>app::get('b2c')->_('待发货'),'optional'=>false,'newcount'=>false,'filter'=>array()),
            1=>array('label'=>app::get('b2c')->_('已发货'),'optional'=>false,'filter'=>array()),
        );
        return $sub_menu;
    }

    public function manualSearch()
    {
        $model = app::get('b2c')->model('goods');
        $nextWeek = strtotime('+1 week');
        $info  = $model->getList('*',array(
            'filter_sql'    => 'goods_id<3',
            //'filter_sql' => 'expire_date<=' . $nextWeek //一周内过期的商品
        ));
        $this->pagedata['app_name'] = "expire";
        $this->pagedata['info'] = $info;
        $this->page('admin/index.html');
    }

}
