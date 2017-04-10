<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['promotions_type']=array(
    'columns'=>array(
        'type_id'=>array(
            'type'=>'number',
            'pkey'=>true,
            'required' => true,
            'editable' => false,
            'extra' => 'auto_increment',
            'in_list' => false,
            'label'=>app::get('starbuy')->_('类型id'),
            'comment'=>app::get('starbuy')->_('类型id'),
        ),
        'name'=>array(
            'type' => 'varchar(255)',
            'required' => true,
            'default' => '',
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'is_title' => true,
            'label'=>app::get('starbuy')->_('类型名称'),
            'comment'=>app::get('starbuy')->_('类型名称'),
        ),
        'bydefault'=>array(
            'type'=>'bool',
            'editable' => true,
            'default'=>'false',
            'default_in_list' => true,
            'in_list' => true,
            'label'=>app::get('starbuy')->_('是否系统默认'),
            'comment'=>app::get('starbuy')->_('是否系统默认'),
        ),
    ),
);
