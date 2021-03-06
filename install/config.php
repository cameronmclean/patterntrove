<?php

	if(!isset($nosession) || !$nosession){
	$sess_time = 480;
	session_start();
	ini_set('session.gc_maxlifetime',$sess_time*60);
	}
	
	/* Used in setting titles on pages, rss feeds etc*/
	$ct_config['blog_title'] = "LabTrove";
	$ct_config['blog_desc'] = "An out of the box instance of LabTrove";
	
	/* Sets the default style the labtrove instance */
	/* See wiki for 'how to' to customise */
	$ct_config['blog_style'] = "default";
	
	/* Setting the defaults for the url of the blog */
	$ct_config['this_server'] = "example.com";
	$ct_config['blog_path'] = "/";
	$ct_config['blog_protocol'] = "http"; /* Change to https if over https*/
	$ct_config['blog_url'] = "{$ct_config['blog_protocol']}://{$ct_config['this_server']}{$ct_config['blog_path']}";

	/* Set for path for the site title (this is the title top left, it may be changed to the institution website) */
	$ct_config['blog_site_url'] = $ct_config['blog_path'];

	/* Enter the usernames of the webmaster and contact */
	$ct_config['blog_webmaster'] = '';
	$ct_config['blog_contact'] = '';

	/* database connections*/
	$ct_config['blog_host'] = "localhost";
	$ct_config['blog_user'] = "labtrove";
	$ct_config['blog_pass'] = "labtrove";
	$ct_config['blog_db'] = "labtrove";

	/* Security for whole blog, 0 = public, 1 = Logged in users only */
	/* other zones are possible but the need to set up */
	$ct_config['blog_zone'] = 0;
 
	/* number of post/page */
	$ct_config['no_blogs_page'] = 5;
	
	$ct_config['uri_server'] = $ct_config['this_server'];
	$ct_config['uri_db'] = $ct_config['blog_db'] ;
	
	/* any plugins */
	/* you change login_openid to login_ldap or login_local */
	$ct_config['plugins'] = array('login_openid', 'uri_samedb'); 
	
	/* if you are using openid what can a new user do */
	$ct_config['openid']['default_user_type'] = 1;

	/* location of cache dir */
	$ct_config['cache_dir'] = "cache"; 

	/* Email settings, Comming soon */
	$ct_config['blog_enmsg'] = false;
	$ct_config['blog_msgdb'] = $ct_config['uri_db'];
	
	
	/* if dont have these installed then the blog wont try and autoconvert items */
	$ct_config['autoconv']['ffmpeg2theora'] = 1;
	$ct_config['autoconv']['ffmpeg'] = 1;
	$ct_config['autoconv']['convert'] = 1;

	/* Path and filename of OSCAR JAR file and Chemspider API security token for ChemSpider plugin */
        $ct_config['chemspider']['oscar_path'] = "/usr/local/bin";
        $ct_config['chemspider']['oscar_jar'] = "oscar4-all-4.1.2-with-dependencies.jar";
        $ct_config['chemspider']['oscar_fq_jar'] = "{$ct_config['chemspider']['oscar_path']}/{$ct_config['chemspider']['oscar_jar']}";
	$ct_config['chemspider']['security_token'] = "CHANGEME";

	/* 	optional settings */
	
	/* Enable export as png 
		Your instance will need to have connectivity to http://screen.labtrove.org/ and 
		this will send your data (privately) to our servers. */
	/* $ct_config['export_png'] = false; */

?>
