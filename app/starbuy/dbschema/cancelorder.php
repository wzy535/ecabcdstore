<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['cancelorder'] = array(
    'columns'=>array(
        'order_id'=>array(
            'type'=>'bigint unsigned',
        ),
        'canceltime'=>array(
            'type'=>'time',
        ),
    ),
);
