<?php
/**
* ShopEx licence
*
* @copyright Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
* @license http://ecos.shopex.cn/ ShopEx License
*/

class crm_init_members extends PHPUnit_Framework_TestCase
{
    /*
     * author guzhengxiao
     */
    public function setUp()
    {
        // $this->model = app::get('b2c')->model('goods_type');
    }


    public function testInsert(){
        $member_id = '27';
        $member_rpc_object = kernel::single("b2c_apiv_exchanges_request_member");
        $member_rpc_object->createActive($member_id);
    }
}
