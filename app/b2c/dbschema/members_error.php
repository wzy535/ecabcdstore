<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['members_error']=array (
  'columns' =>
  array (
    'member_id' =>
    array (
      'type' => 'table:members',
      'label' => app::get('b2c')->_('会员用户名'),
      'comment' => app::get('b2c')->_('会员用户名'),
    ),
    'etime' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'label' => app::get('b2c')->_('错误时间'),
      'comment' => app::get('b2c')->_('错误时间'),
    ),
    'error_num' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => app::get('b2c')->_('错误计数'),
      'comment' => app::get('b2c')->_('错误计数'),
    ),
    'type' =>
    array (
      'type' => array(
            'check' =>app::get('b2c')->_('身份验证'),
            'possword' => app::get('b2c')->_('支付密码')
       ),
      'required' => true,
      'label' => app::get('b2c')->_('错误类型'),
      'comment' => app::get('b2c')->_('错误类型'),
    ),
  ),
  'index' =>
  array (
    'ind_createtime' =>
    array (
      'columns' =>
      array (
        0 => 'member_id',
      ),
    ),
  ),
  'version' => '$Rev: 42376 $',
  'comment' => app::get('b2c')->_('会员支付错误表'),
);
