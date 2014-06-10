<?php

/*

Current passive hooks:
on_post_new: parrams: blog_bits.*
on_post_edit: parrams: blog_bits.*



Current active hooks:
on_post_render: parrams: blog_bits.* //for adjusting/add the html output of the blog

Export Hook
export_post 


*/

function hooks_run($hook, $attr = array()){
			
		global $ct_config;
		if(!isset($ct_config['hooks'][$hook]) || !is_array($ct_config['hooks'][$hook]) )
			return false;
	
		$hooks = $ct_config['hooks'][$hook];
		
		foreach($hooks as $hooka){
			
			if(!function_exists($hooka['function']))
				continue;
			$param_arr = array();
			if(isset($hooka['function']) && is_array($hooka['params']) ){
				foreach($hooka['params'] as $param)
					if(isset($attr[$param])) $param_arr[] = $attr[$param];
			}
			
			 call_user_func_array (  $hooka['function'] , $param_arr );
		}
		
		
}


function hooks_run_active($hook, $attr = array(), &$return){
	global $ct_config;
	
		if(!isset($ct_config['hooks'][$hook]) || !is_array($ct_config['hooks'][$hook]) )
			return false;

		$hooks = $ct_config['hooks'][$hook];
		
		foreach($hooks as $hooka){

			if(!function_exists($hooka['function']))
				continue;
			$param_arr = array();
			if(isset($hooka['function']) && is_array($hooka['params']) ){
				foreach($hooka['params'] as $param)
					if(isset($attr[$param])) $param_arr[] = $attr[$param];
			}

			 $return = call_user_func_array (  $hooka['function'] , $param_arr );
		}
	
}

function hooks_run_export_post($ext,&$sql){
	global $ct_config;
	
		if(!isset($ct_config['hooks']['export_post'][$ext]) || !is_array($ct_config['hooks']['export_post'][$ext]) )
			return false;

			$hooka = $ct_config['hooks']['export_post'][$ext];
			if(isset($hooka['function'])){
				return call_user_func_array (  $hooka['function'] , array($sql) );
			}
			
			return false;
			 
		
	
}

?>