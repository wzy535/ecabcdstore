<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class logisticstrack_ctl_wap_tracker extends wap_controller{

    public function __construct($app) {
        parent::__construct($app);
    }
    
    public function pull($deliveryid) {
        if ( !$deliveryid ) {
            $this->pagedata['logi_error'] = app::get('logisticstrack')->_('ecstore缺少发货单号');
            return false;
        }
        $deliveryMdl = app::get('b2c')->model('delivery');
        $delivery = $deliveryMdl->getList('logi_id,logi_name,logi_no',array('delivery_id'=>$deliveryid,'disabled'=>'false'),0,1);
        $this->pagedata['delivery'] = $delivery;
        header("cache-control: no-store, no-cache, must-revalidate");
        header('Content-Type: text/html; charset=UTF-8');
        if ( logisticstrack_puller::pull_logi($deliveryid, $data) ) {
            $this->pagedata['logi'] = $data['data'];
        } else {
            $this->pagedata['logi_error'] = $data['msg'];
        }
        $this->display('wap/logistic_detail.html');
    }
}
