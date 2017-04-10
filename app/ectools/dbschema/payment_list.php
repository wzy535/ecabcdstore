<?php

$db['payment_list']=array (
  'columns' => array (
    'list_id' => array (
      'type' => 'int(8)',
      'required' => true,
      'pkey' => true,
      'label' => app::get('ectools')->_('序号'),
      'editable' => false,
      'extra' => 'auto_increment',
      'in_list' => false,
    ),
    'account' => array (
      'type' => 'varchar(50)',
      'label' => app::get('ectools')->_('收款账号'),
      'width' => 110,
      'searchtype' => 'tequal',
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'bank' => array (
      'type' => 'varchar(50)',
      'label' => app::get('ectools')->_('收款银行'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 43384 $',
  'comment' => app::get('ectools')->_('支付列表'),
);
