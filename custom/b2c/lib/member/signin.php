<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_member_signin
{
    /**
     * 构造方法
     * @param object app
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 签到
     * @param array data
     * @return boolean true or false
     */
    public function sign($data)
    {
        if(!is_array($data)) return false;

        $obj_signin = $this->app->model('member_signin' );
        $result = $obj_signin->insert($data);

        if($result){
            $this->change_signin($data);
            $this->change_point($data);

            return true;
        }
        return false;
    }

    /**
     * 同步签到状态
     * @param array data
     * @return boolean true or false
     */
    public function change_signin($data)
    {
        if(!is_array($data)) return false;

        $nodes_obj = $this->app->model('shop');
        $nodes = $nodes_obj->count( array('node_type'=>'ecos.taocrm','status'=>'bind'));

        if($nodes > 0){
            $params = array();
            $params['member_id'] = $data['member_id'];
            $params['expiretime'] = $data['signin_time'];

            $rpc_obj = kernel::service('b2c_member_signin_rpc_sync');
            $rpc_obj->changeActive($params);
        }
        return true;
    }

    /**
     * 更新积分
     * @param array data
     * @return boolean true or false
     */
    public function change_point($data)
    {
        if(!is_array($data)) return false;

        $mem_point = $this->app->model('member_point');
        $member_id = $data['member_id'];
        $point = $data['point'];
        $msg = '签到赠送积分';
        $mem_point->change_point($member_id,$point,$msg,'signin_score',2,0,$member_id,'charge');
        return true;
    }
}
