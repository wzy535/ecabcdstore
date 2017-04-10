<?php

class starbuy_special_remind{
    /*
     *保存开团提醒方式
     *
     *
     */
    function __construct($app){
        $this->app = $app;
        $this->remind_mdl = kernel::single('starbuy_mdl_special_remind');
    }
    function save_remind($params){
        return $this->remind_mdl->save($params);
    }
}
