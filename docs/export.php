<?php

include("../lib/default_config.php");

$sql = "SELECT * FROM  blog_blogs WHERE blog_sname = '{$_REQUEST['blog']}'";
$result = runQuery($sql,'Blogs');
$blog = db_get_next_row($result);

if(!checkzone($blog['blog_zone'],0,$blog['blog_id']) || !checkzone($ct_config['blog_zone'])){
	newMsg("Forbidden: You are not allowed to access this blog!", "error");
	$_SESSION['labtrove']['turl'] = $ct_config['blog_path'].$_SERVER['LABTROVE_REQUEST_PATH'];
	header("Location: {$ct_config['blog_path']}");
	exit();
}

checkblogconfig($blog['blog_id']);

include("style/{$ct_config['blog_style']}/blogstyle.php");

if(!isset($head)) { $head = ''; }
if(!isset($body)) { $body = ''; }

$blogpost = NULL;
$blogpost['post'] = '';

$title = $blog['blog_name'];
$desc = $blog['blog_desc'];
$title_url = render_link($blog['blog_sname']);

$blogpost['title'] = "Export Notebook";

if(!isset($_REQUEST['saveblog']) || !$_REQUEST['saveblog'])
{
  foreach(array("blog_name","blog_desc","blog_type") as $val)
  {
    $_REQUEST[$val] = $blog[$val];
  }
}

$datapath = "{$ct_config['pwd']}/docs/cache/blogdumps";
if(isset($_REQUEST['clear']))
{
  $_SESSION['export_key'][$_REQUEST['blog']] = NULL;
  header("Location: ".render_link("export.php?blog={$_REQUEST['blog']}"));
  exit();
}

if(isset($_REQUEST['go']))
{
  $key = tempdir($datapath);
  $_SESSION['export_key'][$_REQUEST['blog']] = $key;
  $index = preg_replace('/[^0-9,-]/', '', $_REQUEST['index_post']);  
  $depth = (int)$_REQUEST['depth'];
  if(isset($_REQUEST['bom']))  // bill of materials
  {
    $bom = preg_replace('/[0-9:.-]/', '', $_REQUEST['bom']); // can this be contrived to describe and access posts beyond the user's viewable set?
    $exec = "php {$ct_config['pwd']}/lib/scripts/exporthtml.php bom ".escapeshellcmd($_REQUEST['bom'])." {$depth} $key > /dev/null 2>&1 &";
  }
  else
  {
    $exec = "php {$ct_config['pwd']}/lib/scripts/exporthtml.php \"{$blog['blog_id']}\" \"{$index}\" {$depth} $key > /dev/null 2>&1 &";
  }
 echo `$exec`;
  sleep(1);
}

if($_SESSION['export_key'][$_REQUEST['blog']])
{
  $blogpost['title'] = "Export Notebook Processing";

  $jquery['code'] .= "\n
    setTimeout(function(){ updatestatus() }, 5000);

    function updatestatus()
    {
      $('#statustxt').load('".$ct_config['blog_path']."cache/blogdumps/".$_SESSION['export_key'][$_REQUEST['blog']]."/status?'+Math.random());
      setTimeout(function(){ updatestatus() }, 5000);
    }
  ";

  $blogpost['post'] .= "<b>Started:</b> <span>".date("r", @file_get_contents("{$datapath}/".$_SESSION['export_key'][$_REQUEST['blog']]."/created"))."</span>";
  $blogpost['post'] .= "<br/><b>Status:</b> <span id=\"statustxt\">".@file_get_contents("{$datapath}/".$_SESSION['export_key'][$_REQUEST['blog']]."/status")."</span>";
  $blogpost['post'] .= "<br/><a href=\"export.php?blog={$blog['blog_sname']}&clear=1\">Start Again</a>";

  $body .= blog_style_post($blogpost);
}
else
{
	$blogpost['post'] .= <<<POST
<table>
<form method="POST">
  <tr><td colspan=2>To export the notebook as a HTML dump please select from the following options</td></tr>
  <tr>
    <th width=100>Index Post</th>
    <td><select name="index_post" style="width: 500px">
      <option value="-1">All Posts</option>";
POST;

  $sql = "SELECT bit_id, bit_title FROM  blog_bits WHERE  bit_blog ='{$blog['blog_id']}' AND bit_edit = 0 ORDER BY  bit_datestamp DESC ";
  $result = runQuery($sql,'Blogs');

  while($bits = db_get_next_row($result))
  {
    $blogpost['post'] .= "      <option title=\"{$bits['bit_title']}\" value=\"{$bits['bit_id']}\">{$bits['bit_title']}</option>";
  }

  $blogpost['post'] .= <<<POST
    </select></td>
  </tr>
  <tr>
    <th width=100>Follow Depth</th>
    <td><select name="depth">
POST;

  for($i=1; $i<10; $i++)
  {
    $blogpost['post'] .= "      <option value=\"{$i}\">{$i}</option>\n";
  }

  $blogpost['post'] .= <<<POST
    </select></td>
  </tr>
  <tr><td></td><td><input type="submit" name="go" value="Start Export"/></td></tr>
  </form>
</table>
POST;

  $body .= blog_style_post($blogpost);
}

include('page.php');

?>
