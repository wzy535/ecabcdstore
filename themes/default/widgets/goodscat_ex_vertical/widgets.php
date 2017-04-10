<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/*基础配置项*/
$setting['author']='litie@shopex.cn';
$setting['version']='v1.0';
$setting['name']='首页主商品分类';
$setting['order']=0;
$setting['stime']='2012-08';
$setting['catalog']='商品相关';
$setting['description'] = '支持三级分类展示；支持不弹出状态下展示二级分类；支持关联促销和品牌信息；尺寸可视化编辑；支持左右方向弹出; 经过千个以上分类性能测试';
$setting['userinfo'] = '';
$setting['usual']    = '1';
$setting['tag']    = 'auto';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

/*首次默认配置项*/
$setting['show_cat_lv2'] 		= 1;
$setting['show_cat_lv3'] 		= 1;
$setting['show_cat_sale']		= 1;
$setting['show_cat_brand'] 		= 1;
$setting['box_flex'] 			= 1;
$setting['box_border_width']		= 1;//px
$setting['container_width'] 		= 200; //px
$setting['cat_lv2_width'] 		= 30;  //%
$setting['box_width']	 		= 700; //px
$setting['box_link_width'] 		= 35;// %
$setting['brand_logo_maxwidth'] 	= 75;//px
$setting['sales_title'] 	= "相关促销";
$setting['brand_title'] 	= "相关品牌";


?>
