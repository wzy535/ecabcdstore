<?php

class starbuy_ctl_site_special extends starbuy_frontpage{

    function __construct($app){
        parent::__construct($app);
        $this->app = $app;
        $this->mdl_special_goods = app::get('starbuy')->model('special_goods');
        $this->mdl_product = app::get('b2c')->model('products');
        $this->mdl_goods = app::get('b2c')->model('goods');
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->userObject = kernel::single('b2c_user_object');
        $this->special_pro = kernel::single('starbuy_special_products');
    }
    function index($type_id,$page=1){
        $type_id = $this->_request->get_param(0);
        $GLOBALS['runtime']['path'] = $this->runtime_path($type_id);
        $this->pagedata['type_id']=$type_id;
        $page = $page ? $page : 1;
        $params = $this->filter_decode('',$type_id);
        $filter = $params['filter'];
        $orderby = $params['orderby'];
        $beabout = $this->get_product($filter,$page,$orderby);
        $released = $this->get_released_product($filter);
        $this->pagedata['filter']=$filter;
        $this->pagedata['orderby_sql'] = $orderby;

		$this->pagedata['imageDefault'] = app::get('image')->getConf('image.set');
		$this->pagedata['goodsData'] = $beabout;
		$this->pagedata['releasedgoodsData'] = $released;
		#获取会员的登录状态
		$this->pagedata['login_id'] = ($this->userObject->get_member_id()) ? ($this->userObject->get_member_id()) : "0";
		$this->page('site/gallery/index.html');
	}


    function runtime_path($type_id,$product_id=null){
        $title = kernel::single('starbuy_special_products')->getTypename(array('type_id'=>$type_id));

        $url = "#";
        if($product_id){
            $url = $this->gen_url(array('app'=>'starbuy', 'ctl'=>'site_special','act'=>'index','arg0'=>$type_id));
        }
        $path = array(
            array(
                'type'=>"goodscat",
                'title'=>"首页",
                'link'=>kernel::base_url(1),

            ),
            array(
                'type'=>"goodscat",
                'title'=>$title,
                'link'=>$url,
            ),
        );

        if($product_id){
            $product = $this->_getProduct($product_id);
            $path[] = array(
                'type'=>"goodscat",
                'title'=>$product['name'],
                'link'=>"#",
                );
        }
        return $path;
    }
    function get_product($filter,$page=1,$orderby="promotion_price asc"){

        $filter['begin_time|sthan']=time();
        $filter['end_time|bthan']=time();
        $filter['status']="true";
        //分页
        $beabout_total = $this->count($filter);
        $this->pagedata['total'] = $beabout_total;
        $pageLimit = app::get('b2c')->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 20);
        $this->pagedata['page'] = $page;
        $this->pagedata['pageLimit'] = $pageLimit;
        $pagetotal= $this->pagedata['total'] ? ceil($this->pagedata['total']/$pageLimit) : 1;
        $max_pagetotal = $this->app->getConf('gallery.display.pagenum');
        $max_pagetotal = $max_pagetotal ? $max_pagetotal : 100;
        $this->pagedata['pagetotal'] = $pagetotal > $max_pagetotal ? $max_pagetotal : $pagetotal;

        //获取参加团购的货品
        $special_goods = $this->mdl_special_goods->getList("*",$filter,$pageLimit*($page-1),$pageLimit,$orderby,$total=false);
        foreach($special_goods as $value){
            $result[] = $this->special_pro->getParams($value);
        }
        return $result;
    }

    function get_released_product($filter){
        $filter['release_time|sthan']=time();
        $filter['begin_time|bthan']=time();
        $filter['status']="true";
        $this->pagedata['beabout_total'] = $this->count($filter);
        //获取参加团购的货品
        $special_goods = $this->mdl_special_goods->getList("*",$filter);
        foreach($special_goods as $value){
            $result[] = $this->special_pro->getParams($value);
        }
        return $result;
    }


    function count($filter){
        $total = $this->mdl_special_goods->count($filter);
        return $total;
    }

    function _getProduct($filter){
        $products="";
        if($filter){
            $products = $this->mdl_product->getRow("*",array('product_id'=>$filter));
        }
        return $products;
    }


    function ajax_get_goods(){
        $post = $this->filter_decode($_POST);
        $page = $post['page'] ? $post['page'] : 1;
        $filter = $post['filter'];
        $orderby = $post['orderby'];
        $goodsData = $this->get_product($filter,$page,$orderby);
        if($goodsData){
            $this->pagedata['goodsData'] = $goodsData;
            echo $this->fetch('site/gallery/type/grid.html');
        }
    }

    function filter_decode($params=null,$type_id){
        if(!$params){
            $cookie_filter = $_COOKIE['S']['SPECIAL']['FILTER'];
            if($cookie_filter){
                $tmp_params = explode('&',$cookie_filter);
                foreach($tmp_params as $k=>$v){
                    $arrfilter = explode('=',$v);
                    $f_k = str_replace('[]','',$arrfilter[0]);
                    if($f_k == 'type_id' || $f_k == 'orderby'|| $f_k == 'page'){
                        $params[$f_k] = $arrfilter[1];
                    }else{
                        $params[$f_k][] = $arrfilter[1];
                    }
                }
            }
            if($params['type_id'] != $type_id){
                unset($params);
                $this->set_cookie('S[SPECIAL][FILTER]','nofilter');
            }
        }//end if

        if($params['orderby']){
            $post['orderby'] = $params['orderby'];
            unset($params['orderby']);
        }else{
            $post['orderby'] = 'promotion_price asc';
            unset($params['orderby']);
        }

        if($params['page']){
            $post['page'] = $params['page'];
        }

        $post['filter'] = $params;

        if($type_id){
            $post['filter']['type_id'] = $type_id;
        }
        return $post;
    }

    function ajax_remind_save(){
        $remind_mdl = app::get('starbuy')->model('special_remind');
        $remind = $_POST;
        $way = $this->getWay($remind['goal'],$msg);
        if(!$way){
            $this->splash('error',"",$msg,true);
        }
        $product = $this->mdl_product->getRow('name',array('product_id'=>$remind['product_id']));
        $remind['remind_way'] = $way;
        $remind['savetime'] = time();
        $remind['goodsname'] = $product['name'];
        $count = $remind_mdl->count(array('product_id'=>$remind['product_id'],'goal'=>$remind['goal'],'type_id'=>$remind['type_id']));
        if($count){
            $this->splash('error',"",'您已经订阅过',true);
        }
        $result = $remind_mdl->save($remind);
        if($result){
            $url = $this->gen_url(array('app'=>'starbuy', 'ctl'=>'site_team','act'=>'index','arg0'=>$remind['product_id'],'arg1'=>$remind['type_id']));
            $this->splash('success','','订阅成功',true);
        }else{
            $this->splash('error','',"订阅失败",true);
        }
    }




	/*
	 * 获取提醒方式(邮箱，手机号码、站内信)
	 *
	 * @params $login_account 登录账号
	 * @return $account_type
	 */
	public function getWay($remind_way,&$msg){

		$way = "msgbox";
		if($remind_way && strpos($remind_way,'@')){
			if(!preg_match('/^(?:[a-z\d]+[_\-\+\.]?)*[a-z\d]+@(?:([a-z\d]+\-?)*[a-z\d]+\.)+([a-z]{2,})+$/i',trim($remind_way)) ){
				$msg = $this->app->_('邮件格式不正确');
				return false;
			}
			$way = 'email';
		}elseif(preg_match("/^1[34578]{1}[0-9]{9}$/",$remind_way)){
			$way = 'sms';
		}elseif($remind_way){
			$msg = $this->app->_('请输入正确邮箱或手机号码');
			return false;
		}
		return $way;
	}

	/*
	 * 团购详情页面弹出开售提醒页面
	 */

	function remind_url(){
		$get = $this->_request->get_params();
		$filter['product_id'] = $get[0];
		$filter['type_id'] = $get[1];
		$special_goods = $this->mdl_special_goods->getRow("begin_time,remind_time,remind_way,product_id",$filter);
		if(in_array('email',$special_goods['remind_way']) && !in_array('sms',$special_goods['remind_way'])){
			$listpro['email_remind'] = true;
		}elseif(in_array('sms',$special_goods['remind_way']) && !in_array('email',$special_goods['remind_way'])){
			$listpro['sms_remind'] = true;
		}elseif(in_array('email',$special_goods['remind_way']) && in_array('sms',$special_goods['remind_way'])){
			$listpro['all_remind'] = true;
		}else{
			$listpro['msgbox_remind'] = true;
		}
		$this->pagedata['product_id'] = $filter['product_id'];
		$this->pagedata['type_id'] = $filter['type_id'];
		$this->pagedata['remind_time'] = strtotime("-".$special_goods['remind_time']." hour",$special_goods['begin_time']);//$special_goods['begin_time'];
		$this->pagedata['begin_time'] = $special_goods['begin_time'];
		$this->pagedata['member_id'] = ($this->userObject->get_member_id()) ? ($this->userObject->get_member_id()) : "0";
		$this->pagedata['remind_type'] = $listpro;
		echo $this->fetch('site/product/remind.html');
	}

	/*
	 *保存订阅提醒信息
	 *@POST 订购信息
	 */
	function save_remind(){
		$remind_mdl = app::get('starbuy')->model('special_remind');
		$remind = $_POST;
		$way = $this->getWay($remind['goal'],$msg);
		if(!$way){
			$this->splash('error',"",$msg,true);
		}
		$product = $this->mdl_product->getRow('name',array('product_id'=>$remind['product_id']));
		$remind['remind_way'] = $way;
        $count = $remind_mdl->count(array('product_id'=>$remind['product_id'],'goal'=>$remind['goal'],'type_id'=>$remind['type_id']));
        if($count){
            $this->splash('error',"",'您已经订阅过',true);
        }

		$remind['savetime'] = time();
		$remind['goodsname'] = $product['name'];
		$result = $remind_mdl->save($remind);
		if($result){
			$url = $this->gen_url(array('app'=>'starbuy', 'ctl'=>'site_team','act'=>'index','arg0'=>$remind['product_id'],'arg1'=>$remind['type_id']));
			$this->splash('success',$url,'订阅成功',true);
		}else{
			$this->splash('error',null,"订阅失败",true);
		}
	}

    function getNowTime(){
        $timenow['timeNow'] = time();
        $timenow = json_encode($timenow);
        echo $timenow;
    }


}
