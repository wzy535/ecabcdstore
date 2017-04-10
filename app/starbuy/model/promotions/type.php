<?php

class starbuy_mdl_promotions_type extends dbeav_model{

    function delete($filter, $subSdf = 'delete'){
        if($id = $filter['type_id']){
            $list = $this->getRow('name,type_id',array('type_id'=>$id,'bydefault'=>'true'));
            if($list){
                $this->recycle_msg = app::get('starbuy')->_('系统默认标签不能删除');
                return false;
            }
        }
        parent::delete($filter);

    }

    function pre_recycle($rows){
        foreach($rows as $v){
            $ids[] = $v['type_id'];
            if($v['bydefault'] == 'true'){
                $this->recycle_msg = app::get('starbuy')->_($v['name'].'是系统标签，不可删除');
                return false;
            }
        }
        $o = $this->app->model('special');
        $rows = $o->getList('special_id,name',array('type_id'=>$ids));
        if( $rows[0] ){
            $this->recycle_msg = app::get('starbuy')->_('标签已被 [组合促销]使用');
            return false;
        }
        return true;
    }

}
