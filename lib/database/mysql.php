<?php

/* database functions, currently MySQL flavoured */

$database_debug_level = 0;
$ct_config['db_link'] = mysql_pconnect($ct_config['blog_host'],$ct_config['blog_user'],$ct_config['blog_pass']);
mysql_select_db($ct_config['blog_db']) or die("Could not select database");

$ct_config['db_backend'] = 'mysql';

/* internal interface to MySQL, to connect and run SQL */
/* $oneliner is will return mysql $result not the array of values */
function _db_call($sql, $oneliner = true)
{
  global $ct_config, $database_debug_level;

  $result = mysql_query($sql);

  if(!$result)
  {
    error_log("Database Connection Error : failed to execute query ({$sql})");
    error_log(mysql_error());
    return false;
  }

  if($database_debug_level > 0)
  {
    /* log the query and where in the client code this was called from */
    $bt = debug_backtrace(false);
    $n = 1;
    if(!$bt[1]) { $n = 0; }
    error_log("DATABASE CALL '{$bt[$n]['file']}:{$bt[$n]['line']}' : '{$sql}'");
  }

  if(is_bool($result) || !$oneliner)
  {
    return $result;
  }
  else
  {
    return mysql_fetch_array($result, MYSQL_ASSOC);
  }
}

// mysql wrappers

function db_affected_rows(){
	return mysql_affected_rows();
}

function db_get_next_row($result)
{
  return mysql_fetch_array($result);
}

function db_get_number_of_rows($result)
{
  return mysql_num_rows($result);
}

function db_escape_string($string)
{
  return mysql_real_escape_string($string);
}

function db_insert_id()
{
  return mysql_insert_id();
}

// labtrove specific template routines

function db_uri_db($table)
{
  global $ct_config;
  return $ct_config['uri_db'] . "." . $table;
}

// template routines

function db_from_timestamp($time)
{
  return "FROM_UNIXTIME(" . $time . ")";
}

function db_timestamp($time)
{
  return "UNIX_TIMESTAMP(" . $time . ")";
}

function db_month_template($month)
{
  return "month({$month})";
}

function db_year_template($year)
{
  return "year({$year})";
}

function db_escape_sentinel_template()
{
  return "";
}

function db_limit_1()
{
  return "LIMIT 1";
}

// legacy routines

function runQuery($sql, $query_desc="")
{
  // Connecting, selecting database
  global $ct_config;

  if($ct_config['devo']) // database debugging, enable general_log_file in /etc/mysql/my.cnf and the annotated SQL will be logged by MySQL
  {
    $bt = debug_backtrace(false);
    $sql .= " -- CALLED FROM '{$bt[0]['file']}:{$bt[0]['line']}'";
  }

  //$uri = $_SERVER['SERVER_NAME'];
  if(! $ct_config['db_link'])
  {
    echo "DB Connection Error!";
    exit(); // dangerous as exits entire php stack
  }

  if($ct_config['devo'])
  {
    $time_start = microtime(true);
  }

  if(!$ct_config['db_link'])
  {
    return false;
  }
  // else $ret .=  "An error occurred while attempting to connect to the database.";

  // Run the query.
  $result = mysql_query($sql);
  if(!$result)
  {
    // Get the error message.
    $err_msg = mysql_error();
    $email = "support@labtrove";
    $ret .=  "<hr />\n";
    $ret .=  "<p>There was a problem running the <b>$query_desc</b> query. Please report the message ";
    $ret .=  "below to the <a href=\"mailto:$email\">webmaster</a>, telling them when and where the problem occurred.</p>";
    $ret .=  "<pre><b>ERROR MESSAGE:</b>\n$err_msg</pre>";
    $ret .=  "<hr />\n";
    $ret .=  $sql;
  	$ret .=  "<hr />\n";
	
    if($_REQUEST['backtrace'])
    {
      	echo "<pre>";
		print_r(debug_backtrace());
	   	echo "</pre>";
    }
    echo $ret;
  }

  if($ct_config['devo'])
  {
    $time =  microtime(true) - $time_start;
    $ct_config['devstr']['sql'][]= array("sql"=>$sql,"time"=>$time);
  }

  return $result;
}

?>