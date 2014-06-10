<?php

// data access

function db_get_blog_by_id($blog_id)
{
  global $ct_config;
  $blog_id = (int)$blog_id;
  return _db_call("SELECT * FROM  blog_blogs WHERE blog_id = '{$blog_id}'");
}

function db_get_blog_metadata_by_bit($bit_id)
{
  global $ct_config;
  $bit_id = (int)$bit_id;
  return _db_call("SELECT bit_meta, bit_user FROM blog_bits WHERE bit_id = {$bit_id} AND bit_edit IN (-1,0) ORDER BY bit_edit ASC");
}

function db_get_blog_link_info($bit_id)
{
  global $ct_config;
  return _db_call("SELECT blog_bits.bit_title, blog_blogs.blog_sname FROM blog_bits INNER JOIN blog_blogs ON blog_bits.bit_blog = blog_blogs.blog_id WHERE bit_id = $bit_id AND bit_edit < 1");
}

function db_get_post_by_id($post_id, $mode = 0)
{
  global $ct_config;

  $post_id = (int)$post_id;

  //eg get draft if -1;
  if($mode=='edit')
  {
    $modesql = "bit_edit in (0,-1) ORDER BY bit_edit ASC ";
  }
  elseif($mode)
  {
    $modesql = "bit_edit = ".(int)$mode;
  }
  else
  {
    $modesql = "bit_edit = 0";
  }

  $sql = "SELECT  bit_id, bit_rid,  bit_user ,  bit_title ,  bit_content ,  bit_meta ,  bit_datestamp ,  bit_timestamp ,  bit_group ,  bit_blog ,  bit_edit ,  bit_editwhy , ".db_timestamp( "bit_datestamp" )." AS datetime ,  ".db_timestamp( "bit_timestamp" )." AS timestamp
    FROM  blog_bits
    WHERE bit_id = '{$post_id}' AND {$modesql} " . db_limit_1();

  return _db_call($sql);
}

function db_add_data_to_database_by_value($data_type, $size, &$data)
{
  global $ct_config;

  $sql = sprintf("INSERT INTO %s.blog_data (data_id, data_datetime, data_type , data_data, filesize, mode) VALUES ('', NOW(),  '%s',  '%s', '%s', 'database')",
    $ct_config['blog_db'],
    db_escape_string($data_type),
    db_escape_string($data),
    db_escape_string($size)
  );

  _db_call($sql);

  return db_insert_id();
}

function db_get_data_by_id($data_id)
{
  global $ct_config;
  $data_id = (int)$data_id;
  $row = _db_call("SELECT * FROM blog_data WHERE data_id = '".$data_id."'");
	if($row['filepath'] && substr($row['filepath'],0,1)!="/")
		$row['filepath'] = $ct_config['uploads_dir']."/".$row['filepath'];
	return $row;
}

function db_get_sections_list()
{
  global $ct_config;
  return _db_call("SELECT DISTINCT bit_group AS section FROM blog_bits WHERE bit_group != ''", false);
}

function db_get_blogs_list()
{
  global $ct_config;
  return _db_call("SELECT * FROM blog_blogs WHERE blog_type = 1 AND blog_del != 1", false);
}

function db_get_blog_by_sname($blog_sname)
{
  global $ct_config;
  return _db_call("SELECT * FROM  blog_blogs WHERE blog_sname = '" . db_escape_string($blog_sname) . "'");
}

function db_get_blog_users_by_sname($blog_sname)
{
  global $ct_config;
  return _db_call("SELECT DISTINCT bit_user AS user FROM blog_bits, blog_blogs WHERE bit_blog = blog_id AND blog_sname = '" . db_escape_string($blog_sname) . "'", false);
}

function db_get_blog_metas_by_sname($blog_sname)
{
  global $ct_config;
  return _db_call("SELECT DISTINCT bit_meta AS meta FROM blog_bits, blog_blogs WHERE bit_meta LIKE '%<META>%</META>%' AND bit_blog = blog_id AND bit_edit = 0 AND blog_sname = '" . db_escape_string($blog_sname) . "'", false);
}

function db_add_data_to_database_by_reference($data_type, $size, $checksum, $filepath)
{
  global $ct_config;

  $sql = sprintf("INSERT INTO %s.blog_data (data_id, data_datetime, data_type ,filepath, filesize, checksum, mode) VALUES ('', NOW(), '%s',  '%s', '%s', '%s', 'filesystem')",
    $ct_config['blog_db'],
    db_escape_string($data_type),
    db_escape_string($filepath),
    db_escape_string($size),
    db_escape_string($checksum));
  _db_call($sql);

  return db_insert_id();
}

// user access

function db_get_user_by_uid($uid, $user_enabled = NULL)
{
  global $ct_config;
  if(isset($user_enabled))
  {
    return _db_call("SELECT * FROM users WHERE user_uid = '" . db_escape_string($uid) . "' AND user_enabled = " . db_escape_string($user_enabled) . " " . db_limit_1());
  }
  else
  {
    return _db_call("SELECT * FROM users WHERE user_uid = '" . db_escape_string($uid) . "' " . db_limit_1());
  }
}

function db_get_user_by_id($id, $user_enabled = NULL)
{
  global $ct_config;
  if(isset($user_enabled))
  {
    return _db_call("SELECT * FROM users WHERE user_id = '" . db_escape_string($id) . "' AND user_enabled = " . db_escape_string($user_enabled) . " " . db_limit_1());
  }
  else
  {
    return _db_call("SELECT * FROM users WHERE user_id = '" . db_escape_string($id) . "' " . db_limit_1());
  }
}

function db_get_user($user, $user_enabled = NULL)
{
  global $ct_config;
  if(isset($user_enabled))
  {
    return _db_call("SELECT * FROM users WHERE user_name = '" . db_escape_string($user) . "' AND user_enabled = " . db_escape_string($user_enabled) . " " . db_limit_1());
  }
  else
  {
    return _db_call("SELECT * FROM users WHERE user_name = '" . db_escape_string($user) . "' " . db_limit_1());
  }
}

function db_get_users()
{
  global $ct_config;
  return _db_call("SELECT user_id,user_name, user_fname, user_email,user_type FROM users ORDER BY user_name", false);
}

// utility

function real_db_escape(&$var)
{
  if(is_array($var))
  {
    foreach($var as $k=>$v)
    {
      if(is_array($v))
      {
        real_db_escape($var[$k]);
      }
      else
      {
        $var[$k] = addslashes($v);
      }
    }
  }
  else
  {
    $var = addslashes($var);
  }
}

function real_db_unescape(&$var)
{
  if(is_array($var))
  {
    foreach($var as $k=>$v)
    {
      if(is_array($v))
      {
        real_db_unescape($var[$k]);
      }
      else
      {
        $var[$k] = stripslashes($v);
      }
    }
  }
  else
  {
    $var = stripslashes($var);
  }
}

?>
