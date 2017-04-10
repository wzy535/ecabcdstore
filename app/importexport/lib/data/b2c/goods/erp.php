<?php

class importexport_data_b2c_goods_erp {

    /**
     * 导出商品用type_id进行分组
     */
    public function set_group($col){
        return 'type_id';
    }

    //导出商品公共的固定字段
    private function get_comm_title(){

        $title = array(
            'bn'            => app::get('b2c')->_('bn:商品编号'),
            'barcode'       => app::get('b2c')->_('barcode:条形码'),
            'ibn'           => app::get('b2c')->_('ibn:货号'),
            'brand_name'    => app::get('b2c')->_('col:品牌'),
            'cost'          => app::get('b2c')->_('col:成本价'),
            'price'         => app::get('b2c')->_('col:销售价'),
            'name'          => app::get('b2c')->_('col:商品名称'),
            'spec'          => app::get('b2c')->_('col:规格'),
            'pic_name'      => app::get('b2c')->_('col:图片地址'),//图片文件名称
            'brief'         => app::get('b2c')->_('col:商品简介'),
            'weight'        => app::get('b2c')->_('col:重量'),
            'unit'          => app::get('b2c')->_('col:单位'),
            'display_status'=> app::get('b2c')->_('col:可视状态'),
        );

        return $title;
    }

    public function get_title( $title = array() ){
        $comm_title = $this->get_comm_title();
        $goodsTypeModel = app::get('b2c')->model('goods_type');
        $goodsTypeData = $goodsTypeModel->getList('type_id,name,params', array('type_id') );

        $tmp_comm_title = array();
        $title = array();
        foreach( (array)$goodsTypeData as $row ){
            $title_header['type_name'] = '*:'.$row['name'];
            $tmp_comm_title = array_merge($title_header, $comm_title);
            $title[$row['type_id']] = $tmp_comm_title;
        }

        return $this->title = $title;
    }

    public function get_content_row($row){

        if(!$this->title){
            $this->title = $this->get_title();
        }

        $productsData = app::get('b2c')->model('products')->getList('product_id,bn,barcode,price,cost,marketable,spec_info,spec_desc,weight',array('goods_id'=>$row['goods_id']));

        $goodsData = $this->_get_goods_data($row);
        $data = array();
        //单规格
        if( count($productsData) == 1 && !$productsData[0]['spec_desc'] )
        {
            $goodsData['ibn']        = $productsData[0]['bn'];
            $goodsData['price']      = $productsData[0]['price'];
            $goodsData['cost']       = $productsData[0]['cost'];
            $goodsData['weight']     = $productsData[0]['weight'];
            $goodsData['barcode']    = $productsData[0]['barcode'];
            foreach( (array)$this->title[$row['type_id']] as $col=>$col_name){
                $data[0][$col] = $goodsData[$col] ? $goodsData[$col] : '';
            }
            $data[0]['display_status'] = '显示';
        }
        //多规格
        else
        {
            //多规格第一行商品数据
            foreach( (array)$this->title[$row['type_id']] as $col=>$col_name){
                $headerGoods[$col] = $goodsData[$col] ? $goodsData[$col] : '';
            }
            $data[] = $headerGoods;

            //多规格货品数据
            foreach( $productsData as $product_row)
            {
                $productData['type_name']  = $goodsData['type_name'];
                $productData['name']       = $goodsData['name'];
                $productData['bn']         = $goodsData['bn'];
                $productData['ibn']        = $product_row['bn'];
                $productData['price']      = $product_row['price'];
                $productData['cost']       = $product_row['cost'];
                $productData['weight']     = $product_row['weight'];
                $productData['barcode']    = $product_row['barcode'];
                //货品规格
                $productData['spec'] = $this->_get_product_spec_name($product_row['spec_info']);

                //数组按照title排序
                foreach( (array)$this->title[$row['type_id']] as $col=>$col_name){
                    $content[$col] = $productData[$col] ? $productData[$col] : '';
                }
                $content['display_status'] = '显示';
                $data[] = $content;
            }//end
        }
        return $data;
    }

    /**
     * 组织商品数据
     * @param array $row 一条商品基本数据
     * @return array
     */
    private function _get_goods_data($row){
        $goodsData['bn'] = $row['bn'];
        $goodsData['name'] = $row['name'];
        $goodsData['store'] = $row['store'];
        $goodsData['brief'] = $row['brief'];
        $goodsData['description'] = str_replace( '"','""', str_replace("\n"," ",$row['intro']) );
        $goodsData['unit'] = $row['unit'];

        //图片名称
        $goodsData['pic_name'] = $this->_get_gods_images($row['goods_id']);

        //类型名称
        $goodsTypeModel = app::get('b2c')->model('goods_type');
        $goodsTypeData = $goodsTypeModel->getList('type_id,name,params', array('type_id'=>$row['type_id']) );
        $goodsData['type_name'] = $goodsTypeData[0]['name'];

        //品牌
        $goodsData['brand_name'] = $row['brand_id'];

        //商品规格
        $goodsData['spec'] = $this->_get_goods_spec_name($row['spec_desc']);

        return $goodsData;
    }

    //图片文件名称
    private function _get_gods_images($goods_id){
        $goodsImages = app::get('image')->model('image_attach')->getList('attach_id,image_id',array('target_id'=>$goods_id, 'target_type'=>'goods') );
        if( !$goodsImages ) return '';

        $imageModel = app::get('image')->model('image');
        foreach( $goodsImages as $row){
            $imageData = $imageModel->dump($row['image_id'],'url');
            $pic_name_arr[] = $row['image_id'].'@'.$imageData['url'];
        }
        $pic_name = implode('#',$pic_name_arr);
        return $pic_name;
    }

    //商品的规格信息
    private function _get_goods_spec_name($spec_desc){

        if( empty($spec_desc) ) return '';
        $specModel = app::get('b2c')->model('specification');
        foreach( (array)$spec_desc as $spec_id=>$spec_value){
            $spec_ids[] = $spec_id;
        }
        $specData = $specModel->getList('spec_name,spec_id',array('spec_id'=>$spec_ids));
        foreach( $specData as $spec_row ){
            $spec[$spec_row['spec_id']] = $spec_row['spec_name'];
        }

        foreach( (array)$spec_desc as $spec_id=>$spec_value){
            $return[] = $spec[$spec_id];
        }

        return  implode('|',$return);
    }

    //货品规格值
    private function _get_product_spec_name($spec_info){
        if ( !$spec_info ) return '';
        $arr_spec_info = explode('、',$spec_info);
        $spec_name = array();
        $spec = array();
        foreach( $arr_spec_info as $spec_val ){
            $spec_name = explode('：',$spec_val);
            $spec[] = $spec_name[1];

        }
        return implode('|',$spec);
    }

}

