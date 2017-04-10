<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['member_signin']=array (
  'columns' =>
  array (
    'member_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'label' => app::get('b2c')->_('会员ID'),
      'default_in_list' => false,
    ),
    'signin_date' =>
    array (
        'type' => 'date',
        'pkey' => true,
        'label' => app::get('b2c')->_('签到日期'),
        'default' => '1970-01-01',
        'required' => true,
        'comment' => app::get('b2c')->_('签到日期'),
    ),

    'point' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('签到获取的积分'),
      'editable' => false,
      'comment' => app::get('b2c')->_('签到获得的积分'),
    ),

    'signin_time' =>
    array (
      'type' => 'int(10)',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('签到时间'),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 44523 $',
  'comment' => app::get('b2c')->_('签到记录表'),
);
