<?php

class preparesell_mdl_preparesell extends dbeav_model{
	var $has_many = array(
        'products' => 'preparesell_goods:replace',
        );

}