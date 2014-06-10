<?php

include("../lib/default_config.php");

function get_blogs_list($selected)
{
  global $ct_config;

  $result = db_get_blogs_list();
  $select = "<select name='search_blog' style='width: 555px; padding: 2px;'>\n";
  $select .= "<option value=''>All Notebooks</option>\n";
  while($rowb = db_get_next_row($result))
  {
    if(checkzone($rowb['blog_zone'], 0, $rowb['blog_id']))
    {
      if( $rowb['blog_id'] == $selected )
      { $select .= "<option value='{$rowb['blog_id']}' selected>{$rowb['blog_name']}</option>\n"; }
      else
      { $select .= "<option value='{$rowb['blog_id']}'>{$rowb['blog_name']}</option>\n"; }
    }
  }
  $select .= "</select>\n";

  return $select;
}

// get a list of users who have authored posts for blogs the current user can see
// this is more secure than simply returning a list of all users in the system
function get_users_list($selected)
{
  global $ct_config;

  $result = db_get_blogs_list();
  $snames = array();
  while($rowb = db_get_next_row($result))
  {
    if(checkzone($rowb['blog_zone'], 0, $rowb['blog_id']))
    {
      $snames[ $rowb['blog_sname'] ] = 1;
    }
  }

  $users = array();
  foreach ($snames as $key=>$value)
  {
    $users_result = db_get_blog_users_by_sname($key);
    while($user_row = db_get_next_row($users_result))
    {
      if( !isset($users[ $user_row['user'] ]) ) { $users[ $user_row['user'] ] = 0; }
      $users[ $user_row['user'] ]++;
    }
  }
  arsort($users);

  $select = "<select name='search_user'>\n";
  $select .= "<option value=''>All Authors</option>\n";
  foreach ($users as $key=>$value)
  {
    $name = get_user_info($key, "name");
    if( $name == $selected )
    { $select .= "<option value='{$name}' selected>{$name}</option>\n"; }
    else
    { $select .= "<option value='{$name}'>{$name}</option>\n"; }
  }
  $select .= "</select>\n";

  return $select;
}

function get_metas_list($selected)
{
  global $ct_config;

  $result = db_get_blogs_list();
  $snames = array();
  while($rowb = db_get_next_row($result))
  {
    if(checkzone($rowb['blog_zone'], 0, $rowb['blog_id']))
    {
      $snames[ $rowb['blog_sname'] ] = 1;
    }
  }

  $metas = array();
  foreach ($snames as $key=>$value)
  {
    $metas_result = db_get_blog_metas_by_sname($key);
    while($meta_row = db_get_next_row($metas_result))
    {
      $metadata = readxml($meta_row['meta']);
      $metadata = $metadata['METADATA']['META'];
      if(is_array($metadata))
      {
        foreach($metadata as $key=>$value)
        {
          $metas[ $key ] = 1;
        }
      }
    }
  }

  $select = "<select name='search_meta'>\n";
  $select .= "<option value=''>All Keys</option>\n";
  foreach ($metas as $key=>$value)
  {
    if( $key == $selected )
    { $select .= "<option value='{$key}' selected>{$key}</option>\n"; }
    else
    { $select .= "<option value='{$key}'>{$key}</option>\n"; }
  }
  $select .= "</select>\n";

  return $select;
}

function extract_meta($in)
{
  $metas = array();
  $metadata = readxml($in);
  $metadata = $metadata['METADATA']['META'];
  if(is_array($metadata))
  {
    foreach($metadata as $key=>$value)
    {
      $metas[ $key ] = $value;
    }
  }
  return $metas;
}

function user_matches($name)
{
  if( !isset($_REQUEST['search_user']) || $_REQUEST['search_user'] == '' ) { return true; } // unset means match all
  return ( $_REQUEST['search_user'] == $name ); 
}

function meta_matches($meta)
{
  if( !isset($_REQUEST['search_meta']) || $_REQUEST['search_meta'] == '' ) { return true; } // unset means match all
  foreach ($meta as $k=>$v)
  {
    if( $_REQUEST['search_meta'] == $k ) { return true; }
  }
  return false;
}

function date_from_matches($date)
{
  if( !isset($_REQUEST['date_from']) || $_REQUEST['date_from'] == '' ) { return true; } // unset means match all
  $d = explode('/', $_REQUEST['date_from'], 3);
  $date_from = mktime(0, 0, 0, $d[1], $d[0], $d[2]);
  return ($date >= $date_from);
}

function date_to_matches($date)
{
  if( !isset($_REQUEST['date_to']) || $_REQUEST['date_to'] == '' ) { return true; } // unset means match all
  $d = explode('/', $_REQUEST['date_to'], 3);
  $date_to = mktime(0, 0, 0, $d[1], $d[0], $d[2]);
  return ($date <= $date_to);
}

function get_sections_list($selected)
{
  global $ct_config;

  $result = db_get_sections_list();
  $select = "<select name='search_section'>\n";

  $select .= "<option value=''>All Sections</option>\n";
  while($rowb = db_get_next_row($result)) // does this a) show too many sections, b) 'leak' sections names ?
  {
    $s = $rowb['section'];
    if( $s == $selected )
    { $select .= "<option value='$s' selected>$s</option>\n"; }
    else
    { $select .= "<option value='$s'>$s</option>\n"; }
  }
  $select .= "</select>\n";

  return $select;
}

// 0 = no nat lang processing, 1 = some or all nat lang process, dependant on use_mysql_fulltext_search value
$natlang = 1; // default to use nat lang processing, ie not 'simple'
$natlang_req = (isset($_REQUEST['natlang'])) ? (int)$_REQUEST['natlang'] : 0;
if( $natlang_req == 0 )
{
  $natlang_checked = '';
}
else
{
  $natlang_checked = 'checked';
  $natlang = 0;
}

if( $natlang == 1 && $ct_config['use_mysql_fulltext_search'] == 2 )
{
  include("../lib/functions/porter_stemmer.php");
}

// advanced search options
if( isset($_REQUEST['include_comments']) )
{
  $include_comments_checked = ($_REQUEST['include_comments'] == 1) ? 'checked' : '';
}
else // default
{
  $include_comments_checked = 'checked';
}

if(isset($_REQUEST['uri']))
{
  $pathinfo = $_REQUEST['uri'];
  $pathinfo = explode("/",$pathinfo);
  $request['blog_sname'] = array_shift($pathinfo);
  while( $request[array_shift($pathinfo)] = addslashes(array_shift($pathinfo)) );
  $_REQUEST['uri'] = "search/".$_REQUEST['uri'];
}
else
{
  $_REQUEST['uri'] = "search/";
}

///Load Blog info
if( isset($request) && is_set_not_empty('blog_sname', $request) )
{
  $blog = db_get_blog_by_sname($request['blog_sname']);
  $blog_id = $blog['blog_id'];
  $title = $blog['blog_name'];
  $desc = $blog['blog_desc'];
  $title_url = render_link($blog['blog_sname']);
  checkblogconfig($blog_id);
}
else
{
  $blog_id = false;
}

if( !$blog_id && isset($request) && is_set_not_empty('blog_sname', $request) )
{
  set_http_error(404, $_REQUEST['uri']);
  exit();
}
if(!$blog_id)
{
  $title = $ct_config['blog_title'];
  $desc = $ct_config['blog_desc'];
  $title_url = $ct_config['blog_path'];
  $_REQUEST['sall']=1;
}

include("style/{$ct_config['blog_style']}/blogstyle.php");

$_SESSION['blog_id'] = $blog_id;

if(isset($_REQUEST['save']) && ($_SESSION['user_name'] == $request['user']))
{
  $sql = "SELECT * FROM  blog_users WHERE u_name = '{$_SESSION['user_name']}'";
  $result = runQuery($sql,'Blogs');

  if($_REQUEST['proflocate'])
  {
    $_REQUEST['proflocate'] = 1;
  }

  if(db_get_number_of_rows($result))
  {
    $sql = "UPDATE  blog_users SET  u_emailsub =  '".(int)$_REQUEST['emailset']."', u_sortsub =  '".(int)$_REQUEST['emailsort']."', u_proflocate =  '".(int)$_REQUEST['proflocate']."' WHERE blog_users.u_name =  '{$_SESSION['user_name']}' " . db_limit_1();
  }
  else
  {
    $sql = "INSERT INTO  blog_users ( u_name , u_emailsub , u_sortsub , u_proflocate ) VALUES ( '{$_SESSION['user_name']}',  '".(int)$_REQUEST['emailset']."',  '".(int)$_REQUEST['emailsort']."',  '".(int)$_REQUEST['proflocate']."');";
  }

  runQuery($sql,'Blogs');

  $sql = "DELETE FROM  blog_sub WHERE  blog_sub.sub_username =  '{$_SESSION['user_name']}'";
  runQuery($sql,'Blogs');

  if(isset($_REQUEST['blogs_sub']))
  {
    foreach($_REQUEST['blogs_sub'] as $key => $value)
    {
      $sql = "INSERT INTO  blog_sub ( sub_username , sub_blog ) VALUES ( '{$_SESSION['user_name']}',  '".(int)$key."' );";
      runQuery($sql,'Blogs');
    }
  }
}

$jquery['ui'] = true;

$body = "";

$head = <<<HEAD
  <style>
    .search_section
    {
      border: 1px solid lightgrey;
      padding: 10px;
      margin: 10px;
      width: 715px;
    }
    .search_result
    {
      margin-top:4px;
      padding-bottom: 10px;
    }
  </style>
HEAD;

if(!checkzone($ct_config['blog_zone']) )
{
  header("Location: {$ct_config['blog_path']}projects/blog/index.php?msg=Forbidden!");
  exit();
}

if( is_set_not_empty('msg', $_REQUEST) )
{
  $body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: {$_REQUEST['msg']} </div></div>";
}

$encoded_query_string = htmlentities( stripslashes( isset($_REQUEST['q']) ? $_REQUEST['q'] : '' ), ENT_QUOTES );
$select = get_blogs_list( isset($_REQUEST['search_blog']) ? $_REQUEST['search_blog'] : '' );

// Advanced search options
$section_select = get_sections_list( isset($_REQUEST['search_section']) ? $_REQUEST['search_section'] : '' );
$user_select = get_users_list( isset($_REQUEST['search_user']) ? $_REQUEST['search_user'] : '' );
$meta_select = get_metas_list( isset($_REQUEST['search_meta']) ? $_REQUEST['search_meta'] : '' );
$date_from_str = isset($_REQUEST['date_from']) ? $_REQUEST['date_from'] : '';
$date_to_str = isset($_REQUEST['date_to']) ? $_REQUEST['date_to'] : '';
$adv = is_set_not_empty('adv', $_REQUEST) ? $_REQUEST['adv'] : '0';
$tips = is_set_not_empty('tips', $_REQUEST) ? $_REQUEST['tips'] : '0';

if( $ct_config['use_mysql_fulltext_search'] > 0)
{
  $use_simple = "<label title='Tick when you want to search for an exact word, term or phrase.' for='natlang'>Use simple text search</label><input id='natlang' type='checkbox' name='natlang' $natlang_checked value='1'>";
}
else
{
  $use_simple = '';
}

$body .= <<<BODY
  <div class="containerPost">
    <div class="postTitle">Search</div>
    <form name="searchform" method="GET" action="{$_REQUEST['uri']}">
    <div class="search_section">
      <table>
        <tr>
          <td style="text-align: right; width: 65px; vertical-align: top; padding-top: 4px;">Search for</td>
          <td><input type="text" name="q" value="{$encoded_query_string}" style="width: 550px;"></td>
        </tr>
        <tr>
          <td style="text-align: right;">in</td>
          <td>$select</td>
        </tr>
        <tr>
          <td></td>
          <td><input type=submit name=search value="Search"></td>
        </tr>
        <tr>
          <td></td>
          <td>$use_simple <a id='advanced_search_open'>More Options</a> | <a id='search_tips_open'>Show Tips</a></td>
        </tr>
      </table>
    </div> <!-- normal search -->

    <div style='display: none; text-align: left;' class='search_section' id='advanced_search'>
        <b>Advanced Search Controls</b><br><br>
        <table>
          <tr><td colspan='2'>Include comments in the results
            <input type='hidden' name='adv' value='$adv'>
            <input type='hidden' name='tips' value='$tips'>
            <input type='hidden' name='include_comments' value='0'>
            <input type='checkbox' name='include_comments' $include_comments_checked value='1'></td></tr>
          <tr><td>Restrict to a section</td><td>$section_select</td></tr>
          <tr><td>Restrict to an author</td><td>$user_select</td></tr>
          <tr><td>Restrict to a metadata key</td><td>$meta_select</td></tr>
          <tr><td>Restrict to dates after</td><td><input type="text" id="date_from" name="date_from" size="10" value="$date_from_str"> (DD/MM/YYYY)</td></tr>
          <tr><td>Restrict to dates before</td><td><input type="text" id="date_to" name="date_to" size="10" value="$date_to_str"> (DD/MM/YYYY)</td></tr>
        </table>
    </div> <!-- advanced search -->
    <div style='display: none; text-align: left;' class='search_section' id='search_tips'>
      <b>Search Tips</b><br><br>
      <div class="search_result">To have your search match all available posts, ensure 'Use simple text search' is ticked and the 'Search for' box is empty.</div>
      <div class="search_result">Full text search typically only operates on terms 4 characters or longer, if your search contains short words and is not returning what you are looking for, try checking 'Use simple text search' and search again.</div>
    </div> <!-- search tips -->

      <script>
  jQuery("#advanced_search_open").click(function ()
  {
    if(jQuery("#advanced_search").is(":hidden"))
    {
      jQuery("#advanced_search").slideDown("slow");
      jQuery("#advanced_search_open").html("Fewer Options");
      document.searchform.adv.value = '1';
    }
    else
    {
      jQuery("#advanced_search").slideUp("slow");
      jQuery("#advanced_search_open").html("More Options");
      document.searchform.adv.value = '0';
    }
  });

  if(document.searchform.adv.value == '1')
  {
    jQuery("#advanced_search").show();
    jQuery("#advanced_search_open").html("Fewer Options");
  }

  jQuery("#search_tips_open").click(function ()
  {
    if(jQuery("#search_tips").is(":hidden"))
    {
      jQuery("#search_tips").slideDown("slow");
      jQuery("#search_tips_open").html("Hide Tips");
      document.searchform.tips.value = '1';
    }
    else
    {
      jQuery("#search_tips").slideUp("slow");
      jQuery("#search_tips_open").html("Show Tips");
      document.searchform.tips.value = '0';
    }
  });

  if(document.searchform.tips.value == '1')
  {
    jQuery("#search_tips").show();
    jQuery("#search_tips_open").html("Hide Tips");
  }

  jQuery(function()
  {
    var dates = $( "#date_from, #date_to" ).datepicker({
	// defaultDate: "+1w",
	changeMonth: true,
	numberOfMonths: 1,
        dateFormat: 'dd/mm/yy',
	onSelect: function( selectedDate ) {
		var option = this.id == "date_from" ? "minDate" : "maxDate",
		instance = $( this ).data( "datepicker" ),
			date = $.datepicker.parseDate(
				instance.settings.dateFormat ||
				$.datepicker._defaults.dateFormat,
				selectedDate, instance.settings );
		dates.not( this ).datepicker( "option", option, date );
	}
    });
  });
    </script>
  </form>
BODY;

if(isset($_REQUEST['q']))
{
  // Build SQL template variables

  $result_index = 0;
  $result_fragments[$result_index] = "";
  $result_scores[$result_index] = 0;

  $search_matcher = "bit_title LIKE  '%{$_REQUEST['q']}%' OR bit_content LIKE  '%{$_REQUEST['q']}%'";
  $search_matcher_as_score = '';
  $search_matcher_comment = "com_title LIKE  '%{$_REQUEST['q']}%' OR com_cont LIKE  '%{$_REQUEST['q']}%'";
  $search_matcher_as_score_comment = '';
  $search_order_by = " ORDER BY  bit_datestamp DESC ";

  if( $natlang > 0 && $ct_config['use_mysql_fulltext_search'] == 1 )
  {
    $search_matcher = "MATCH (bit_content, bit_title) AGAINST ('" . $_REQUEST['q'] . "' IN BOOLEAN MODE)";
    $search_matcher_as_score = ", " . $search_matcher . " AS score";
    $search_matcher_comment = "MATCH (com_cont, com_title) AGAINST ('" . $_REQUEST['q'] . "' IN BOOLEAN MODE)";
    $search_matcher_as_score_comment = ", " . $search_matcher_comment . " AS score";
    $search_order_by = " ORDER BY score DESC";
  }
  elseif( $natlang > 0 && $ct_config['use_mysql_fulltext_search'] == 2 )
  {
    $search_matcher = "MATCH (bit_content, bit_title) AGAINST ('" . porter_stemmer_prime_search($_REQUEST['q']) . "' IN BOOLEAN MODE)";
    $search_matcher_as_score = ", " . $search_matcher . " AS score";
    $search_matcher_comment = "MATCH (com_cont, com_title) AGAINST ('" . porter_stemmer_prime_search($_REQUEST['q']) . "' IN BOOLEAN MODE)";
    $search_matcher_as_score_comment = ", " . $search_matcher_comment . " AS score";
    $search_order_by = " ORDER BY score DESC";
  }

  // Search Posts

  $ts = db_timestamp("bit_datestamp");
  $sql = <<<SQL
SELECT bit_id, bit_rid, bit_user, bit_title, $ts AS datetime, bit_blog, blog_id, blog_name, blog_sname, bit_user, blog_zone, bit_meta $search_matcher_as_score
FROM blog_bits INNER JOIN blog_blogs ON blog_bits.bit_blog = blog_blogs.blog_id
WHERE bit_edit = 0 AND ({$search_matcher})
SQL;

  if(is_set_not_empty('search_blog', $_REQUEST))
  {
    $sql .= " AND blog_blogs.blog_id = " . (int)$_REQUEST['search_blog'];
  }

  if(is_set_not_empty('search_section', $_REQUEST))
  {
    $sql .= " AND blog_bits.bit_group = '" . $_REQUEST['search_section'] . "'";
  }

error_log($sql);
//echo $sql;

  $sql .= $search_order_by;
  $result = runQuery($sql,'Blogs');

  $blog_name = '';
  if(db_get_number_of_rows($result))
  {
    while($rowb = db_get_next_row($result))
    {
      $name = get_user_info($rowb['bit_user'], "name");
      $meta = extract_meta($rowb['bit_meta']);
      if( checkzone($rowb['blog_zone'],1,$rowb['blog_id']) != 0 && user_matches($name) && meta_matches($meta) && date_from_matches($rowb['datetime']) && date_to_matches($rowb['datetime']) )
      {
        if($blog_name == '')
        {
          $blog_name = $rowb['blog_sname'];
        }
        $search_results_blog_ids[ $rowb['blog_id'] ] = $rowb['blog_id'];
        $search_results_post_ids[ $rowb['bit_id'] ] = $rowb['bit_id'];
        $search_results_ids[ $rowb['blog_id'] ][ $rowb['bit_id'] ] = $rowb['bit_id'];
        $fragment = "\t\t\t<div class='search_result'>".render_blog_link($rowb['bit_id'])." by ".$name;
        if($_REQUEST['sall'])
        {
          $fragment .= " from ".$rowb['blog_name'];
        }
        $fragment .= "<br />\t\t\t<span class=\"timestampComment\">".date("jS F Y @ H:i",$rowb['datetime'])."</span></div>\n";

        $result_fragments[$result_index] = $fragment;
        $result_scores[$result_index] = isset($rowb['score']) ? $rowb['score'] : 0;
        $result_index++;
      }
    } // end while
  }

  // Search Comments
  if( $include_comments_checked == 'checked' )
  {
    $ts = db_timestamp( "blog_com.com_datetime" );
    $sql = <<<SQL
SELECT blog_name,blog_id, blog_com.com_id as uid,  blog_bits.bit_id ,  blog_com.com_user AS  bit_user ,  blog_com.com_title AS  bit_title ,  blog_com.com_cont AS  bit_content , $ts AS datetime , 'comment' AS btype , blog_com.com_edit, blog_blogs.blog_zone $search_matcher_as_score_comment
FROM blog_bits INNER JOIN  blog_com ON  blog_bits.bit_id =  blog_com.com_bit INNER JOIN  blog_blogs ON  blog_bits.bit_blog =  blog_blogs.blog_id
WHERE blog_com.com_edit = 0 AND  bit_edit = 0 AND ({$search_matcher_comment})
SQL;

    if(is_set_not_empty('search_blog', $_REQUEST))
    {
      $sql .= " AND blog_blogs.blog_id = " . (int)$_REQUEST['search_blog'];
    }

    if(is_set_not_empty('search_section', $_REQUEST))
    {
      $sql .= " AND blog_bits.bit_group = '" . $_REQUEST['search_section'] . "'";
    }

    $sql .= $search_order_by;
    $result = runQuery($sql,'Blogs');

    if(db_get_number_of_rows($result))
    {
      while($rowb = db_get_next_row($result))
      {
        $name = get_user_info($rowb['bit_user'], "name");
        if( checkzone($rowb['blog_zone'],1,$rowb['blog_id']) != 0 && user_matches($name) && date_from_matches($rowb['datetime']) && date_to_matches($rowb['datetime']) ) // no metadata filter on comments
        {
          $fragment = "\t\t\t<div class='search_result' style='font-style: italic;'><a href=\"".render_blog_link($rowb['bit_id'],1)."#{$rowb['uid']}\">".$rowb['bit_title']."</a> by ".$name;
          if($_REQUEST['sall'])
          {
            $fragment .= " from ".$rowb['blog_name'];
          }
          $fragment .= "</i><br />\t\t\t<span class=\"timestampComment\">".date("jS F Y @ H:i",$rowb['datetime'])."</span></div>\n";
          $result_fragments[$result_index] = $fragment;
          $result_scores[$result_index] = isset($rowb['score']) ? $rowb['score'] : 0;
          $result_index++;
        }
      } // end while
    }
  }

  // Render results

  if( $include_comments_checked == 'checked' )
  {
    $body .= "\t\t<div class=\"postTitle\">Results - Posts and <i>Comments</i></div>\n";
  }
  else
  {
    $body .= "\t\t<div class=\"postTitle\">Results - Posts</div>\n";
  }
  $body .= "<div class='search_section'>";

  if($result_fragments[0])
  {
    // arsort($result_scores); // this doesn't help with boolean scores

    // $body .= "<ul>\n";
    foreach ($result_scores as $index => $score)
    {
      // $body .= "($score)";
      $body .= $result_fragments[$index];
    }
    // $body .= "</ul>\n";
  }
  else
  {
    if( $natlang == 1 && $ct_config['use_mysql_fulltext_search'] == 2 )
    {
      $body .= "<i>No results found<br>Not found what you are looking for, try a simple text search.</i>\n";
    }
    else
    {
      $body .= "<i>No results found</i>\n";
    }
  }

  $body .= "</div>";

  if($result_fragments[0] && isset($search_results_post_ids) && sizeof($search_results_post_ids) )
  {
    $search_results_ids_str = '';
    foreach ($search_results_ids as $b => $ids)
    {
      if($search_results_ids_str == '')
      { $search_results_ids_str .= $b . '-' . implode('.', $ids); }
      else
      { $search_results_ids_str .= ':' . $b . '-' . implode('.', $ids); }
    }

	
	$export_search_html = "<form action='export.php' method='POST'>
		<input type='hidden' name='bom' value='".$search_results_ids_str."'/>
		<input type='hidden' name='depth' value='1'/>
		<input type='hidden' name='go' value='start+export'/>
		<input type='submit' value='Export the posts described by this search'/>
		</form>";
	
    $body .= "<div class='search_section'>$export_search_html</div>";
  }

  $body .="</div>\n";
}

include('page.php');
?>
