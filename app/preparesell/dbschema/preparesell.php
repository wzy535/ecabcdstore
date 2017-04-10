<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['preparesell']  = array(
    'columns'=>array(
        'prepare_id'=>array(
            'type'=>'number',
            'pkey'=>true,
            'required' => true,
            'editable' => false,
            'extra' => 'auto_increment',
            'in_list' => false,
            'label'=>app::get('preparesell')->_('规则id'),
            'comment'=>app::get('preparesell')->_('规则id'),
        ),

        'goods_id'=>array(
            'type'=>'table:goods@b2c',
            'label'=>app::get('preparesell')->_('商品id'),
            'comment'=>app::get('preparesell')->_('商品id'),
        ),
        'preparename'=>array(
            'type' => 'varchar(255)',
            'required' => true,
            'default' => '',
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'yes',
            'filterdefault'=>true,
            'is_title' => true,
            'label'=>app::get('preparesell')->_('规则名称'),
            'comment'=>app::get('preparesell')->_('规则名称'),
            'order' => 6,
            'width' => 100,
        ),
        'description'=>array(
            'type'=>'text',
            'required' => false,
            'default' => '',
            'editable' => false,
            'in_list' => true,
            'filterdefault'=>true,
            'label'=>app::get('preparesell')->_('规则描述'),
            'comment'=>app::get('preparesell')->_('规则描述'),
            'order'=>15,
            'width' => 100,
        ),

        'status'=>array(
            'type'=>'bool',
            'default' => 'false',
            'in_list' => true,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault'=>true,
            'default_in_list' => true,
            'label'=>app::get('preparesell')->_('状态'),
            'comment'=>app::get('preparesell')->_('状态'),
            'order'=>10,
            'width' => 50,
        ),

        'begin_time'=>array(
            'type'=>'time',
            'default'=> 0,
            'in_list'=>true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'order' => 7,
            'label'=>app::get('preparesell')->_('预售订金开始时间'),
            'comment'=>app::get('preparesell')->_('预售订金开始时间'),
        ),
        'end_time'=>array(
            'type'=>'time',
            'default'=> 0,
            'in_list'=>true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'order' => 8,
            'label'=>app::get('preparesell')->_('预售订金结束时间'),
            'comment'=>app::get('preparesell')->_('预售订金结束时间'),
        ),
        'timeout'=>array(
            'type'=>'int(10) unsigned',
            'default' =>0,
            'label' => app::get('preparesell')->_('超时时间'),
            'comment' => app::get('preparesell')->_('超时时间'),
        ),

        'begin_time_final'=>array(
            'type'=>'time',
            'default'=> 0,
            'in_list'=>true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'order' => 9,
            'label'=>app::get('preparesell')->_('尾款开始时间'),
            'comment'=>app::get('preparesell')->_('尾款开始时间'),
        ),
        'end_time_final'=>array(
            'type'=>'time',
            'default'=> 0,
            'in_list'=>true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'order' => 10,
            'label'=>app::get('preparesell')->_('尾款结束时间'),
            'comment'=>app::get('preparesell')->_('尾款结束时间'),
        ),
        'remind_way'=>array(
            'type'=>'serialize',
            'default_in_list'=>false,
            'in_list'=>false,
            'label'=>app::get('preparesell')->_('提醒方式'),
            'comment'=>app::get('preparesell')->_('提醒方式'),
        ),
        'remind_time'=>array(
            'type'=>'int(10) unsigned',
            'default' => 0,
            'default_in_list'=>true,
            'in_list'=>true,
            'label'=>app::get('preparesell')->_('提前提醒时间'),
            'comment'=>app::get('preparesell')->_('提前提醒时间'),
            'order'=>11 ,
            'width' => 100,
        ),
        'remind_time_send'=>array(
            'type'=>'time',
            'default' => 0,
            'default_in_list'=>true,
            'in_list'=>true,
            'label'=>app::get('preparesell')->_('提前提醒时间'),
            'comment'=>app::get('preparesell')->_('提前提醒时间'),
            'order'=>16 ,
            'width' => 100,
        ),
        'promotion_type'=>array(
            'type' => 'varchar(255)',
            'required' => true,
            'default' => '',
            'editable' => true,
            'in_list' => true,
            'default_in_list' => false,
            'is_title' => true,
            'label'=>app::get('preparesell')->_('规则类型'),
            'comment'=>app::get('preparesell')->_('规则类型'),
            'order' => 12,
            'width' => 100,
        ),

      /*  'prepares_rule'=>array(
            'type'=>'float',
            'in_list'=>false,
            'default_in_list' => true,
            'order' => 5,
            'label'=>app::get('preparesell')->_('预售规则'),
            'comment'=>app::get('preparesell')->_('预售规则'),
        ),*/


    ),
);
