<?php

class preparesell_task
{

    public function pre_install()
    {
        app::get('preparesell')->setConf('app_is_actived','true');
        logger::info('Initial search');
        kernel::single('base_initial', 'search')->init();
    }//End Function

    public function post_uninstall(){
        app::get('preparesell')->setConf('app_is_actived','false');
    }

    public function post_update($dbver){
        if($dbver['dbver'] < 0.2)
        {
            if(app::get('preparesell')->is_actived())
            {
                app::get('preparesell')->setConf('app_is_actived','true');
                logger::info('Now "preparesell.app_is_actived" is "true"');
            }else
            {
                app::get('preparesell')->setConf('app_is_actived','false');
                logger::info('Now "preparesell.app_is_actived" is "false"');
            }
        }
    }

}//End Class
