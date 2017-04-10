<?php
class starbuy_cart_process_special implements b2c_interface_cart_process{

    private $app;

    /**
     * 构造函数
     *
     * @param $object $app  // service 调用必须的
     */
    public function __construct() {
        $this->app = app::get('b2c');
    }

    function get_order(){
        return 97;
    }

    function process($aData,&$aResult = array(),$aConfig = array()){
        $this->specialPro = kernel::single('starbuy_special_products');
        if($allgoods = $aResult['object']['goods']){
            foreach($allgoods as $key=>$value){
                $products = $value['obj_items']['products'];
                foreach($products as $k=>$v){
                    $specialproduct = $this->specialPro->getSpecialProduct($v['product_id']);
                    if($specialproduct){
                        $promotion_price = $specialproduct['promotion_price'];
                        $products[$k]['price']=array(
                            'price'=>$promotion_price,
                            'member_lv_price'=>$promotion_price,
                            'buy_price'=>$promotion_price,
                        );
                        $products[$k]['json_price']=array(
                            'price'=>$promotion_price,
                            'member_lv_price'=>$promotion_price,
                            'buy_price'=>$promotion_price,
                        );
                        $products[$k]['subtotal'] = $promotion_price*$allgoods[$key]['quantity'];

                        $products[$k]['store'] = (intval($specialproduct['limit']) > 0&&$v['store']>$specialproduct['limit'])?$specialproduct['limit']:$v['store'];
                        $allgoods[$key]['subtotal'] = $promotion_price*$allgoods[$key]['quantity'];
                        $allgoods[$key]['subtotal_price'] = $promotion_price*$allgoods[$key]['quantity'];
                        $allgoods[$key]['subtotal_prefilter_after'] = $promotion_price*$allgoods[$key]['quantity'];
                        $allgoods[$key]['special_type'] = $specialproduct['type_id'];
                        $allgoods[$key]['store']['real'] = (intval($specialproduct['limit']) > 0&&$v['store']>$specialproduct['limit'])? $specialproduct['limit']:$v['store'];
                        //$allgoods[$key]['store']['store'] = (intval($specialproduct['limit']) > 0&&$v['store']>$specialproduct['limit'])? $specialproduct['limit']:$v['store'];
                    }

                }
                $allgoods[$key]['obj_items']['products'] = $products;
            }
            $aResult['object']['goods'] = $allgoods;
        }
    }
}
