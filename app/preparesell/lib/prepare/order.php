<?php

class preparesell_prepare_order{

    function __construct($app){
        $this->app = $app;
        $this->mdl_prepare_order = app::get('preparesell')->model('prepare_order');
        $this->mdl_preparesell_product = app::get('preparesell')->model('preparesell_goods');
    }

    //预售订单保存
    function sava_prepare_order($data){
    	$prepare_id=$this->mdl_preparesell_product->getRow('*',array('product_id'=>$data['product_id']));
        unset($prepare_id['id']);
    	$data_order=$prepare_id;
        $goodsurl=app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','full'=>1,'act'=>'index','arg0'=>$data['product_id'],'full'=>true,));
        $data_order['canceltime']=strtotime('+'.$prepare_id['timeout'].' '.'hours',time());
    	$data_order['order_id']=$data['order_id'];
        $data_order['member_id']=$data['member_id'];
        $data_order['goal']=$data['email'];
        $data_order['mobile']=$data['mobile'];
        $data_order['goodsurl']=$goodsurl;
        $data_order['goodsname']=$data['name'];
        $result = $this->mdl_prepare_order->save($data_order);

        //以后自动取消将调用b2c应用中公用的订单取消
        $mdl_cancelorder = app::get('b2c')->model('order_cancel_list');
        $data = array(
            'order_id'=>$data_order['order_id'],
            'promotion_type'=>'prepare',
            'canceltime'=>$data_order['canceltime'],
            'reason_desc'=>'预售订单超时未支付，自动取消'
        );
        $mdl_cancelorder->save($data);
    }
    //得到预售订单信息为会员中心服务
    function get_prepare_info($data)
    {
    	foreach ($data as $key => $value) {
    		$order_id[$key]=$value['order_id'];
    	}
    	$prepare_order=$this->mdl_prepare_order->getList('*',array('order_id|in'=>$order_id));
        foreach ($prepare_order as $key => $value) {
            $prepare[$value['order_id']]['order_id']=$value['order_id'];
            $prepare[$value['order_id']]['prepare_id']=$value['prepare_id'];
            $prepare[$value['order_id']]['product_id']=$value['product_id'];
            $prepare[$value['order_id']]['preparesell_price']=$value['preparesell_price'];
            $prepare[$value['order_id']]['promotion_price']=$value['promotion_price'];
            $prepare[$value['order_id']]['promotion_type']=$value['promotion_type'];
            $prepare[$value['order_id']]['preparename']=$value['preparename'];
            $prepare[$value['order_id']]['description']=$value['description'];
            $prepare[$value['order_id']]['status']=$value['status'];
            $prepare[$value['order_id']]['begin_time']=$value['begin_time'];
            $prepare[$value['order_id']]['end_time']=$value['end_time'];
            $prepare[$value['order_id']]['begin_time_final']=$value['begin_time_final'];
            $prepare[$value['order_id']]['end_time_final']=$value['end_time_final'];
            $prepare[$value['order_id']]['nowtime']=time();
            $prepare[$value['order_id']]['remind_way']=$value['remind_way'];
            $prepare[$value['order_id']]['initial_num']=$value['initial_num'];
        }
        return $prepare;

    }

    function get_order_info($order_id)
    {
        $prepare_order=$this->mdl_prepare_order->getRow('*',array('order_id'=>$order_id));
        if(!empty($prepare_order))
        {
            $prepare_order['nowtime']=time();
        }
        return $prepare_order;
    }

    //自动删除过期订单数据
    function check_order($orders)
    {
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        foreach($orders as $key=>$val){
            $oid = $val;
            if($obj_checkorder->check_order_cancel($oid)){
                $sdf['order_id'] = $oid;
                $sdf['op_id'] =1;// $this->user->user_id;
                $sdf['opname'] = "admin";//$this->user->user_data['account']['login_name'];
                $sdf['account_type'] = "shopadmin";//$this->user->account_type;

                $sdf['op_id'] = $this->user->user_id;
                $sdf['opname'] = $this->user->user_data['account']['login_name'];
                $sdf['account_type'] = $this->user->account_type;


                $b2c_order_cancel = kernel::single("b2c_order_cancel");

                if ($b2c_order_cancel->generate($sdf,$null, $message)){
                    if($order_object = kernel::service('b2c_order_rpc_async')){
                        $order_object->modifyActive($sdf['order_id']);
                    }
                    $this->mdl_prepare_order->delete(array('order_id'=>$oid));
                }
            }
        }
    }

    //订单预售的判断
    public function get_promotion_type($filter)
    {
        $promotion_type = $this->mdl_preparesell_product->getRow('promotion_type',array('product_id'=>$filter));
        //foreach ($promotion_type as $key => $value) {
            //$pro_type[] = $value['promotion_type'];
        //}
        return $promotion_type;
    }

}
