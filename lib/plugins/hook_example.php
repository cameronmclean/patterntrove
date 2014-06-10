<?php

	$ct_config['hooks']['on_post_render'][] = array("function"=>"hook_example_post", "params"=>array("bit_id","bit_cache"));
	
	function hook_example_post($bit_id,$bit_cache){
		$return = $bit_cache;
		$return .= "<br/> Bit ID: $bit_id";

		return $return;
	}

?>