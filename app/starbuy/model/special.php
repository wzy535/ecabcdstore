<?php

class starbuy_mdl_special extends dbeav_model{


    var $has_many = array(
        'products' => 'special_goods:replace',
        );

    function pre_recycle($row){

        foreach($row as $v){
            if($v['status'] == 'true' && $v['end_time'] >= time() && $v['release_time'] <= time()){
                $this->recycle_msg = app::get('starbuy')->_('活动未结束，不可删除');
                return false;
            }
        }
        return true;
    }


}
