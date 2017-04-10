<?php
$db['register_record']=array (
    'columns' =>
    array (
        'id' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'comment' => app::get('referrals')->_('ID'),
        ),
        'reference_id' =>
        array (
            'type' => 'number',
            'default' => '0',
            'required' => true,
            'label' => app::get('referrals')->_('推荐人id'),
            'width'=>80,
            'default_in_list'=>true,
            'in_list'=>true,
            'comment' => app::get('referrals')->_('推荐人id'),
        ),
        'register_id' =>
        array (
            'type' => 'number',
            'default' => '0',
            'required' => true,
            'label' => app::get('referrals')->_('注册人id'),
            'width'=>80,
            'default_in_list'=>true,
            'in_list'=>true,
            'comment' => app::get('referrals')->_('注册人id'),
        ),
        'regtime' =>
        array (
          'label' => app::get('referrals')->_('注册时间'),
          'width' => 75,
          'type' => 'time',
          'editable' => false,
          'filtertype' => 'time',
          'filterdefault' => true,
          'in_list' => true,
          'default_in_list' => true,
          'comment' => app::get('referrals')->_('注册时间'),
        ),
        'register_point' =>
        array (
          'type' => 'number',
          'default' => '0',
          'required' => true,
          'editable' => false,
          'comment' => app::get('referrals')->_('推荐人获得的积分'),
        ),
        'referrals_code' => 
        array (
            'type' => 'varchar(32)',
            'default' => 0,
            'required' => false,
           'comment' => app::get('b2c')->_('使用的二维码'),
        ),


    ),

);
