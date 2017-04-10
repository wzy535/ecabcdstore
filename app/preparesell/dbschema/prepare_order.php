<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['prepare_order'] = array(
    'columns'=>array(
    	'id' =>
	    array (
	      'type' => 'mediumint(8)',
	      'required' => true,
	      'pkey' => true,
	      'extra'=>'auto_increment',
	    ),
       'order_id' =>
	    array (
	      'type' => 'table:orders@b2c',
	      'label' => __('订单号'),
	      'width' => 110,
	    ),
        'member_id' =>
        array (
          'type' => 'table:members@b2c',
          'label' => app::get('preparesell')->_('会员用户名'),
          'width' => 75,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
        ),
        'prepare_id' =>
	    array (
	      'type' => 'mediumint(8)',
	      'default' => '0',
	      'label' => __('预售ID'),
	      'width' => 75,
	    ),
	    'product_id'=>array(
            'type'=>'table:products@b2c',
            'label'=>app::get('preparesell')->_('货品id'),
            'comment'=>app::get('preparesell')->_('货品id'),
        ),
        'goodsname'=>array(
            'type'=>'varchar(50)',
            'label' => __('商品名称'),
        ),

        'goal'=>array(
            'type'=>'varchar(50)',
            'label' => __('会员email'),
        ),
        'mobile'=>array(
            'type'=>'varchar(50)',
            'label' => __('会员mobile'),
        ),
        'goodsurl'=>array(
            'type'=>'varchar(100)',
            'label' => __('货品链接'),
        ),


        'preparesell_price'=>array(
            'type'=>'money',
            'in_list'=>true,
            'default_in_list' => true,
            'order' => 5,
            'label'=>app::get('preparesell')->_('预售价格'),
            'comment'=>app::get('preparesell')->_('预售价格'),
        ),

        'promotion_price'=>array(
            'type'=>'money',
            'in_list'=>true,
            'default_in_list' => true,
            'order' => 5,
            'label'=>app::get('preparesell')->_('销售价格'),
            'comment'=>app::get('preparesell')->_('销售价格'),
        ),

        'preparename'=>array(
            'type' => 'varchar(255)',
            'required' => true,
            'default' => '',
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'is_title' => true,
            'label'=>app::get('preparesell')->_('规则名称'),
            'comment'=>app::get('preparesell')->_('规则名称'),
            'order' => 6,
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
        'is_send'=>array(
            'type'=>'int(10) unsigned',
            'default' => 0,
            'default_in_list'=>true,
            'in_list'=>true,
            'label'=>app::get('preparesell')->_('短信是否以发过'),
            'comment'=>app::get('preparesell')->_('短信是否以发过'),
            'order'=>12 ,
            'width' => 100,
        ),
        'remind_time_send'=>array(
            'type'=>'time',
            'default' => 0,
            'default_in_list'=>true,
            'in_list'=>true,
            'label'=>app::get('preparesell')->_('提前提醒时间'),
            'comment'=>app::get('preparesell')->_('提前提醒时间'),
            'order'=>13 ,
            'width' => 100,
        ),
        'canceltime'=>array(
            'type'=>'time',
            'default' =>0,
            'label' => app::get('preparesell')->_('超时时间'),
            'comment' => app::get('preparesell')->_('超时时间'),
        ),

       /* 'prepares_rule'=>array(
            'type'=>'float',
            'in_list'=>false,
            'default_in_list' => true,
            'order' => 5,
            'label'=>app::get('preparesell')->_('预售规则'),
            'comment'=>app::get('preparesell')->_('预售规则'),
        ),*/
        'initial_num'=>array(
            'type'=>'int(10) unsigned',
            'default'=>0,
            'label' => app::get('b2c')->_('初始销售量'),
            'comment' => app::get('b2c')->_('初始销售量'),
        ),
    ),
);
