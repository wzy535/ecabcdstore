<?php
$db['order_cancel_list'] = array(
    'columns' => array(
        'order_id' => array(
            'type' => 'table:orders',
            'pkey' => true,
            'default' => 0,
            'editable' => false,
            'comment' => app::get('b2c')->_('订单ID'),
        ),
        'promotion_type' => array (
            'type' =>
            array (
                'normal' => app::get('b2c')->_('普通订单'),
                'prepare' => app::get('b2c')->_('预售订单'),
            ),
            'default' => 'normal',
            'label' => app::get('b2c')->_('销售类型'),
        ),
        'canceltime'=>array(
            'type'=>'time',
            'comment' => app::get('b2c')->_('取消时间'),
        ),
        'reason_desc' => array(
            'type' => 'varchar(150)',
            'label' => app::get('b2c')->_('其他原因'),
            'width' => 75,
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'index' =>
    array(
        'ind_canceltime' =>
        array(
            'columns' =>
            array(
                0 => 'canceltime',
            ),
        ),
    ),
);
