<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * b2c member interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_apiv_apis_response_sysinfo
{

    //获取ecstore的版本信息
    public function version($params, &$service){
        $data = array();
        $xml = file_get_contents(ROOT_DIR.'/config/deploy.xml');
        $deploy = kernel::single('base_xml')->xml2array($xml,'base_deploy');

        $data['ecstore_version'] = $deploy['product_version'];
        $data['system_version'] = $deploy['business_version'];
        return $data;
    }

}
