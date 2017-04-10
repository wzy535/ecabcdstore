<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db['order_delivery_time']=array (
  'columns' => 
  array (
    'order_id' => 
    array (
      'type' => 'table:orders',
      'required' => true,
      'pkey' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('订单ID'),
    ),
    'delivery_time' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('自动确认收货时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
       'in_list' => true,
      'default_in_list' => true,
      'orderby' => true,
    ),
  ),
  'engine' => 'innodb',
  'comment' => app::get('b2c')->_('自动确认收货'),
);