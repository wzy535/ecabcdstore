<?php

class preparesell_prepare_prepare{

    function __construct($app){
        $this->app = $app;
        //$this->mdl_preparesell = app::get('preparesell')->model('preparesell');
        $this->mdl_preparesell_product = app::get('preparesell')->model('preparesell_goods');
    }


    function getSpecialProduct($filter){
        $prepare = $this->mdl_preparesell_product->getRow('*',array('product_id'=>$filter));
        return $prepare;
    }

   
}
