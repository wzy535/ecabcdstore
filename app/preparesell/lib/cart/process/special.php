<?php
class preparesell_cart_process_special implements b2c_interface_cart_process{

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
        return 96;
    }
    //设置价格不算会员价
    function process($aData,&$aResult = array(),$aConfig = array()){
        $this->specialPro = kernel::single('preparesell_prepare_prepare');
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

                        //$products[$k]['store'] = (intval($specialproduct['limit']) > 0&&$v['store']>$specialproduct['limit'])?$specialproduct['limit']:$v['store'];
                        $allgoods[$key]['subtotal'] = $promotion_price*$allgoods[$key]['quantity'];
                        $allgoods[$key]['subtotal_price'] = $promotion_price*$allgoods[$key]['quantity'];
                        $allgoods[$key]['subtotal_prefilter_after'] = $promotion_price*$allgoods[$key]['quantity'];
                        $allgoods[$key]['prepare_type'] = 'prepare';
                    }

                }
                $allgoods[$key]['obj_items']['products'] = $products;
            }
            //echo '<pre>';print_r($allgoods);exit();
            $aResult['object']['goods'] = $allgoods;

        }
    }
}
