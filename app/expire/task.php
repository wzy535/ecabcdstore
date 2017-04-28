<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class expire_task
{
    function post_install()
    {
        logger::info('Initial expire');
        kernel::single('base_initial', 'expire')->init();
    }//End Function
}//End Class
