<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class trustlogin_mdl_trustlogin_cfg extends dbeav_model{


    function __construct(&$app){
        $this->app = $app;
        $this->columns = array(
                        'app_name'=>array('label'=>app::get('trustlogin')->_('名称'),'width'=>200),
                        'app_version'=>array('label'=>app::get('trustlogin')->_('版本'),'width'=>200),
                   );

        $this->schema = array(
                'default_in_list'=>array_keys($this->columns),
                'in_list'=>array_keys($this->columns),
                'idColumn'=>'app_name',
                'columns'=>$this->columns
            );
    }

     /**
     * suffix of model
     * @params null
     * @return string table name
     */
    public function table_name(){
        return 'trustlogin_cfg';
    }

    function get_schema(){
        return $this->schema;
    }

    //返回接口的数量
    function count($filter=''){
        return count($this->api);
    }
    /**
     * 取到服务列表 - 1条或者多条
     * @params string - 特殊的列名
     * @params array - 限制条件
     * @params 偏移量起始值
     * @params 偏移位移值
     * @params 排序条件
     */
    public function getList($cols='*', $filter=array('status' => 'false'), $offset=0, $limit=-1, $orderby=null){
        //todo fitler;

        $arrServicelist = kernel::servicelist('trustlogin_trustlogin.trustlogin_mdl_trustlogin_cfg');

        $start_index = 0;
        foreach($arrServicelist as $class_name => $object)
        {
            $row['app_name'] = $object->name;
            $row['app_staus'] = (($arrPaymnet['status']===true||$arrPaymnet['status']==='true') ? app::get('ectools')->_('开启') : app::get('ectools')->_('关闭'));
            $row['app_version'] = $object->ver;
            $row['app_class'] = $class_name;
            $data[] = $row;
        }

        return $data;
    }

   

}
