<?php
class referrals_member_policy
{
    public function __construct($app)
    {
        $this->app = $app;
        //$this->bind_crm_status= $this->is_bind_crm();
        //$this->register_rule = $this->app->model('register_rule');
        $this->register_record = $this->app->model('register_record');
        $this->b2c_members_model = app::get('b2c')->model('members');
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

    public function referrals_member($referrals_code,$member_id)
    {
        $referrals_setting = app::get('referrals')->getConf('register_rule');
        if( is_array($referrals_setting)  ){
            $this->referrals_Process($referrals_code,$member_id,$referrals_setting['register_point']);
        }
        else{
            return false;
        }

    }

    public function referrals_Process($referrals_code,$member_id,$register_point)
    {
        $save_data = $this->referrals_member_info($referrals_code,$member_id,$register_point);
        $this->referrals_parent_code($referrals_code,$member_id,$register_point,$save_data['reference_id']);
    }

    public function referrals_parent_code($referrals_code,$member_id,$register_point,$referrals_member_id)
    {
        $is_bind_crm=$this->is_bind_crm();
        if($is_bind_crm){
            $register_member_info=$this->members_info($member_id);
            $data = array(
                'register_crm_member_id'=>$register_member_info['crm_member_id'],
                'parent_code'=>$referrals_code,
                'point'=>$register_point
                 );   
            $point_rpc_obj = kernel::service('b2c_member_point_rpc_async');
            $point_rpc_obj->changeByParentcodeActive($data);
        }else{
            $this->point_change($referrals_member_id,$register_point);
        }
    }

    public function point_change($referrals_member_id,$register_point)
    {
        $mem_point =kernel::single('b2c_mdl_member_point');
        $msg = '推荐送积分';
        $mem_point->change_point($referrals_member_id,$register_point,$msg,'referrals',2,0,$referrals_member_id,'charge');
    }

    public function create_code($member_id)
    {
        $this->members_info($member_id);
        return $this->return_code_rule();
    }

    public function members_info($member_id)
    {
        $columns=array('member_id','crm_member_id','referrals_code');
        $columns = implode(',',$columns);
        $this->member_info = $this->b2c_members_model->getRow($columns,array('member_id'=>$member_id));
        return $this->member_info;

    }

    public function return_code_rule()
    {
        if(!empty($this->member_info['referrals_code'])){
            return $this->member_info['referrals_code'];
        }
    	$code = $this->code_rule();
        $this->b2c_members_model->update(array('referrals_code'=>$code),array('member_id'=>$this->member_info['member_id']));
        return $code;
    }

    public function code_rule()
    {
    	$code='ec_'.substr(md5(time().$this->member_info['member_id']),0,14);
    	return $code;
    }

    public function referrals_members_info($member_id)
    {

        $reference_id = $this->register_record->getRow('reference_id,referrals_code',array('register_id'=>$member_id));
        if($reference_id['reference_id']){
            $result=$this->members_info($reference_id['reference_id']);
            return $result;
        }elseif($reference_id['reference_id'] == 0  && !empty($reference_id['referrals_code'])){
            return $reference_id;
        }else{
            return false;
        }
    }

    public function crm_save($points)
    {
        if(!is_numeric($points)){
            return false;
        }else{
            $referrals_setting = app::get('referrals')->getConf('register_rule');
            $referrals_setting['register_point'] = $points;
            app::get('referrals')->setConf('register_rule',$referrals_setting);
        }
        return true;
    }

    public function save_referrals_member($referrals_code,$member_id){
        $referrals_setting = app::get('referrals')->getConf('register_rule');
        if( is_array($referrals_setting)){
            $this->referrals_save($referrals_code,$member_id,$referrals_setting['register_point']);
        }
        else{
            return false;
        }
    }
    public function referrals_save($referrals_code,$member_id,$register_point){
        $save_data = $this->referrals_member_info($referrals_code,$member_id,$register_point);
        $this->register_record->save($save_data);
    }
    public function referrals_member_info($referrals_code,$member_id,$register_point){
        $result = $this->b2c_members_model->getRow('member_id',array('referrals_code'=>$referrals_code));
        if(!empty($result['member_id'])){
            $referrals_member_id = $result['member_id'];
            //$this->point_change($referrals_member_id,$register_point);
        }
        else{
            $referrals_member_id = 0;
        }

        $save_data=array(
            'reference_id' => $referrals_member_id,
            'register_id' => $member_id,
            'regtime'     => time(),
            'register_point' => $register_point,
            'referrals_code' => $referrals_code
            );
        return $save_data;
    }


}
?>
