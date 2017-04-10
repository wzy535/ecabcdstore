<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class expire_ctl_admin_product extends desktop_controller{
    var $workground = 'expire.wrokground.product';

    function index(){
        $nextWeek = strtotime('+1 week');
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
        ));
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
