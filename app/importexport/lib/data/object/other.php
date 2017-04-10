<?php
/*
 * 提供导出导出数据处理
 * */
class importexport_data_object_other extends importexport_data_object{

    /**
     * 实例对应的model
     *
     * @params string $class_model  例如:b2c_mdl_members
     */
    public function __construct($params){
        //实例化要导出或导入的model
        $class_model = $params['model'];
        $suffix = $params['suffix'];
        $model = substr(stristr($class_model,'mdl_'),4);
        $app_id = substr($class_model,0,strpos($class_model,'_mdl'));
        $this->model = app::get($app_id)->model($model);

        //导出导入数据组织扩展
        $object =  kernel::service('importexport.'.$class_model.'_'.$suffix);
        if( is_object($object) ){
            $this->extends = $object;
        }

        $this->set_group();
    }

    /*
     * 获取导出字段
     */
    public function get_title()
    {
        if( $this->extends && method_exists($this->extends, 'get_title') )
        {
            $title = $this->extends->get_title();
        }
        return $title;
    }
}
