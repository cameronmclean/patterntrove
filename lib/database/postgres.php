<?php

/* database functions, PostgreSQL flavoured - *** beta code, not fully tested use with caution *** */

if( !extension_loaded('pgsql') )
{
  error_log("No Postgres extension found (pgsql), review this php installation");
  echo "Database configuration issue, please contact the system administator";
  exit;
}

$ct_config['db_backend'] = 'postgres';
$ct_config['use_mysql_fulltext_search'] = 0; // force search to be simple

$database_debug_level = 0;
$ct_config['db_link'] = pg_connect("host={$ct_config['blog_host']} dbname={$ct_config['blog_db']} user={$ct_config['blog_user']} password={$ct_config['blog_pass']}")
  or die('Could not connect to postgres database: ' . pg_last_error());

/* internal interface to PostgreSQL, to connect and run SQL */
/* $oneliner is will return the $result not the array of values */
function _db_call($sql, $oneliner = true)
{
  global $ct_config, $database_debug_level;

  $result = pg_query($sql);

  if(!$result)
  {
    error_log("Database Connection Error : failed to execute query ({$sql})");
    error_log( pg_last_error() );
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

  if( preg_match('/^INSERT INTO/', $sql) ) // this is not a nice way to detect this
  {
    $insert_query = pg_query("SELECT lastval();");
    $insert_row = pg_fetch_row($insert_query);
    $_SESSION['postgres_insert_id'] = $insert_row[0];
  }

  if(is_bool($result) || !$oneliner)
  {
    return $result;
  }
  else
  {
    return pg_fetch_array($result, null, PGSQL_ASSOC);
  }

//?? have we leaked any resources?
}

// postgres wrappers

function db_get_next_row($result)
{
  return pg_fetch_array($result, null, PGSQL_BOTH); /* $result, 'next row', 'assocative array and numerical array in results' */
}

function db_get_number_of_rows($result)
{
  return pg_num_rows($result);
}

function db_escape_string($string)
{
  return pg_escape_string($string);
}

function db_insert_id()
{
  if(!isset($_SESSION['postgres_insert_id'])) { error_log("WARNING: postgres_insert_id has not been set prior to use"); }
  return $_SESSION['postgres_insert_id'];
}

// labtrove specific template routines

function db_uri_db($table)
{
  // NOTE : this implicitly means that uri_db has to be the same as blog_db under postgres
  return $table;
}

// template routines

function db_from_timestamp($time)
{
  return "to_timestamp(" . $time . ")";
}

function db_timestamp($time)
{
  return "date_part('epoch', " . $time . ")";
}

function db_month_template($month)
{
  return "EXTRACT(MONTH FROM {$month})";
}

function db_year_template($year)
{
  return "EXTRACT(YEAR FROM {$year})";
}

function db_escape_sentinel_template()
{
  return "E";
}

function db_limit_1()
{
  return "";
}

// legacy routines

function runQuery($sql, $query_desc="")
{
  // Connecting, selecting database
  global $ct_config;

  if($ct_config['devo']) // database debugging
  {
    $bt = debug_backtrace(false);
    $sql .= " -- CALLED FROM '{$bt[0]['file']}:{$bt[0]['line']}'";
  }

  $uri = $_SERVER['SERVER_NAME'];
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
  $result = pg_query($sql);
  if(!$result)
  {
    // Get the error message.
    $err_msg = mysql_error();
    $email = "andrew@bluerhinos.co.uk";
    $ret .=  "<hr />\n";
    $ret .=  "<p>There was a problem running the <b>$query_desc</b> query. Please report the message ";
    $ret .=  "below to the <a href=\"mailto:$email\">webmaster</a>, telling them when and where the problem occurred.</p>";
    $ret .=  "<pre><b>ERROR MESSAGE:</b>\n$err_msg</pre>";
    $ret .=  "<hr />\n";
    $ret .=  $sql;
    if($_REQUEST['backtrace'])
    {
      print_r(debug_backtrace());
    }
    echo $ret;
  }

  if($ct_config['devo'])
  {
    $time =  microtime(true) - $time_start;
    $ct_config['devstr']['sql'][]= array("sql"=>$sql,"time"=>$time);
  }

  if( preg_match('/^INSERT INTO/', $sql) ) // this is not a nice way to detect this
  {
    $insert_query = pg_query("SELECT lastval();");
    $insert_row = pg_fetch_row($insert_query);
    $_SESSION['postgres_insert_id'] = $insert_row[0];
  }

  return $result;
}

?>
