<?php

class starbuy_ctl_admin_promotions_type extends desktop_controller{

    function index(){
        $this->finder(
            'starbuy_mdl_promotions_type',
            array(
                'title'=>app::get('starbuy')->_('促销类型'),
                'actions'=>array(
                    array(
                        'label'=>app::get('starbuy')->_('添加类型'),
                        'target'=>'dialog::{title:\'添加规格\', width:420, height:400}',
                        'href'=>'index.php?app=starbuy&ctl=admin_promotions_type&act=add_type',
                    ),
                ),
            )
        );
    }

    function alertpages(){
        $this->pagedata['goto'] = urldecode($_GET['goto']);
        $this->singlepage('loadpage.html');
    }

    function add_type($id=""){

        $typemodel = kernel::single('starbuy_mdl_promotions_type');
        if($list = $typemodel->getList('*',array('type_id'=>$id))){
            $this->pagedata['typedata'] = $list[0];
        }
        $this->page('admin/promotions/add_type.html');
    }

    function save_type(){
        $postdata = $_POST['typedata'];
        $this->begin();
        $typemodel = kernel::single('starbuy_mdl_promotions_type');
        $result=$typemodel->save($postdata);
        $this->end($result);
    }
}
