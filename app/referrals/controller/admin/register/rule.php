<?php
class referrals_ctl_admin_register_rule extends desktop_controller{

    public function __construct($app)
    {
        parent::__construct($app);
        $this->is_bind_crm=$this->is_bind_crm();
        header("cache-control: no-store, no-cache, must-revalidate");
    }


    public function index()
    {

        $rule = app::get('referrals')->getConf('register_rule');
        if(empty($rule['register_point'])){
            $rule['register_point'] = 0;
        }
        $this->pagedata['rule'] = $rule; 
        $this->pagedata['is_bind_crm'] = $this->is_bind_crm;
        $this->page('admin/register/rule/frame.html');

    }

    public function save()
    {
        $this->begin();
        $setting = app::get('referrals')->getConf('register_rule');
        if($this->validate($setting,$msg)){
            app::get('referrals')->setConf('register_rule',$setting);
            $this->end(true,app::get('referrals')->_('保存成功'));
        }else{
            $this->end(false,$msg);
        }
    }

    public function validate(&$setting,&$msg)
    {
        if(!$this->is_bind_crm){
            if(is_numeric($_POST['rule']['register_point']) && $_POST['rule']['register_point']>=0){
                $setting['register_point'] =$_POST['rule']['register_point'];
            }else{
                $msg ="积分值错误";
                return false;
            }
        }
        if($_POST['rule']['status']>0){
            $setting['status'] =$_POST['rule']['status']=1;
        }else{
            $setting['status'] =$_POST['rule']['status']=0;
        }
        return true;
    }

    public function is_bind_crm()
    {
        $nodes_obj = app::get('b2c')->model('shop');
        $nodes = $nodes_obj->count( array('node_type'=>'ecos.taocrm','status'=>'bind'));
        if($nodes > 0){
            return true;
        }
        else{
            return false;
        }
    }

}


?>
