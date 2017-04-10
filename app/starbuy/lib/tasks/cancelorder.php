<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class starbuy_tasks_cancelorder extends base_task_abstract implements base_interface_task{


    function exec($params=null){
        $cancel_mdl = app::get('starbuy')->model('cancelorder');
        $cancel_obj = kernel::single('starbuy_special_order');
        $orders = $cancel_mdl->getList("order_id",array('canceltime|sthan'=>time()));
        $cancel_obj->check_order($orders);
    }
}


