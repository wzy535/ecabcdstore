<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_finder_receivedcols{
    var $column_received = '自动确认收货时间';
    var $column_received_order = COLUMN_IN_TAIL;
    function column_received($row){
        $order_id = $row[$row['idColumn']];
        $received = app::get('b2c')->model('order_delivery_time');
        $filter = array('order_id'=>$order_id);
        $received_time = $received->getList('delivery_time',$filter);
        if($received_time){
            foreach ($received_time as $row) {
                $return .= date('Y-m-d H:i:s',$row['delivery_time']);
            }
        }
        return $return;
    }

}
