<?php
/**
 * 如果调试API应用级参数较少，则可以直接在此文件中定义好应用级参数
 *
 * 参数变量名为：API实现函数方法名称
 */


/**
 *b2c.coupon.get_coupon_list  获取优惠劵列表
 *应用级参数
 */
$get_coupon_list = array();

/**
 * 获取优惠券，只有B类优惠券需要调用
 */
/*$get_coupon_number = array(
    'num' => 2,
    'cpns_id' => '1',
);*/

//一群货品bn
$bn=array(
        'bn'=> array(
            'P4CB273F60F45F',
            'P4CB273F60F480',
            'P4CB273F60F498',
            'P4CB2B50698995',
            'P4C85AAD5DD4ED',
            'P4C85AAD5DD503',
            'P4C85AAD5DD517',
            'P4C85AAD5DD52C',
            'P4C85AAD5DD543',
            'P4C85AAD5DD5A6',
            'P4C85AAD5DD5C3',
            'P4C85AAD5DD5D9',
            'P4C85AC15B71DC',
            )
        );

//一群有会员价的货品bn
$bn2=array(
'bn'=>array(
    'P4CB2BBD0A03A7',
    'P4CB2BB85A40AD',
    'aaaaa'
)
);

$get_coupon_number5 = array(
    'goods_id' => 94,
    'page_no' => 1,
    'page_size' => 10,
);
$get_coupon_number = array(
    'type_id' => 2,
);
/**
 * 应用级参数定义
 */
$add_comments = array(
    'goods_id' => 79,
    'member_id' => 11,
    'comment'=>'asifhaowfy9affhw8efywiojfsdfjwie',
    'order_id'=>'140710155137157',
    //'product_id' =>'233',
    'hidden_name'=>'',
    'author'=>'',
    'goods_point'=>array(
        array(
            'comment_id' => 47,
            'display' => false,
            'goods_id' => 79,
            'goods_point' => 0,
            'member_id' => 11,
            'type_id' => 1,
            ),
        array(
            'comment_id' => 47,
            'display' => false,
            'goods_id' => 79,
            'goods_point' => 0,
            'member_id' => 11,
            'type_id' => 2,
            ),
        array(
            'comment_id' => 47,
            'display' => false,
            'goods_id' => 79,
            'goods_point' => 0,
            'member_id' => 11,
            'type_id' => 3,
            ),
    ),

);
$get_coupon_number1 = array(
    'goods_id' => 94,
    'member_id'=>1,
);
/**
 * 应用级参数定义
 */
$get_coupon_number2 = array(
    'cat_id' => 2,

);
/*$get_coupon_number = array(
    'parent_id' => 1,
   // 'member_id'=>10,
);*/
//$api_params = $add_comments;
$list = array(
    'member_id' => 10,
    'accesstoken'=>'68e2a727d53d9f0f15b2df5066a080d3',
);
$brand_id = array('brand_id'=>json_encode(array(1,2,3,4 )));
$goods_id = array('product_id'=>81);
$goods_ids = array('cat_id'=>1);
$user = array('uname'=>'demo','password'=>'s35a6c0509511f5cba78637b600f3971');
$cart = array('member_id'=>'10',
              'accesstoken'=>'8f5c3a311ac606386c0df816abcb2dd5',
              'area_id'=> '',
              'shipping_id'=>'',
              'is_protect'=>'',
              'payment'=>'',
              'cur'=>'',
              'is_tax'=>'',
              'tax_type'=>'',
              'dis_point'=>'',
          );
$order = array(
    'member_id'=>10,
    'accesstoken'=>'3d9765eb71ba6caca69cc315ce412693',
    'area'=>'mainland:上海/上海市/徐汇区:25',
    'addr'=>'桂林路396号2号楼5F',
    'zip'=>'230233',
    'name'=>'王语嫣',
    'mobile'=>'13333333333',
    'shipping_id'=>'1',
    'tel'=>null,
    'day'=>'指定日期',
    'special'=>'2014-12-31',
    'time'=>'任意时间段',
    'payment_currency'=>'CNY',
    'payment_pay_app_id'=>'wxpay',
    'payment_is_tax'=>'true',
    'payment_tax_type'=>'company',
    'payment_tax_company'=>'酷美（上海）信息技术有限公司',
    'payment_tax_content'=>'办公用品',

    'coupon'=>null,
    'memo'=>null,
);

$get_member_coupon = array('member_id'=>'1');

$update_point = array('member_id'=>'1','point'=>'10402');

$version = array();

$update_member_signin = array('member_id'=>51,'signin_time'=>time());

$referrals = array('status'=>1,'points'=>10);

$api_params = $referrals;
