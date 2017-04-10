<?php
class preparesell_ctl_admin_delprepare extends desktop_controller
{	//删除预售过期规则
	 function del_rules()
    {   
        $this->pagedata['prepare_id']=$_POST['prepare_id'];
        $this->display('admin/del.html');
    }
	function to_del()
	{	
    //echo '<pre>';print_r($_POST);exit();
		$mdl_preparesell = app::get('preparesell')->model('preparesell');
    $mdl_goods = app::get('b2c')->model('goods');
		$row=$mdl_preparesell->getList('*',array('prepare_id'=>$_POST['prepare_id']));
		$nowtime=time();
        foreach($row as $key =>$v){
            if( $nowtime < $v['begin_time'] || $nowtime>$v['end_time_final'] || $v['status']=='false')
            {
                $goods_id[]=$v['goods_id'];
            }
        }
       	if(!empty($goods_id))
       	{	
       		$this->begin('index.php?app=preparesell&ctl=admin_preparesell&act=index');
       		
       		if($_POST['is_del']=='true')
       		{
       			$data_goods=array('nostore_sell'=>1);
       		}
       		else
       		{
       			$data_goods=array('nostore_sell'=>0);
       		}
          //echo '<pre>';print_r($goods_id);exit();
	        $filter_goods=array(
	            'goods_id|in'=>$goods_id
	        );
          //echo '<pre>';print_r($filter_goods);exit();
	        $del_pre=$mdl_preparesell->delete($filter_goods,'delete');
	        $update_goods=$mdl_goods->update($data_goods,$filter_goods);
	        $del=array($update_goods,$del_pre);
	        $this->end($del,true,app::get('preparesell')->_('删除成功'));
       	}
       	else
       	{	

       		$this->begin('index.php?app=preparesell&ctl=admin_preparesell&act=index');

       		$this->end(false,app::get('preparesell')->_('活动未结束，不可删除'));
       	}
	}
}
?>