<?php

class b2c_version_checkversion {


    public function __construct( $app ) {
        $this->lowest_crm_version = 'v2.2.8';
        $this->app = $app;
    }

    public function is_bind_crm(){
        $nodes_obj = app::get('b2c')->model('shop');
        $nodes = $nodes_obj->count( array('node_type'=>'ecos.taocrm','status'=>'bind'));
        if($nodes > 0){
            return true;
        }
        else{
            return false;
        }
    }

    public function check_version(){
        if($this->is_bind_crm()){
            $use_crm_version=$this->use_crm_version();
            $result=$this->comparison_version($this->lowest_crm_version,$use_crm_version);
            return $result;
        }else{
            return false;
        }

    }
    public function use_crm_version(){
        $version_obj = kernel::single('b2c_apiv_exchanges_request_version');
        $version = $version_obj->getActive();
       //$version ='v2.2.5';
        return $version;
    }

    public function comparison_version($lowest_crm_version,$use_crm_version){
    	if($this->rule($use_crm_version) >= $this->rule($lowest_crm_version)){
    		return false;
    	}else{
    		$data = array('crm'=>array('lowest_crm_version'=>$lowest_crm_version));
            return $data;
    	}

    }

    public function rule($version){
        if(!empty($version)){
            $version = explode('.', substr($version,1)) ;
            if(is_array($version)){
            	$num = $version[0] * 100000 + $version[1] * 1000 + $version[2];
                return $num;
            }else{
            	return 0;
            }
        }else{
            return 0;
        }


    }


}
