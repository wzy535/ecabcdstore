<?php
class ectools_finder_payment_list{
    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=ectools&ctl=payment&act=edit_list&list_id='.$row['list_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finder_id='.$_GET['_finder']['finder_id'].'" target="dialog::{title:\''.app::get('ectools')->_('编辑收款帐号信息').'\',width:700,height:300}"><span>'.app::get('ectools')->_('编辑').'</span></a>';
    }
}
