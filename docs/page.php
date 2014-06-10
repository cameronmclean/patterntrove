<?php

header("Cache-Control: no-cache");
header("Pragma: no-cache");

if(!isset($minipage) || !$minipage)
	$loginbox = renlogin_blog();
	if (!isset($head)) {
		$head = "";
	}
	if(isset($jquery)){
		if( array_key_exists('function', $jquery) ){
			if(!isset($jquery['code'])){ $jquery['code'] = ''; }
			$jquery['code'] .= "\n\n$(function() {

			   {$jquery['function']}

			 });";
		}
		if(!isset($jquery['srcs'])) { $jquery['srcs'] = ''; }
		
		if(isset($jquery['ui'])){
			$jquery['srcs'] .= <<<JQUERY
				<script type="text/javascript" src="{$ct_config['blog_path']}inc/jquery-ui/js/jquery-ui-1.8.20.custom.min.js"></script>
				<link rel="stylesheet" type="text/css" href="{$ct_config['blog_path']}inc/jquery-ui/css/ui-lightness/jquery-ui-1.8.20.custom.css"/>
JQUERY;
		}
		
		if(isset($jquery['edit-post'])){
				$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/js/jquery.edit-post.js\"></script>\n";
		}
		
		if(isset($jquery['validate'])){
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/jquery/js/jquery.validate.js\"></script>\n";
		}
		

		if(isset($jquery['fieldselection'])){
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/jquery/js/jquery.fieldselection.js\"></script>\n";
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/blog.fieldselection.js\"></script>\n";
		}
		if(isset($jquery['tinymce'])){
			//	$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/tinymce/jquery.tinymce.js\"></script>\n";
						$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/jquery/js/jquery.url.js\"></script>\n";
						$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/tinymce/jquery.cookie.js\"></script>\n";
		}
		
		
		
		if(isset($jquery['markitup'])){
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/markitup/jquery.markitup.js\"></script>\n";
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/markitup/sets/bbcode/set.js\"></script>\n";
			$jquery['srcs'] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$ct_config['blog_path']}inc/markitup/skins/simple/style.css\" />\n";
			$jquery['srcs'] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$ct_config['blog_path']}inc/markitup/sets/bbcode/style.css\" />\n";
		}
		
		if(isset($jquery['ui-tabs'])){
				$jquery['srcs'] .= <<<JQE
				<link rel="stylesheet" href="{$ct_config['blog_path']}inc/fileuploader/jquery.ui.tabs.css" type="text/css" media="screen">
				<script type="text/javascript" src="{$ct_config['blog_path']}inc/fileuploader/jquery.ui.tabs.js"></script>
JQE;
		}
		
		if( !array_key_exists('srcs', $jquery) ){
			$jquery['srcs'] = "";
		}
		if( !isset($head) ) { $head = ""; }
		$head .= <<<END
				<script type="text/javascript" src="{$ct_config['blog_path']}inc/jquery/js/jquery-1.7.1.min.js"></script>
				<script type="text/javascript" src="{$ct_config['blog_path']}inc/jquery/js/jquery.textarea-expander.js"></script>
				{$jquery['srcs']}
END;
	}
	

	$head .="<script type=\"text/javascript\">\n";
	$head .= "/* <![CDATA[ */";
	$head .="var labtrove_path = '{$ct_config['blog_path']}';\n";
	$head .="var labtrove_url = '{$ct_config['blog_url']}';\n";
	
	if(isset($jquery['code'])){
		$head .="\n{$jquery['code']}\n";
	}
	$head .= "/* ]]> */";
	$head .="</script>";

	
if(isset($minipage) && $minipage){
	include("style/{$ct_config['blog_style']}/minipage.php");
}else{
	include("style/{$ct_config['blog_style']}/index.php");
}
?>
