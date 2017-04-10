<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
* 节点导航类,实现site_interface_menu接口
*
*/
class starbuy_menu_groupbuy implements site_interface_menu
{
    /**
    * 添加菜单,选择节点首页后的生成的HTML表单
    * @access public
    * @param array $config 配置信息
    * @return array '文章ID'=>array('select'=>...)
    */
    public function inputs($config=array()){
        $selectmaps = kernel::single('starbuy_special_products')->_get_protype();
        if(is_array($selectmaps)){
            foreach($selectmaps as $key => $select){
                $options[$select['type_id']] = $select['name'];
            }
        }

        $inputs = array(
            app::get('content')->_("促销类型") => array('type'=>'select', 'title'=>'type_id', 'required'=>true, 'name'=>'type_id', 'value'=>$config['type_id'], 'options'=>$options),
        );
        return $inputs;
    }

    /**
    * 设置params 和config的值
    * @param array $post post数组
    */
    public function handle($post){

        $this->params['type_id'] = $post['type_id'];

        $this->config = $this->params;
    }

    /**
    * 获取params的值
    * @return array
    */
    public function get_params(){
        return $this->params;
    }

    /**
    * 获取config的值
    * @return array
    */
    public function get_config(){
        return $this->config;
    }
}//End Class
