<?php
class dev_ctl_tools extends desktop_controller{
    
    function tablesql(){
        $table = new base_application_dbtable;
        $table = $table->detect($_GET['tableapp'],$_GET['tablename']);
        $sql = $table->get_sql();
        echo '<textarea style="height:95%;width:100%;border:none;margin:0;padding:0">',
        '# Defined: ',
        realpath($table->getPathname()),'/',$table->key,'.php',
        "\n\n",
        $sql,
        '</textarea>';
    }
    
    function tablesrc(){
        header('Content-type: text/html;charset=utf8');
        $_GET['tableapp'] = $this->sanitize_plural_expression($_GET['tableapp']);
        $_GET['tablename'] = $this->sanitize_plural_expression($_GET['tablename']);
        $file = APP_DIR.'/'.$_GET['tableapp'].'/dbschema/'.$_GET['tablename'].'.php';
        echo '<div style="padding:2px 10px;border-bottom:1px solid #ccc">'.$file.'</div>';
        highlight_file($file);
    }
  function sanitize_plural_expression($expr) {
    $expr = preg_replace('@[^a-zA-Z0-9_]@', '', $expr);
    return $expr;
  }

}