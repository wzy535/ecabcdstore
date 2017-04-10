<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

interface trustlogin_interface_trust
{

    public function get_appkey();

    public function get_appSecret();

    public function get_setting();

    public function set_setting($data);

    public function get_logo();

    public function callback($data);
}

?>
