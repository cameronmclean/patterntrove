<?php

	
	$ct_config['core_version'] = "2.3";

	/* default config */
	/* DO NOT EDIT: you can overide anything in ../config.php */
	/* This will get over written by any update! */
	
	$ct_config['label_server'] = false;
	
	/* southampton uni chemtool login system only */
	$ct_config['soap_host'] = "";
	$ct_config['soap_login'] = "";
	
	/* order for side bar */
	$ct_config['blog_infobar'] = array("search","thispost","thisblog","archives","authors","sections","meta","tools");
	
	$ct_config['blog_infobar_max_no'] = 8;
	
	/* list of no nos for the blog short name */
	$ct_config['protected_paths'] = array("api","data", "search","style","inc","admin","user","java", "dashboard","odata","cache");
	
	/* garbage collection */
	$ct_config['gc']['blogdumps'] = 48; //hours
	$ct_config['gc']['blogimgs'] = 30; //days
	$ct_config['gc']['dataitems'] = 200; //days
	
	/* remember me cookie length */
	$ct_config['rememberme']['time'] = 30; //days
	$ct_config['rememberme']['salt'] = md5("labtrovesalt");
	
	/* caching of user info (Some login pliugins only) */
	$ct_config['usercache']['enable'] = 0;
	$ct_config['usercache']['limit'] = 604800; //seconds (7 days = 604800)
	
	/* legacy */
	$ct_config['timeplot'] = "http://middleware.chem.soton.ac.uk/data/chemtools_labrecall.php";
	
	/* dev mode */
	$ct_config['devo'] = 0;
	
	$ct_config['pwd'] = dirname(dirname(__FILE__));
	/* temp dir */
	$ct_config['tmp_dir'] = "/tmp/labtrove";
	
	/* use mysql full text search engine, requires "alter table blog_bits add fulltext (bit_content, bit_title);" */
	/* disabled=0, enabled=1, enabled with stemmed queries=2 */
	$ct_config['use_mysql_fulltext_search'] = 2;
	
        /* This section controls the way larger file uploads are treated
	 * - You may need to upgrade your database schema to use this feature
         * - You will need to update php.ini to allow for larger file transfers, see the following settings
             max_execution_time, max_input_time, memory_limit, post_max_size, upload_max_filesize
         */
	/* the file size where upon the file is stored on the filesystem rather than the database, set to 0 for always in database, 
		you can set to -1 to always save to file system */
	$ct_config['uploads_threshold'] = 1024 * 1024; // 1Mb
	// $ct_config['uploads_threshold'] = 0; //always in db
	// $ct_config['uploads_threshold'] = -1; //alwats in filesystem
	
	/* where uploaded files are stored on the filesystem - make sure this exists and is writable */
	$ct_config['uploads_dir'] = "{$ct_config['pwd']}/uploads";
	/* the max size an upload can be */
	$ct_config['uploads_max_size'] = 500 * 1024 * 1024 ; // 500Mb

	$ct_config['upload_php'] = 'upload_simple.php'; // can also be the old 'upload.php'

	// sets config file if envset
	if($configstr = getenv("LABTROVE_CONFIG")){
		if(file_exists($configstr)){
			$ct_config['config_file'] = $configstr;
		}else{
			die("LABTROVE ERROR: config file \"{$configstr}\" does not exist! (Set by \$LABTROVE_CONFIG)");
		}
	}else{
		$configstr = $ct_config['pwd']."/config.php";
		if(file_exists($configstr)){
			$ct_config['config_file'] = $configstr;
		}else{
			die("LABTROVE ERROR: config file \"{$configstr}\" does not exist! (No env \$LABTROVE_CONFIG set assumed its $configstr)");
		}
	}
	
	// sets config file if envset
	if($configstr = getenv("LABTROVE_CONFIG_DIR")){
		if(file_exists($configstr)){
			$ct_config['config_dir'] = $configstr;
		}else{
			die("LABTROVE ERROR: config directory \"{$configstr}\" does not exist! (Set by \$LABTROVE_CONFIG_DIR)");
		}
	}else{
		$configstr = $ct_config['pwd']."/config";
		if(file_exists($configstr)){
			$ct_config['config_dir'] = $configstr;
		}else{
		//	die("LABTROVE ERROR: config directory \"{$configstr}\" does not exist! (No env \$LABTROVE_CONFIG_DIR set assumed its $configstr)");
		}
	}
	
	$ct_config['enable_exhibit_view'] = 0; // set to 1 to enable

	/* Enable/disable export as png */
	$ct_config['export_png'] = false;

	$ct_config['default_config_loaded'] = true;

	include_once("{$ct_config['config_file']}");

	/* include option web config overrides - as used by some deployments */
	/*
	$webconfig = $ct_config['pwd']."/webconfig.php";
	if( file_exists($webconfig) ) { include_once($webconfig ); }
	*/

	include_once("{$ct_config['pwd']}/lib/functions.php");
	include_once("{$ct_config['pwd']}/lib/functions_blog.php");
	include_once("{$ct_config['pwd']}/lib/functions_database.php");
	include_once("{$ct_config['pwd']}/lib/functions_proc.php");

?>