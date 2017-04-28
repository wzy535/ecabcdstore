<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class desktop_finder_builder_view extends desktop_finder_builder_prototype{
    public $use_buildin_new_dialog = false;
    public $use_buildin_set_tag = false;
    public $use_buildin_recycle = true;
    public $use_buildin_export = false;
    public $use_buildin_import = false;
    public $use_buildin_filter = false;
    public $use_buildin_setcol = true;
    public $use_buildin_refresh = true;
    public $use_buildin_selectrow =true;
    public $use_buildin_tagedit =true;
    public $allow_detail_popup =false;
    public $use_save_filter = true;//是否显示保存搜索结果
    public $max_actions =7;
    public $filter = array();
    public $delete_confirm_tip = '';
    public $base_query_string = '';

    /**
     * @var 全局变量,控制视图
     */
    private $__view = array();

    function main()
    {
        $this->html_script = '';
        $this->html_header = '';
        $this->html_body   = '';
        $this->html_footer = '';
        $this->html_pager  = '';
        $this->html_actions= '';

        $this->short_object_name = substr($this->object_name,strpos($this->object_name,'_mdl_')+5);
        $this->__view = $this->get_views();

        if(count($this->__view) && $this->use_view_tab){
            $this->tab_view_count = 0;
            foreach((array)$this->__view as $view){
                if($view['addon'])
                    $this->tab_view_count += $view['addon'];
            }
            if(!$_GET['view']){
                $default_view = reset($this->__view);
                $view_filter = $default_view['filter'];
            }else{
                $view_filter = (array)$this->__view[$_GET['view']]['filter'];
                // 如果有view_tab的情况下,则将当前view的总数记下，减少同一finder两次进行count计算，增加性能
                if($this->__view[$_GET['view']]['addon']!='_FILTER_POINT_'){
                    $this->current_view_count = $this->__view[$_GET['view']]['addon'];
                }
            }
        }
        $this->__view_filter = $view_filter;

        if($_GET['filter']){
            $get_filter = (array)$_GET['filter'];
            if(!is_array($_GET['filter'])){
                if(isset($_GET['filter']) && $_GET['filter']=(array)unserialize(urldecode($_GET['filter']))){
                    $get_filter = (array)$_GET['filter'];
                }
            }
        }
        #if( $_POST ) $view_filter = array();
        $this->params = array_merge(
            (array)$this->base_filter,
            (array)$get_filter,
            (array)$view_filter,
            (array)$_POST
        );
        /** 用于打开的input_object object_base_filter **/
        if (isset($_GET['obj_filter'])&&$_GET['obj_filter']) $this->params = array_merge($this->params,array('obj_filter'=>$_GET['obj_filter']));

        unset($this->params['_finder']);

        foreach($this->params as $k=>$v){
            if(!is_array($v)&&$v!==false)
            $this->params[$k] = trim($v);
            if($this->params[$k]===''){
                unset($this->params[$k]);
            }
        }

        $this->getColumns();
        $this->getOrderBy();

        $this->pagelimit = $this->getPageLimit();

        $this->var_name = 'window.finderGroup[\''.$this->name.'\']';

        if($this->detail_pages){
            $this->detail_url = $this->url.'&action=detail&finder_id='.$this->name;
        }


        $desktop = app::get('desktop');
        $render = $this->render = new base_render($desktop);
        $render->pagedata = $this->controller->pagedata;
        $render->pagedata['title'] = $this->title;
        $render->pagedata['name'] = $this->name;
        $render->pagedata['var_name'] = $this->var_name;
        $render->pagedata['url'] = $this->url;
        $render->pagedata['use_save_filter'] = $this->use_save_filter;
        // finder列表顶部自定义显示html信息
        if($this->top_extra_view){
          $render->pagedata['top_extra'] = "";

          foreach($this->top_extra_view as $app=>$view){

                $current_app = app::get($app);
                $_render = new base_render($current_app);

                $_render->pagedata = $render->pagedata;

                $render->pagedata['top_extra'].= $_render->fetch($view);
          }
        }

        $this->createView();
        $this->_pager();

        $output ='';
        if(!$_POST['_finder']['in_pager']){

            $output.= $this->controller->sidePanel();

            $this->_script();
            $output.= $this->html_script;

            $this->_actions();


            $this->_header($this->html_subheader);

            $this->_footer($this->html_pager);

            $output.= '<!-----.mainHead-----'.
                  $render->fetch('finder/view/finder_title.html').
                  $this->html_actions.
                  $this->html_header.
                  '-----.mainHead----->'.
                  $this->html_body.
                 '<!-----.mainFoot-----'.
                  $this->html_footer.
                 '-----.mainFoot----->';

        }else{

            $output.= '<!-----.pager-----'.$this->html_pager.'-----.pager----->'.
                    $this->html_body.
                '<!-----.innerheader-----'.$this->html_subheader.'-----.innerheader----->';
        }

       echo $output;
       return $output;

    }

    function _script(){

        $finderOptions = array(
            'selectName'=>$this->dbschema['idColumn'].'[]',
            'object_name'=>$this->object_name,
            'finder_aliasname'=>$this->finder_aliasname,
            'packet'=>$this->__view ? true : false,
        );

        $finderOptions = ecos_cactus('desktop','finder_builder_view_script_gen_finderoptions',$this->__view,$this->options,$finderOptions);

        $this->html_script .=<<<EOF
<script>
Ex_Loader('finder',function(){
 finderDestory();
 var finderOption={$finderOptions};
 {$this->var_name} = new Finder("{$this->name}",finderOption);
EOF;
        $this->html_script.='});</script>';


    }

    function _header($subheader = ''){


            $render = $this->render;

            $render->pagedata['inputhtml'] = $this->toinput($this->params);

            $render->pagedata['subheader'] = $subheader;

            if(isset($_GET['filter_class']) && isset($_GET['filter_class']) != null )
            {
                $_GET['filter'] = $_GET['filter_class'];
                unset($_GET['filter_class']);
            }
            $query = $_GET;
            unset($query['page']);
            $query = utils::http_build_query($query);
            $render->pagedata['query'] = $query;


            $this->html_header = $render->fetch('finder/view/header.html');

    }

    function _subheader(){}

    function _body(){


    }

    function _footer($pager){

            $render = $this->render;
            $render->pagedata['pager'] = $pager;
            $this->html_footer = $render->fetch('finder/view/footer.html');


    }

    function _pager(){

        $pre_btn_addon = $this->pager_info['current']>1?'':'disabled="disabled"';
        $next_btn_addon = $this->pager_info['current']<$this->pager_info['total']?'':'disabled="disabled"';

        $nextpage = $this->pager_info['current']+1;
        $prevpage = $this->pager_info['current']-1;


        $from = $this->pagelimit*($this->pager_info['current']-1)+1;
        $to   = $from+$this->pager_info['list']-1;



        $pager = $this->ui->desktoppager(array(
            'current'=>$this->pager_info['current'],
            'total'=>$this->pager_info['total'],
            'link'=>'javascript:'.$this->var_name.'.page(%d);'
            ));

        $plimit_sel = '';

        foreach($this->plimit_in_sel as $sel){
            $checkcode = $this->pagelimit==$sel?' checked="checked" ':'';
            $___t=app::get('desktop')->_('条');
            $plimit_sel .= <<<EOF
            <div class="item" onclick="{$this->var_name}.request({data:'plimit={$sel}'})">
            <input type="radio" name="finder_plimit" $checkcode id=""/>
            <label>{$sel}$___t</label>
            </div>
EOF;
        }

        $render = $this->render;
        $render->pagedata['plimit'] = $this->pagelimit;
        $render->pagedata['plimit_sel'] = $plimit_sel;


        $render->pagedata['from'] = $from;
        $render->pagedata['to'] = $to;

        $render->pagedata['pre_btn_addon'] = $pre_btn_addon;
        $render->pagedata['next_btn_addon'] = $next_btn_addon;
        $render->pagedata['pager'] = $pager;

        $this->html_pager = $render->fetch('finder/view/pager.html');


    }

    function _actions(){
        $finder_name = $this->name;
        $actions = $this->actions;

        if($this->use_buildin_new_dialog){
            $actions[] = array('label'=>app::get('desktop')->_('新建'),'icon'=>'add.gif','href'=>$this->url.'&action=new_item','target'=>'dialog::{width:400,title:\''.app::get('desktop')->_('新建').'\'}');
        }

        if($this->use_buildin_set_tag){
            $_tagaction =array(
                    'label'=>app::get('b2c')->_('标签'),
                    'icon'=>'label.gif',
                    'group'=>array(
    array('label'=>app::get('desktop')->_('为选中项打标签'),'submit'=>$this->url.'&action=tag','target'=>'dialog::{width:400,title:\''.app::get('desktop')->_('设置标签').'\'}')
                            )
                        );


            if($this->has_tag==true&&$this->use_buildin_tagedit){
                $_tagediturl='index.php?app=desktop&ctl=default&act=alertpages&nobuttion=1&goto='.urlencode('index.php?app='.$this->app->app_id.'&ctl='.$_GET['ctl'].'&act=tags&nobuttion=1&type='.$this->short_object_name);
                if (($obj = kernel::service('desktop.tags.setting')) && method_exists($obj,'gen_target_url')){
                    $obj->gen_target_url($this->short_object_name,$this->app->app_id,$_tagediturl);
                }
                array_push($_tagaction['group'],array('label'=>'_SPLIT_'),array('label'=>app::get('desktop')->_('标签设置'),'href'=>$_tagediturl,'target'=>'_blank'));

            }

            $actions[] = $_tagaction;

        }

        if($this->use_buildin_recycle){
            $actions[] = array('label'=>app::get('desktop')->_('删除'),'icon'=>'del.gif','confirm'=>$this->delete_confirm_tip?$this->delete_confirm_tip:app::get('desktop')->_('确定删除选中项？删除后可进入回收站恢复'),'submit'=>$this->url.'&action=dorecycle');
        }

        if($this->use_buildin_export){
            $export_url = 'index.php?app=importexport&ctl=admin_export&act=export_view&_params[app]='.$this->app->app_id.'&_params[mdl]='.$this->object_name;
            $actions[] = array('label'=>app::get('desktop')->_('导出'),'icon'=>'download.gif','submit'=>$export_url.'&action=export','target'=>'dialog::{width:400,height:170,title:\''.app::get('desktop')->_('导出').'\'}');
        }

        if($this->use_buildin_import){
            $import_url = 'index.php?app=importexport&ctl=admin_import&act=import_view&_params[app]='.$this->app->app_id.'&_params[mdl]='.$this->object_name;
            $actions[] = array('label'=>app::get('desktop')->_('导入'),'icon'=>'upload.gif','href'=>$import_url.'&action=import','target'=>'dialog::{width:400,height:150,title:\''.app::get('desktop')->_('导入').'\'}');
        }

        foreach((array)$this->service_object as $object){

           $actions = array_merge((array)$actions,(array)$object->actions);

        }
        foreach(kernel::servicelist('finder_actions.'.$this->object_name) as $key=>$service_object){
            if(method_exists($service_object,'action_modify')){
                $service_object->action_modify($actions);
            }
        }
        $max_action = $this->max_actions;
        $i=0;

        if (isset($actions) && $actions)
        {
            foreach($actions as $key=>$item){

            //  if(!$item['label']){continue;}

                if($item['href']){$item['href'] = $item['href'].'&_finder[finder_id]='.$finder_name.'&finder_id='.$finder_name;
                }else{
                   $item['href'] ="javascript:void(0);";
                }
                if($item['submit']){$item['submit'] = $item['submit'].'&finder_id='.$finder_name;}

                $show_actions[] = $item;
                unset($actions[$key]);
                if($i++==$max_action-1){
                    break;
                }
            }
            $other_actions = $actions;
        }

        $render = $this->render;

        $render->pagedata['show_actions'] =  $show_actions;
        $render->pagedata['other_actions'] =  $other_actions;
        $render->pagedata['finder_aliasname'] =  $this->finder_aliasname;
        $render->pagedata['finder_name'] =  $finder_name;
        $render->pagedata['use_buildin_filter'] =  $this->use_buildin_filter;
        $render->pagedata['use_buildin_setcol'] =  $this->use_buildin_setcol;
        $render->pagedata['use_buildin_refresh'] =  $this->use_buildin_refresh;

        /** 判断是否要显示归类视图 **/
        $render->pagedata['haspacket'] = $this->__view ? true : false;

        $searchOptions = array();
        if(method_exists($this->object,'searchOptions'))
            $searchOptions =  $this->object->searchOptions();


        if( is_array($searchOptions) && $this->__view_filter ) {
            foreach( $searchOptions as $key => $val ) {
                if( isset($this->__view_filter[$key]) ) {
                    unset($searchOptions[$key]);
                }
            }
        }
        $render->pagedata['searchOptions'] = $searchOptions;
        $render->pagedata['__search_options_default_label'] = current($searchOptions);

        if(isset($_GET['filter_class']) && $_GET['filter_class'] != null )
        {
            $get = $_GET;
            $get['filter'] = $get['filter_class'];
            unset($get['filter_class']);
            $render->pagedata['url'] = 'index.php?'.utils::http_build_query($get);
        }

        $this->html_actions =$render->fetch('finder/view/actions.html');

    }



    function toinput($params){
        $html = null;
        $this->_toinput($params['from'],$ret,$params['name']);
        foreach((array)$ret as $k=>$v){
            $html.='<input type="hidden" name="'.$k.'" value="'.$v."\" />\n";
        }
        return $html;
    }

    function _toinput($data,&$ret,$path=null){
        foreach((array)$data as $k=>$v){
            $d = $path?$path.'['.$k.']':$k;
            if(is_array($v)){
                $this->_toinput($v,$ret,$d);
            }else{
                $ret[$d]=$v;
            }
        }
    }

    function createView(){

        $page=$_GET['page']?$_GET['page']:1;

        $allCols = $this->all_columns();

        $modifiers = array();

        $col_width_set = app::get('desktop')->getConf('colwith.'.$this->object_name.'_'.$this->finder_aliasname.'.'.$this->controller->user->user_id);
        $filter_builder = new desktop_finder_builder_filter_render();
      //  $fb_return_data = $filter_builder->main($this->object->table_name(),$this->app,$filter,$this->controller,true);

        $type_modifier = array();
        $key_modifier = array();
        $object_modifier = array();
        $modifier_object = new modifiers;

        $tmparr_columns = array();
        foreach($this->columns as $col){
            if(isset($allCols[$col])){
                $colArray[$col] = &$allCols[$col];
                if(method_exists($this->object,'modifier_'.$col)){
                    $key_modifier[$col] = 'modifier_'.$col;
                }elseif(is_string($colArray[$col]['type'])){
                    if(substr($colArray[$col]['type'],0,6)=='table:'){
                        $object_modifier[$colArray[$col]['type']] = array();
                    }elseif(method_exists($modifier_object,$colArray[$col]['type'])){
                        $type_modifier[$colArray[$col]['type']] = array();
                    }
                }
                if(isset($col_width_set[$col]))$colArray[$col]['width'] = $col_width_set[$col];

                if(isset($allCols[$col]['sql'])){
                    $sql[] = $allCols[$col]['sql'].' as '.$col;
                }elseif($col=='_tag_'){
                    $sql[] = $dbschema['idColumn'].' as _tag_';
                }else{
                    $sql[] = '`'.$col.'`';
                }

                $label = app::get('desktop')->_($colArray[$col]['label']);

                $tmp_col_width = ($colArray[$col]['width']?$colArray[$col]['width']:150);

                if($this->orderBy==$col){
                    $col_set_class = 'class="orderCell"';
                    if($this->orderType=='desc'){
                        $col_class ='highlight-down borderdown orderable';
                    }else{
                        $col_class ='highlight-up borderdown orderable';
                    }
                }elseif(!isset($allCols[$col]['orderby']) || $allCols[$col]['orderby'] !== true){
                    $col_set_class = $col_class ='';
                }elseif(strpos($col,'column_')===false){
                    $col_set_class = 'class="orderCell"';
                    $col_class ='orderable';
                }elseif(strpos($col,'column_')!==false&&$allCols[$col]['order_field']){
                    $col_set_class = 'class="orderCell"';
                    if($this->orderBy == $allCols[$col]['order_field']){
                        if($this->orderType=='desc'){
                            $col_class ='highlight-down borderdown orderable';
                        }else{
                            $col_class ='highlight-up borderdown orderable';
                        }
                    }else{
                        $col_class ='orderable';
                    }
                    $col = $allCols[$col]['order_field'];
                }else{
                    $col_set_class = $col_class ='';
                }
                $___dragwidth=app::get('desktop')->_('拖动改变列宽');
                $___width=app::get('desktop')->_('列宽');

                $column_col_html.="<col style=\"width:".$tmp_col_width."px\" {$col_set_class}></col>\n";

                $column_td_html.=<<<EOF
<td>
  <div class="cell {$col_class}" key="{$col}" order="{$this->orderType}">
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr><td class="finder-col-title">
    <div class="finder-col-label">{$label}</div>
EOF;
            if($desc = $colArray[$col]['desc']){
                $column_td_html.="<div class='finder-col-desc desc-tip' onmouseover='bindFinderColTip(event);'><textarea>".$desc."</textarea>i</div>";
            }
                $column_td_html.=<<<EOF
    </td>
    <td width="5" class="finder-col-resizer-handle">
    <div class="finder-col-resizer" title=$___dragwidth onclick='new Event(event).stopPropagation();'>$___width</div>
    </td>
    </tr>
    </table>
  </div>
</td>
EOF;

    if($fcol = $fb_return_data['filter_cols'][$col]){
            $___limitless=app::get('desktop')->_('不限');
            $filter_column_html.=<<<EOF
        <td>
           <div class="cell">
             <div class="finder-filter-comb" dropMenu="x-dropMenu-{$col}"><button class="btn arrow"></button><span>$___limitless</span></div>
             <div class="x-drop-menu" id="x-dropMenu-{$col}">
             <textarea>{$fcol['addon']}{$fcol['inputer']}<tscript></tscript>
              <hr/>
              <div>$___limitless</div>
             </textarea></div>

           </div>
        </td>
EOF;
            }else{
          $filter_column_html.=<<<EOF
        <td>
           <div class="cell">
            &nbsp;
           </div>
        </td>
EOF;

                }
            }
        }

        foreach((array)$this->service_object as $k=>$object){
            if($object->addon_cols){
                $object->col_prefix = '_'.$k.'_';
                foreach(explode(',',$object->addon_cols) as $col){
                    $sql[] = $col.' as '.$object->col_prefix.$col;
                }
            }
        }
        $sql = (array)$sql;
        if(!isset($colArray[$this->dbschema['idColumn']])) array_unshift($sql,$this->dbschema['idColumn']);
        if($this->params===-1){
            $list = array();
        }else{
            $this->object->filter_use_like = true;
            $count_method = $this->object_method['count'];
            // 如果有view_tab的情况下,则直接使用计算顶部view_tab的addon时记下的总数，减少同一finder两次进行count计算，增加性能
            $item_count = $this->current_view_count ? $this->current_view_count : $this->object->$count_method($this->params);
            $total_pages = ceil($item_count/$this->pagelimit);
            if($page <0 || ($page >1 && $page > $total_pages)){
                $page = 1;
            }
            $getlist_method = $this->object_method['getlist'];
            $order = $this->orderBy?$this->orderBy.' '.$this->orderType:'';
            if($this->orderBy)
            {
                if(in_array($this->orderBy,(array)$this->object->metaColumn))
                {
                    //meta排序暂时不做修改
                }
                else
                {
                    list(,$obj_name,$fkey) = explode(':',$this->object->schema['columns'][$this->orderBy]['type']);
                    if($obj_name)
                    {
                        if($p = strpos($obj_name,'@')){
                            $app_id = substr($obj_name,$p+1);
                            $obj_name = substr($obj_name,0,$p);
                            $o = app::get($app_id)->model($obj_name);
                        }else{
                            $o = $this->object->app->model($obj_name);
                        }
                        $o_idColumn = $o->getList($o->idColumn,array(),0,-1,$o->schema['textColumn'].' '.$this->orderType);
                        foreach($o_idColumn as $o_k=>$o_v)
                        {
                            $filed  = $filed.",'".$o_v[$o->idColumn]."'";
                        }
                        $order= ' FIELD('.$this->orderBy.$filed.')';
                    }
                }
            }
            $list = $this->object->$getlist_method(implode(',',$sql),$this->params,($page-1)*$this->pagelimit,$this->pagelimit,$order);
            $body = $this->item_list_body($page, $list, $colArray, $key_modifier, $object_modifier, $type_modifier);
            $count = count($list);
            $total_pages = ceil($item_count/max($count,$this->pagelimit));

            $this->pager_info = array(
                'current'=> $page,
                'list'=>$count,
                'count'=>$item_count,
                'total'=> $total_pages?$total_pages:1,
              );
            $this->object->filter_use_like = false;
          }




        if($this->detail_url){
            $detail_td_html='<td class="col-opt"><div class="cell">'.app::get('desktop')->_('查看').'</div></td>';
            $detail_col_html = '<col class="col-opt"></col>';

            $filter_td_html='<td class="col-filter" colspan="2"><div class="cell">'.app::get('desktop')->_('筛选条件')>':</div></td>';
            $filter_col_html = '<col class="col-filter"></col>';
        }else{
            $detail_td_html = $detail_col_html = '';
            $filter_td_html = $filter_col_html = '';
        }

        $render = $this->render;





        $render->pagedata['detail_col_html'] = $detail_col_html;
        $render->pagedata['column_col_html'] = $column_col_html;

        $render->pagedata['detail_td_html'] = $detail_td_html;
        $render->pagedata['column_td_html'] = $column_td_html;
        $render->pagedata['pinfo'] = $this->pager_info;
        $render->pagedata['body'] = $body;

        $render->pagedata['filter_td_html'] = $filter_td_html;
        $render->pagedata['filter_column_html'] = $filter_column_html;
        $render->pagedata['filterhandle'] = $render->fetch('finder/view/filterhandle.html');
        $render->pagedata['use_buildin_selectrow'] =  $this->use_buildin_selectrow;


        $this->html_subheader = $render->fetch('finder/view/subheader.html');
        $this->html_body      = $render->fetch('finder/view/body.html');
        $this->html_pager     = $render->fetch('finder/view/pager.html');


    }

    function &item_list_body(&$page, &$list, &$colArray, &$key_modifier, &$object_modifier, &$type_modifier, $ident='col'){
        $body = array();

    $favstar_rows = app::get('desktop')->getConf('favstar.'.$this->object_name.'_'.$this->finder_aliasname.'.'.$this->controller->user->user_id);
        $icon_drop_arrow = $this->ui->img(array(src=>'bundle/finder_drop_arrow.gif',alt=>app::get('desktop')->_('展开'),title=>app::get('desktop')->_('展开')));
        $icon_new_window = $this->ui->img(array(src=>'bundle/new_window.gif',alt=>app::get('desktop')->_('新窗'),title=>app::get('desktop')->_('新窗')));
        $icon_fav_start  = $this->ui->img('bundle/fav_start.png');
        if(!$list){
            return '';
        }
        if(is_array($this->detail_pages)){
            $default_detail = '&finderview='.key($this->detail_pages);
        }
        foreach($list as $i=>$row){
            $row_style = array();

            if($this->row_style_func){
                foreach($this->row_style_func as $object){
                    $row_style[] = $object->row_style($row);
                }
            }

            $zebra_class = $i % 2 ? 'even' : 'odd';

            if($i==0){
                $zebra_class.=' first';
            }
            if($i==(count($list)-1)){
                $zebra_class.=' last';
            }

            $id = htmlspecialchars($row[$this->dbschema['idColumn']]);
            $body[] = '<tr class="row '.$zebra_class.' '.implode(';',$row_style).'" item-id="'.$id.'">';
            $tag = $this->has_tag?(' tags="'.htmlspecialchars($row['_tags']).'"'):'';

            $singleselect = $_GET['singleselect'];

            if($this->use_buildin_selectrow){
                if($singleselect){
                    $singleselect = 'radio';
                }else{
                    $singleselect = 'checkbox';
                }
                $star_class = '';
                $fav_sel='';
            if(intval($favstar_rows['id-'.$id])==1){
                $star_class = 'fav-star-on';
                $fav_sel = 'isfav';
            }
            $body[] = '<td>
                        <div class="clearfix">
                            <div class="span-auto">
                                <input type="'.$singleselect.'"'.$tag.' class="sel '.$fav_sel.'" name="items[]" rowindex="'.(($this->pagelimit * ($page-1)) + $i).'" value="'.$id.'">
                            </div>
                            <div class="flt">
                                <i class="fav-star '.$star_class.'">'.$icon_fav_start.'</i>
                            </div>
                        </div></td>';
         }

            if($this->detail_url){
                if($this->base_query_string){
                    $this->base_query_string = '&'.$this->base_query_string;
                }
            if($this->allow_detail_popup){
                $detail_popup_btn ="<a title='".app::get('desktop')->_('在新窗口查看')."' href='{$this->detail_url}&id={$id}&singlepage=true{$default_detail}{$this->base_query_string}' target='_blank'>
                {$icon_new_window}</a>";
            }else{
                $detail_popup_btn = "";
            }
            $___look=app::get('desktop')->_('展开查看');

                $body[]=<<<EOF
    <td class="finder-list-command">
    <span title=$___look class="btn-detail-open" detail="{$this->detail_url}&id={$id}{$default_detail}{$this->base_query_string}" >
    {$icon_drop_arrow}
    </span>
    {$detail_popup_btn}
</td>
EOF;
            }
            //$funcs = $this->func_columns();
            foreach((array)$colArray as $k=>$col){
                    $body[] = '<td key="'.$k.'" '.($col['editable']?'class="editable"':'').'><div class="cell">';
                    if($col['type']=='func'){
                        $row['idColumn'] = $this->dbschema['idColumn'];
                        $row['app_id'] = $row['app_id']?$row['app_id']:$this->app->app_id;
                        $row['tag_type'] = $row['tag_type']?$row['tag_type']:$this->short_object_name;
                        $body[] = $a = $col['ref'][0]->{$col['ref'][1]}($row);
                    }elseif(isset($key_modifier[$k])){
                        $this->object->pkvalue = $row[$this->dbschema['idColumn']];
                        $body[] = $this->object->{$key_modifier[$k]}($row[$k]);
                    }elseif(is_array($col['type']) && !is_null($row[$k])){
                        $body[] = &$col['type'][$row[$k]];
                    }elseif(isset($object_modifier[$col['type']])){
                        $object_modifier[$col['type']][$row[$k]] =$row[$k];
                        $body[] = &$object_modifier[$col['type']][$row[$k]];
                    }elseif(isset($type_modifier[$col['type']])){
                        $type_modifier[$col['type']][$row[$k]] = $row[$k];
                        $body[] = &$type_modifier[$col['type']][$row[$k]];
                    }else{
                        $body[] = $row[$k];
                    }
                    $body[] = '</div></td>';
            }

            $body[] = '<td>&nbsp;</td></tr>';
        }

        /*下面的代码仅仅为了补全记录未满设定的每页显示数量
        $list_count = count($list);
        $cols_count = 1;
        if($this->detail_url){
            $cols_count++;
        }
        $cols_count += count($colArray);
        if($list_count<$this->pagelimit){
            for($i=0;$i<($this->pagelimit)-count($list_count);$i++){
                $zebra_class = $zebra_class=='even' ? 'odd' : 'even';
                $body[] = '<tr class="row '.$zebra_class.'" >';
                for($k=0; $k<$cols_count; $k++){
                    $body[] = '<td><div class="cell">-</div></td>';
                }
                $body[] = '<td>&nbsp;</td></tr>';

            }
        }
        结束*/

        if($type_modifier){
            $type_modifier_object = new modifiers;
            foreach($type_modifier as $type=>$val){
                if($val){
                    $type_modifier_object->$type($val);
                }
            }
        }

        foreach($object_modifier as $target=>$val){
            if($val){
                list(,$obj_name,$fkey) = explode(':',$target);
                if($p = strpos($obj_name,'@')){
                    $app_id = substr($obj_name,$p+1);
                    $obj_name = substr($obj_name,0,$p);
                    $o = app::get($app_id)->model($obj_name);
                }else{
                    $o = $this->object->app->model($obj_name);
                }
                if(!$fkey)$fkey = $o->textColumn;
                $rows = $o->getList($o->idColumn.','.$fkey,array($o->idColumn=>$val));
                foreach($rows as $r){
                    $object_modifier[$target][$r[$o->idColumn]] = $r[$fkey];
                }
                $app_id = null;
            }
        }

        $body = implode('',$body);
        return $body;
    }
}
