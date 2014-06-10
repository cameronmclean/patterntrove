<?php

/*
	Contains all the code that runs after all the functions have been loaded.
	Must be loaded last by default_config.php
*/





//
//prep_get();

// Labtrove requires magic quotes for the moment.
// Will skip magic quotes if $ct_config['skip_magic_quotes']
if (!get_magic_quotes_gpc()) {
	if(!isset($ct_config['skip_magic_quotes']) || !$ct_config['skip_magic_quotes']){
    	real_db_escape($_GET);
    	real_db_escape($_POST);
    	real_db_escape($_COOKIE);
    	real_db_escape($_REQUEST);
    }
	// does not include $_FILES
}else{
	if(isset($ct_config['skip_magic_quotes']) && $ct_config['skip_magic_quotes']){
    	real_db_unescape($_GET);
    	real_db_unescape($_POST);
    	real_db_unescape($_COOKIE);
    	real_db_unescape($_REQUEST);
    }
}

//Check if you have selected remeber me
if( !is_set_not_empty('user_name', $_SESSION) )
	check_remembered();

?>