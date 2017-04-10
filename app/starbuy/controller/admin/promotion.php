<?php

class starbuy_ctl_admin_promotion extends desktop_controller{
    function index(){

        $type_href = 'index.php?app=starbuy&ctl=admin_promotions_type&act=alertpages&nobuttion=1&goto='.urlencode('index.php?app=starbuy&ctl=admin_promotions_type&act=index&nobuttion=1&type='.$this->short_object_name);
        $this->finder(
            'starbuy_mdl_special',
            array(
                'title'=>app::get('starbuy')->_('组合促销规则'),
                'actions'=>array(
                    array(
                        'label'=>app::get('starbuy')->_('添加规则'),
                        'target'=>'_blank',
                        'href'=>'index.php?app=starbuy&ctl=admin_promotion&act=add_rule',
                    ),
                    array(
                        'label'=>app::get('starbuy')->_('促销类型'),
                        'group'=>array(
                            array(
                                'label'=>app::get('starbuy')->_('类型设置'),
                                'target'=>'_blank',
                                'href'=>$type_href,
                            ),
                        ),
                    ),
                ),
            )
        );

    }

    function add_rule(){
        $this->_public_data();
        $objPro = kernel::single('starbuy_special_promotion');
        if(!isset($this->pagedata['filter'])){
            $this->pagedata['filter'] = $objPro->extend_filter();
        }
        $this->singlepage('admin/promotion.html');
    }

    function edit_rule($id){

        $mdl_special = app::get('starbuy')->model('special');
        $objPro = kernel::single('starbuy_special_promotion');
        $mdl_special_goods = app::get('starbuy')->model('special_goods');
        $special = $mdl_special->getRow('*',array('special_id'=>$id));
        $promotion_pro = $special['promotion_pro'];
        unset($special['promotion_pro']);
        $special['promotion_pro'] = array_keys($promotion_pro);
        if($special['timeout']){
            $special['iftimeout'] = 'true';
        }
        if(!$special['remind_time']){
            $special['remind'] = "false";
            $special['remind_time'] = null;
        }else{
            $special['remind'] = "true";
        }
        if(!$special['limit']){
            $special['limit'] = null;
        }
        if(!$special['initial_num']){
            $special['initial_num'] = null;
        }
        foreach($promotion_pro as $key=>$value){
            if(is_array($value))break;
            unset($promotion_pro[$key]);
            $promotion_pro[$key]['newprice'] = $value;
            $promotion_pro[$key]['initnum'] = $special['initial_num'];
        }
        $this->pagedata['ruleInfo'] = $special;
        $this->pagedata['extend']= $promotion_pro;
        $this->pagedata['filter'] = $objPro->extend_filter(array('special_id' => $id));
        unset($promotion_pro[$key]);
        $this->add_rule();
        //$this->_public_data();
        //$this->singlepage('admin/edit_promotion.html');

    }

    function _public_data(){
        $type = app::get('starbuy')->model('promotions_type');
        $typelist = $type->getList('*');
        if($typelist){
            foreach($typelist as $value){
                $typenames[$value['type_id']] = $value['name'];
            }
        }

        $this->pagedata['remind_way'] = array(
            'email'=>app::get('starbuy')->_('邮件提醒'),
            #'msgbox'=>app::get('starbuy')->_('站内信提醒'),
            'sms'=>app::get('starbuy')->_('手机短信'),
        );
        $this->pagedata['promotins_type'] = $typenames;
    }

    function save_rule(){
        $this->begin();
        $mdl_special = app::get('starbuy')->model('special');
        $mdl_special_goods = app::get('starbuy')->model('special_goods');
        $postdata = $this->_prepareRuleData($_POST);
        $result = $mdl_special->save($postdata);
        $this->end($result);
    }

    function _prepareRuleData($param){

        $mdl_promotions_type = app::get('starbuy')->model('promotions_type');

        #促销规则相关数据整理
        $rule = $param['ruledata'];
        $rule['timeout'] = $rule['timeout'] ? $rule['timeout'] : 0;
        $rule['limit'] = $rule['limit'] ? $rule['limit'] : 0;
        $rule['remind_time'] = ($rule['remind_time'] && $rule['remind'] =="true") ? $rule['remind_time'] : 0;
        $rule['initial_num'] = $rule['initial_num'] ? $rule['initial_num'] : 0;

        #处理发布、开始、结束时间
        $hour = $param['_DTIME_']['H'];
        $release_h = $hour['release_time'];
        $begin_h = $hour['begin_time'];
        $end_h = $hour['end_time'];

        $rule['release_time'] = strtotime($param['release_time'].' '.$release_h.':00:00');
        $rule['begin_time'] = strtotime($param['begin_time'].' '.$begin_h.':00:00');
        $rule['end_time'] = strtotime($param['end_time'].' '.$end_h.':00:00');

        if($rule['release_time'] >= $rule['begin_time']){
            $this->end(false,'发布时间不能小于或等于开始时间！' );
        }
        if($rule['begin_time'] >= $rule['end_time']){
            $this->end(false,'结束时间不能小于或等于开始时间！' );
        }
        if(!is_numeric($rule['timeout'])){
            $this->end(false,'自动关闭订单限时只能位数字' );
        }

        if($rule['type_id'] == "other"){
            $type['name'] = $rule['other'];
            $type['bydefault'] = 'false';
            $type_id = $mdl_promotions_type->insert($type);
            unset($rule['type_id']);
            unset($rule['other']);
            if($type_id){
                $rule['type_id'] = $type_id;
            }else{
                $this->end(false,'自定义促销类型保存失败' );
            }
        }

        if(!$this->_checkpro($rule,$msg)){
           $this->end(false,$msg);
        }

        $rule['remind_way'][count($rule['remind_way'])] = "msgbox";

        #促销货品数据整理
        $checkresult = true;
        foreach($rule['promotion_pro'] as $value){

            $products[]=array(
                'product_id'=>$value,
                'promotion_price'=>$param['extend'][$value]['newprice'],
                'price'=>$param['oldprice'][$value],
                'release_time'=>$rule['release_time'],
                'begin_time'=>$rule['begin_time'],
                'end_time'=>$rule['end_time'],
                'type_id' => $rule['type_id'],
                'remind_time'=>$rule['remind_time'],
                'limit'=>$rule['limit'],
                'cdown'=>$rule['cdown'],
                'initial_num'=>$param['extend'][$value]['initnum'],
                'timeout'=>intval($rule['timeout']),
                'status'=>$rule['status'],
                'description'=>$rule['description'],
                'remind_way'=>$rule['remind_way'],
                //'special_id' => $rule['special_id'],
            );
            $promotion_pro[$value]=$param['extend'][$value];
        }
        unset($rule['promotion_pro']);

        $rule['promotion_pro'] = $promotion_pro;
        $rule['products'] = $products;
        return $rule;
    }


    function _checkpro($data,&$msg){
        $pro_mdl = app::get('starbuy')->model('special_goods');
        $filter['end_time|bthan'] = $data['release_time'];
        $filter['status'] = "true";
        $filter['product_id'] = $data['promotion_pro'];
        $filter['special_id|noequal'] = $data['special_id'];

        $checkdata = $pro_mdl->getList('product_id',$filter);
        if($data['status']=='true' && $checkdata){
            $msg = "以下货品ID参加的其他活动还没有结束:";
            foreach($checkdata as $val){
                $msg .= $val['product_id']." ";
            }
            return false;
        }
        return true;
    }

}
