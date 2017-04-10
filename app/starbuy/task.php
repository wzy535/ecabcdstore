<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class starbuy_task{

    function post_install($options){
        kernel::single('base_initial', 'starbuy')->init();
    }
}
