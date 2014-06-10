<?php
	if(!isset(	$ct_config['default_config_loaded'])){
		$pwd = dirname(__FILE__);
  		include_once( "$pwd/default_config.php");
	}
  include_once("{$ct_config['pwd']}/lib/database/mysql.php");
  // include_once("{$ct_config['pwd']}/lib/database/postgres.php"); // postgres support is still in beta, use with caution
  include_once("{$ct_config['pwd']}/lib/database/common.php");

?>