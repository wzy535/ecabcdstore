<?php

class starbuy_finder_special{

    var $column_edit="编辑";
    var $column_edit_width="50";
    var $column_edit_order="1";
    var $detail_basic="详情";

    public function __construct($app)
    {
        $this->app = $app;

    }
    function column_edit($row){
        $result = '<a href="index.php?app=starbuy&ctl=admin_promotion&act=edit_rule&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['special_id'].'" target="_blank">'.app::get('b2c')->_('编辑').'</a>';
        return $result;
    }


    function detail_basic($row){
        $render = $this->app->render();
        $filter = array('special_id'=>$row);
        $obj_special_goods = kernel::single('starbuy_special_products');
        $detail = $obj_special_goods->getSpecialGoodsDetail($filter);
        $render->pagedata['special_goods'] = $detail;
        $render->pagedata['type_name'] = $obj_special_goods->getTypename($filter);
        return $render->fetch('admin/promotions/detail.html');
    }
}
