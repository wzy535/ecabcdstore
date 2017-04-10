<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['trustinfo']  = array(
    'columns'=>array(
        'trust_id'=>array(
            'type'=>'number',
            'pkey'=>true,
            'required' => true,
            'editable' => false,
            'extra' => 'auto_increment',
            'in_list' => false,
            'label'=>app::get('trustlogin')->_('信任id'),
            'comment'=>app::get('trustlogin')->_('信任id'),
        ),

        'member_id'=>array(
            'type'=>'table:members@b2c',
            'in_list' => false,
            'label'=>app::get('trustlogin')->_('会员id'),
            'comment'=>app::get('trustlogin')->_('会员id'),
        ),

        'openid'=>array(
            'type' => 'varchar(255)',
            'required' => true,
            'default' => '',
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'yes',
            'filterdefault'=>true,
            'is_title' => true,
            'label'=>app::get('trustlogin')->_('用户openid'),
            'comment'=>app::get('trustlogin')->_('用户openid'),
            'order' => 6,
            'width' => 100,
        ),
        'realname'=>array(
            'type'=>'varchar(255)',
            'default' => '',
            'editable' => false,
            'in_list' => true,
            'filterdefault'=>true,
            'label'=>app::get('trustlogin')->_('用户真实名称'),
            'comment'=>app::get('trustlogin')->_('用户真实名称'),
            'order'=>7,
            'width' => 100,
        ),

        'nickname'=>array(
            'type'=>'varchar(255)',
            'in_list' => true,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault'=>true,
            'default_in_list' => true,
            'label'=>app::get('trustlogin')->_('用户昵称'),
            'comment'=>app::get('trustlogin')->_('用户昵称'),
            'order'=>8,
            'width' => 50,
        ),

        'avatar'=>array(
            'type'=>'varchar(255)',
            'in_list' => true,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault'=>true,
            'default_in_list' => true,
            'label'=>app::get('trustlogin')->_('头像'),
            'comment'=>app::get('trustlogin')->_('头像'),
            'order'=>9,
            'width' => 50,
        ),

        'url'=>array(
            'type'=>'varchar(255)',
            'in_list' => true,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault'=>true,
            'default_in_list' => true,
            'label'=>app::get('trustlogin')->_('链接'),
            'comment'=>app::get('trustlogin')->_('链接'),
            'order'=>10,
            'width' => 50,
        ),

        /*'birthday'=>array(
            'type'=>'varchar(255)',
            'in_list' => true,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault'=>true,
            'default_in_list' => true,
            'label'=>app::get('trustlogin')->_('生日'),
            'comment'=>app::get('trustlogin')->_('生日'),
            'order'=>11,
            'width' => 50,
        ),*/
        'gender'=>array(
            'type'=>'varchar(255)',
            'in_list' => true,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault'=>true,
            'default_in_list' => true,
            'label'=>app::get('trustlogin')->_('性别'),
            'comment'=>app::get('trustlogin')->_('性别'),
            'order'=>12,
            'width' => 50,
        ),
        'address'=>array(
            'type'=>'varchar(255)',
            'in_list' => true,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault'=>true,
            'default_in_list' => true,
            'label'=>app::get('trustlogin')->_('用户地址'),
            'comment'=>app::get('trustlogin')->_('用户地址'),
            'order'=>13,
            'width' => 50,
        ),
        'province'=>array(
            'type'=>'varchar(255)',
            'in_list' => true,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault'=>true,
            'default_in_list' => true,
            'label'=>app::get('trustlogin')->_('所在省级'),
            'comment'=>app::get('trustlogin')->_('所在省级'),
            'order'=>14,
            'width' => 50,
        ),

        'city'=>array(
            'type'=>'varchar(255)',
            'in_list' => true,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault'=>true,
            'default_in_list' => true,
            'label'=>app::get('trustlogin')->_('所在城市'),
            'comment'=>app::get('trustlogin')->_('所在城市'),
            'order'=>15,
            'width' => 50,
        ),

    ),
);
