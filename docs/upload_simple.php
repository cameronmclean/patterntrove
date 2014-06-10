<?php
include("../lib/default_config.php");

$minipage = 2;
$upload = 'upload_simple.php';
$error = 'unset';
$title = 'File Upload'; // page title
if(!isset($head)) { $head = ''; }

$blog = db_get_blog_by_id($_REQUEST['blog_id']);

if($blog)
{
  $blog_id = $blog['blog_id'];
  checkblogconfig($blog_id);

  if(isset($_REQUEST['utypesub']))
  {

	$error = "none";
    $cancelled = 0;
    if($_REQUEST['origin']=='file' || $_REQUEST['origin']=='zip')
    {
	
	 if($_SERVER['CONTENT_TYPE'] == 'application/octet-stream'){
      // move the upload into place
      $in = fopen("php://input", "r");
      $temp_out = tempnam("/tmp", "upload-");
      $out = fopen($temp_out, "w");
      $size = 0;
      while (!feof($in))
      {
        $size += fwrite($out, fread($in, 8192));
      }
	
      if(isset($_SERVER["CONTENT_LENGTH"]) && $_SERVER["CONTENT_LENGTH"] > $size)
      {
        error_log("received partial upload, either fault or client cancelled");
		$error = "received partial upload, either fault or client cancelled";
        unlink($temp_out);
        $cancelled = 1;
      }

      $uploaded_tmp_name=$temp_out;
      $uploaded_name=$_REQUEST['qqfile'];
    }else{
	  if($_FILES['qqfile']['error']){
		error_log("File to big (Set via multipart form so limit is less)");
		$error = "File to big (Set via multipart form so limit is less)";
        $cancelled = 1;
	  }else{
	  $size = $_FILES['qqfile']['size'];
	  $uploaded_tmp_name=$_FILES['qqfile']['tmp_name'];
      $uploaded_name=$_FILES['qqfile']['name'];
  	  }
	}
	}
 	
    $filename = 'unset';
    $filename_alias = 'unset';
    $post_id = (int)$_REQUEST['post_id'];

    if($cancelled)
    {
    }
    elseif($_REQUEST['origin']=='file' || $_REQUEST['origin']=='url')
    {
      if($_REQUEST['origin']=='url')
      {
        if(parse_url($_REQUEST['fileurl']))
        {
          //$filename = basename($_REQUEST['fileurl']); // would never work meaningfully
          $filename = $_REQUEST['fileurl'];
          $filename_alias = $filename;
          $ok = 1;
        }
      }
      else // normal file upload
      {
        $filename = $uploaded_tmp_name;
        $filename_alias = $uploaded_name;
        $ok = 1;
      }

      if($ok)
      {
	
	
	 	
	
        $title = (isset($_REQUEST['title'])) ? "{$_REQUEST['title']}" : $filename;
        $ext = pathinfo($filename_alias, PATHINFO_EXTENSION);
        $newid = add_data_by_filename($ext, $filename);
        $main_data = array("$ext"=>array("type"=>"local", "id"=>$newid, "name"=>$filename_alias));
 		$newid = add_data_meta($title, $main_data, NULL, $post_id);

    	$row = db_get_post_by_id($post_id, 'edit');

        $metadata = readxml($row['bit_meta']);

        if(isset($metadata['METADATA']['DATA']) && strlen($metadata['METADATA']['DATA']))
        {
          $metadata['METADATA']['DATA'] .= ",".$newid;
        }
        else
        {
          $metadata['METADATA']['DATA'] = $newid;
        }

        $meta = null;
        $metad = writexml($metadata);
        //$new_id = edit_blog($post_id, 'Added Data', NULL, NULL, $metad, NULL);
		$new_id = edit_blog_new($post_id, 'edit', 'Added Data', NULL, NULL, $metad, NULL);
        $error="none";

        `rm -f "$filename"`; // tidy up
      }
      else
      {
        $error = "Error";
      }
    }
    else if($_REQUEST['origin']=='zip')
    {
      $filename = $uploaded_tmp_name;
      $filename_alias = $uploaded_name;

      $tmpdir = $filename."_ex";
      @mkdir($tmpdir);
      `unzip "{$filename}" -d "{$tmpdir}"`;
      $files = array();
      exec("find $tmpdir", $files);

      foreach($files as $file)
      {
        $filename = basename($file);
        if($filename{0} == ".") continue;
        if($filename{0} == "$") continue;
        if($filename{0} == ":") continue;
        if(strtolower($filename) == "thumbs.db") continue;
        if(stristr($file,"/__")) continue;
        if(is_dir($file)) continue;

        $title = (isset($_REQUEST['title']) && $_REQUEST['title'] != '') ? "{$_REQUEST['title']}: $filename" : $filename;
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $newid = add_data_by_filename($ext, $file);
        $main_data = array("$ext"=>array("type"=>"local", "id"=>$newid, "name"=>$filename));
        $newid = add_data_meta($title, $main_data);
        $newids[] = $newid;
      }

      `rm -rf "$tmpdir"`;
      `rm -f "$filename"`; // tidy up

     
      $row = db_get_post_by_id($post_id, 'edit');
      $metadata = readxml($row['bit_meta']);

      if(isset($metadata['METADATA']['DATA']) && strlen($metadata['METADATA']['DATA']))
      {
        $metadata['METADATA']['DATA'] .= ",".join(",",$newids);
      }
      else
      {
        $metadata['METADATA']['DATA'] = join(",",$newids);
      }
      $meta = null;
      $metad = writexml($metadata);
      //$new_id = edit_blog($post_id, 'Added Data', NULL, NULL, $metad, NULL);
	  $new_id = edit_blog_new($post_id, 'edit', 'Added Data', NULL, NULL, $metad, NULL);
      $error="none";
    }
  


	if($error =="none"){
		echo "{success:true}";
	}else{
		echo "{error:$error}";
	}
	exit();
}

}
else
{
  $error = "Error";
}


if($error=="none")
{
  $bodytag = "onload=\"window.opener.post_form.elements['itemloop'].value = 1;window.opener.post_form.submit();self.close();\"";
}
else
{
  $bodytag = '';
}


$max_file_size = 134217728;
if(isset($ct_config['uploads_max_size'])) { $max_file_size = $ct_config['uploads_max_size']; }

$upload_action = "{$upload}?blog_id=".(int)$_REQUEST['blog_id'];
//$upload_action = "/cgi-bin/upload.cgi?blog_id=".(int)$_REQUEST['blog_id'];
$upload_blog_id = (int)$_REQUEST['blog_id'];
$upload_post_id = (int)$_REQUEST['post_id'];
$req_title = (isset($_REQUEST['title'])) ? stripslashes($_REQUEST['title']): '';

$jquery['ui'] = true;
$jquery['ui-tabs'] = true;

$head .= <<<HEAD
<link rel="stylesheet" type="text/css" href="{$ct_config['blog_path']}inc/fileuploader/fileuploader2.css">
<script src="{$ct_config['blog_path']}inc/fileuploader/fileuploader2.js" type="text/javascript"></script>
HEAD;
$jquery['code'] .= <<<UPSCRIPT

  var uploader;
  var uploader_zip;
 function createUploader()
  {
    uploader = new qq.FileUploader
    ({
      element: document.getElementById('file_uploader'),
      action: '${upload_action}',
      debug: true,
 	  autoUpload: false,
      sizeLimit: {$max_file_size},
	  uploadButtonText: 'Click to upload a file<br/>(You can also drag/drop*)',
      onSubmit: function(id, fileName){ populate_uploader(uploader, 'uploadform'); reset('file_uploader_zip'); },
      onComplete: function(id, fileName){ refreshParent(); }
    });
     uploader_zip = new qq.FileUploader
    ({
      element: document.getElementById('file_uploader_zip'),
      action: '${upload_action}',
      debug: true,
 	  autoUpload: false,
      sizeLimit: {$max_file_size},
	  multiple: false,
	allowedExtensions: ['zip'],
	acceptFiles:	'application/zip, application/x-zip, application/x-zip-compressed, application/octet-stream, application/x-compress, application/x-compressed, multipart/x-zip',
	
      onSubmit: function(id, fileName){ populate_uploader(uploader_zip, 'uploadform_zip'); reset('file_uploader'); },
      onComplete: function(id, fileName){ refreshParent(); }
    });

    // $(function() { $("#tabs").tabs(); });
  }
window.onload = createUploader;
  function populate_uploader(obj, target)
  {
    obj.setParams({
      post_id: {$upload_post_id},
      title: document.getElementById(target).title.value,
      imagefile: '',
      stype: document.getElementById(target).stype.value,
      origin: document.getElementById(target).origin.value,
      utypesub: 'submitted'
    });
  }
  function reset(target) // work round a bug when using multiple FileUploaders
  {
    $("#" + target + "> .qq-uploader > .qq-upload-drop-area").css('display', 'none');
  }
  function refreshParent()
  {
    window.opener.refresh_data_box_items();
  }

UPSCRIPT;

$body = <<<BODY
<h1>File Upload</h1>

<div id='tabs'>
  <ul style="">
    <li id="simple_tabtop"><a href="#simple_tab">File</a></li>
    <li id="url_tabtop"><a href="#url_tab">URL</a></li>
    <li id="zip_tabtop"><a href="#zip_tab">Zip file</a></li>
  </ul>

<div id="simple_tab">
  <form id='uploadform' name='uploadform' enctype='' method='post' action='{$upload_action}'>
  <input type='hidden' name='MAX_FILE_SIZE' value='{$max_file_size}'>
  <input type='hidden' name='origin' value='file'>
  <table style="width: 300px;">
    <tr><td>Post ID</td><td>{$upload_post_id}</td></tr>
    <tr><td>Title</td><td><input type=text name=title value="{$req_title}" style="width: 220px;"></td></tr>
    <tr><td>Type</td><td>
      <select name=stype>
        <option value=''>Auto Detect</option>
        <option value=jpg>Image (jpg)</option>
        <option value=png>Image (png)</option>
        <option value=gif>Image (gif)</option>
        <option value=html>Webpage (html)</option>
        <option value=m>Matlab Code (m)</option>
        <option value=asc>ASCII Text File (asc)</option>
        <option value=text>Plain Text File (txt)</option>
        <option value=pdf>Adobe PDF (pdf)</option>
      </select>
    </td></tr>
  </table>
  </form>
  <div id="file_uploader" style="width:300px; min-height: 140px;">
    <noscript><p>Please enable JavaScript to use file uploader.</p></noscript>
  </div>
  <div class="qq-start-button" onClick="uploader.uploadStoredFiles();">Start Upload</div>
</div> <!-- simple_tab -->

<div id="url_tab">
  <form id='uploadform_url' name='uploadform_url' enctype='multipart/form-data' method='post' action='{$upload_action}'>
  <input type='hidden' name='origin' value='url'>
  <input type='hidden' name='post_id' value="{$upload_post_id}">
  <table style="width: 300px;">
    <tr><td>Post ID</td><td>{$upload_post_id}</td></tr>
    <tr><td>Title</td><td><input type=text name=title value="{$req_title}" style="width: 220px;"></td></tr>
    <tr><td>URL:</td><td><input type="text" name="fileurl" style="width: 220px;"></td></tr>
    <tr><td>Type</td><td>
      <select name=stype>
        <option value=''>Auto Detect</option>
        <option value=jpg>Image (jpg)</option>
        <option value=png>Image (png)</option>
        <option value=gif>Image (gif)</option>
        <option value=html>Webpage (html)</option>
        <option value=m>Matlab Code (m)</option>
        <option value=asc>ASCII Text File (asc)</option>
        <option value=text>Plain Text File (txt)</option>
        <option value=pdf>Adobe PDF (pdf)</option>
      </select>
    </td></tr>
    <tr><td><td align=right><input type=submit name=utypesub value='Upload via URL'></td></tr>
  </table>
  </form>
</div> <!-- url_tab -->

<div id="zip_tab">
  <form id='uploadform_zip' name='uploadform_zip' enctype='multipart/form-data' method='post' action='{$upload_action}'>
  <input type='hidden' name='origin' value='zip'>
  <input type='hidden' name='MAX_FILE_SIZE' value='{$max_file_size}'>
  <input name='stype' type='hidden' value=''>
    <table style="width: 300px;">
      <tr><td>Post ID</td><td>{$upload_post_id}</td></tr>
      <tr><td>Title</td><td><input type=text name=title value="{$req_title}" style="width: 220px;"></td></tr>
    </table>
    </form>
  <div id="file_uploader_zip" style="width:300px; height: 165px;">
    <noscript><p>Please enable JavaScript to use file uploader.</p></noscript>
  </div>

  <div class="qq-start-button" onClick="uploader_zip.uploadStoredFiles();">Start Upload</div>
</div> <!-- zip_tab -->

</div> <!-- tabs -->

<a style="position: absolute; right: 20px; top: 20px;" href='' onclick='window.close();'>Close window</a>
<div>
BODY;

//$jquery['function'] = "$('#urlrow').hide();\n";
$jquery['function'] = <<<JQUERY
$('#tabs').tabs();
JQUERY;

include('page.php');
?>
