<?php

class starbuy_finder_promotions_type{

    var$column_edit="编辑";
    function column_edit($row){
        if($row['bydefault'] == "false"){

        $result = '<a href="index.php?app=starbuy&ctl=admin_promotions_type&act=add_type&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['type_id'].'" target="dialog::{title:\'编辑类型\', width:420, height:400}">'.app::get('starbuy')->_('编辑').'</a>';
        return $result;
        }
    }
}
