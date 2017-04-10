<?php

class preparesell_finder_preparesell{

    var $column_edit = '编辑';
    var $detail_edit = '详细列表';
    //var $column_del = '删除';
    var $column_edit_width="50";
    var $column_edit_order="1";
    public function __construct($app)
    {
        $this->app = $app;

    }
    public function column_edit($row){
        return '<a href="index.php?app=preparesell&ctl=admin_preparesell&act=edit_rule&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['prepare_id'].'" target="_blank">'.app::get('b2c')->_('编辑').'</a>';
    }

   /* public function column_del($row){
        return '<a href="javascript:void(0);" onclick="if(confirm(\'确定要删除吗?\')) W.page(\'index.php?app=preparesell&ctl=admin_preparesell&act=del_rule&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['prepare_id'].'\');">'.app::get('b2c')->_('删除').'</a>';
    }*/
    
    public function detail_edit($row){
        $render = $this->app->render();
        $filter = array('prepare_id'=>$row);
        $obj_preparesell_goods = kernel::single('preparesell_prepare_goods'); 
        $details=$obj_preparesell_goods->getPrepareGoodsDetail($filter);
        $detail=$obj_preparesell_goods->getPrepareProductsDetail($filter);
        //echo '<pre>';print_r($detail);exit();
        $default_product = app::get('b2c')->model('products')->getList('product_id',array('goods_id'=>$details[0]['goods_id'],'is_default'=>'true'));
        if(!$default_product){
            $default_product = app::get('b2c')->model('products')->getList('product_id',array('goods_id'=>$details[0]['goods_id']));
        }
        $render->pagedata['url'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','full'=>1,'act'=>'index','arg'=>$default_product[0]['product_id']));
        $render->pagedata['reparesell_goods'] = $detail;
        return $render->fetch('admin/preparesell/detail.html');
    }

}
