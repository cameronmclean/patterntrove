<?php
/*
	Update Task for updater.
					v= Version no
	$update_tasks[482] = array(array("type"=>"sql", "sql"=>"SELECT 1;", "desc"=>"Selecting 1"));
	$update_tasks[494] = array(array("type"=>"cmd", "cmd"=>"cd lib/scripts; php htaccess.php", "desc"=>"Update Htaccess"));
	
	The commands should also be repetitive with no ill effect

*/



	
	$update_tasks[494] = array(array("type"=>"cmd", "cmd"=>"cd lib/scripts; php htaccess.php", "desc"=>"Update Htaccess"));
	$update_tasks[576] = array(array("type"=>"sql", "sql"=>"UPDATE `blog_types` SET `type_name` = 'Notebooks' WHERE `blog_types`.`type_id` = 1 AND `type_name` = 'Blogs';", "desc"=>"Rename to Notebooks"));


?>