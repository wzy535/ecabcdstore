<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_api_callback_shoprelation implements b2c_api_callback_interface_app
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 回调接口
     * @param array return array
     */
    public function callback($result)
    {
        if (isset($_POST) && $_POST)
        {
            $status = $_POST['status'];
            if($status == 'bind'){
                $node_id = $_POST['node_id'];
                $node_type=$_POST['node_type'];
            }else{
                $node_id = "";
                $node_type = "";
            }

            $obj_shop = $this->app->model('shop');
            if ($_POST['node_id'])
            {
                if($_POST['node_id'] && !intval($result['shop_id'])){
                    $arr_shop = $obj_shop->getList('*',array('node_id'=>$_POST['node_id']));
                    $arr_shop = $arr_shop[0];
                    $arr_shop['node_id'] = $node_id;
                    $arr_shop['status'] = $status;
                    $arr_shop['node_type'] = $node_type;
                }elseif($_POST['node_id']){
                    $arr_shop = $obj_shop->dump(array('shop_id' => $result['shop_id']));
                    $arr_shop['node_id'] = $node_id;
                    $arr_shop['node_type'] = $node_type;
                    $arr_shop['status'] = $status;
                    $arr_shop['node_apiv'] = $_POST['api_v'];
                }
                $obj_shop->save($arr_shop);

            }
        }

        if($_POST['node_type'] == 'ecos.taocrm' && $_POST['status'] == 'unbind'){
            $nodes = $obj_shop->count( array('node_type'=>'ecos.taocrm','status'=>'bind'));
            if($nodes == 0 ){
                $member_point = $this->app->model('member_point');
                $member_point->update(array('status'=>'true')); //解除crm绑定积分清零
                $member_obj = $this->app->model('members');

                $member_data = array('point'=>0,'freezed_point'=>0,'obtained_point'=>0);
                if($this->app->getConf('site.level_switch') == 0){
                    $member_data['member_lv_id'] = 1;
                }

                $members = $member_obj->getList('member_id');
                foreach($members as $key => $value){
                    $filter = array('member_id'=>$value['member_id']);
                    $member_obj->update($member_data,$filter);
                }
            }
        }
    }
}
