<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['special_goods']=array(
    'columns'=>array(
        'id'=>array(
            'type'=>'number',
            'required' => true,
            'pkey'=>true,
            'extra'=>'auto_increment',
        ),
        'special_id'=>array(
            'type'=>'table:special',
            'label'=>app::get('starbuy')->_('规则id'),
            'comment'=>app::get('starbuy')->_('规则id'),
        ),
        'product_id'=>array(
            'type'=>'table:products@b2c',
            'label'=>app::get('starbuy')->_('货品id'),
            'comment'=>app::get('starbuy')->_('货品id'),
        ),
        'type_id'=>array(
            'type'=>'table:promotions_type',
            'label'=>app::get('starbuy')->_('促销类型id'),
            'comment'=>app::get('starbuy')->_('促销类型id'),
        ),
        'promotion_price'=>array(
            'type'=>'money',
            'label'=>app::get('starbuy')->_('促销价格'),
            'comment'=>app::get('starbuy')->_('促销价格'),
        ),
        'release_time'=>array(
            'type'=>'time',
            'default'=> 0,
            'editable' => true,
            'filterdefault'=>true,
            'label'=>app::get('starbuy')->_('发布时间'),
            'comment'=>app::get('starbuy')->_('发布时间'),
        ),
        'begin_time'=>array(
            'type'=>'time',
            'default'=> 0,
            'editable' => true,
            'filterdefault'=>true,
            'label'=>app::get('starbuy')->_('开始时间'),
            'comment'=>app::get('starbuy')->_('开始时间'),
        ),
        'end_time'=>array(
            'type'=>'time',
            'default'=> 0,
            'editable' => true,
            'filterdefault'=>true,
            'label'=>app::get('starbuy')->_('结束时间'),
            'comment'=>app::get('starbuy')->_('结束时间'),
        ),
        'limit'=>array(
            'type'=>'number',
            'label'=>app::get('starbuy')->_('限购数量'),
            'comment'=>app::get('starbuy')->_('限购数量'),
        ),

        'remind_way'=>array(
            'type'=>'serialize',
            'label'=>app::get('starbuy')->_('提醒方式'),
            'comment'=>app::get('starbuy')->_('提醒方式'),
        ),
        'remind_time'=>array(
            'type'=>'number',
            'label'=>app::get('starbuy')->_('提前提醒时间'),
            'comment'=>app::get('starbuy')->_('提前提醒时间'),
        ),
        'timeout'=>array(
            'type'=>'number',
            'label'=> app::get('b2c')->_('超时时间'),
            'comment' => app::get('b2c')->_('超时时间'),
        ),
        'cdown'=>array(
            'type'=>'bool',
            'label'=>app::get('starbuy')->_('是否显示倒计时'),
            'comment'=>app::get('starbuy')->_('是显示否倒计时'),
        ),
        'initial_num'=>array(
            'type'=>'number',
            'comment' => app::get('b2c')->_('初始销售量'),
        ),
        'status'=>array(
            'type'=>'bool',
            'default' => 'false',
            'label'=>app::get('starbuy')->_('状态'),
            'comment'=>app::get('starbuy')->_('状态'),
        ),
        'description'=>array(
            'type'=>'text',
            'required' => false,
            'default' => '',
            'editable' => false,
            'label'=>app::get('starbuy')->_('规则描述'),
            'comment'=>app::get('starbuy')->_('规则描述'),
        ),
    ),
);
