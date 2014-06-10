<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo strip_tags($title)?></title>
	<base href="<?php echo $ct_config['blog_url'];?>"/>
	<?php if(isset($head)) echo $head?>
	<link rel="stylesheet" type="text/css" href="<?php echo $ct_config['blog_path']; ?>style/style.css"/>
	<link rel="stylesheet" type="text/css" href="<?php echo $ct_config['blog_path']; ?>style/post.css"/>
	<link rel="stylesheet" type="text/css" href="<?php echo $ct_config['blog_path']; ?>style/default/style.css"/>
	<?php if($ct_config['blog_style']!="default"){ ?>
	<link rel="stylesheet" type="text/css" href="<?php echo $ct_config['blog_path']; ?>style/<?php echo $ct_config['blog_style']?>/style.css"/>
	<?php } ?>
	<?php if(isset($rss_feed))
		foreach($rss_feed as $feed){ 
		echo "<link rel=\"alternate\" href=\"{$feed['url']}\" type=\"{$feed['type']}\" lang=\"en\" title=\"{$feed['title']}\"/>\n";
	}

	if(isset($ct_config['extra_meta']))
	foreach($ct_config['extra_meta'] as $key=>$var){
		echo "<meta name=\"$key\" content=\"$var\" />\n";
	}
	?>
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo $ct_config['blog_path']; ?>inc/favicon.ico" />
	<meta content="width=800px" name="viewport" />
	
</head>
<body class="body_main" <?php if(isset($bodytag)) echo $bodytag?>>

	<div id="page">
		<div id="top_bit">
			<span style="float:right;">
				<?php if( is_set_not_empty('user_admin', $_SESSION) && $_SESSION['user_admin'] >= 3 ){ echo "<a href=\"{$ct_config['blog_path']}admin.php\">Admin</a> | "; } ?>
				<?php if( is_set_not_empty('user_name', $_SESSION) && $_SESSION['user_name'] ){ echo "<a href=\"{$ct_config['blog_path']}dashboard\">Dashboard</a> | "; } ?>
				<a href="<?php echo $ct_config['blog_path'];?>?allnotebooks">All Notebooks</a> | <a href="http://docs.labtrove.org/2.3/lt/Main_Page" title="The Help Guide to LabTrove"  target="_blank">Help</a>
				 | <a href="http://www.labtrove.org/support.html" title="How get support using LabTrove" target="_blank">Support</a>
				 | <a href="<?php echo $ct_config['blog_path'];?>about.php" title="About this LabTrove">About</a>
	</span><?php echo $loginbox ?></div>

		<div id="header">
			<div id="sitetitle" onclick="javascript:location.href='<?php echo $ct_config['blog_site_url']; ?>'"><?php echo $ct_config['blog_title'];?></div>
			<div id="blogtitle">
				<h1><a href="<?= (isset($title_url) ? $title_url : '') ?>" id="white"><?= (isset($title) ? $title : '') ?></a></h1>
				<span class="description"><?= (isset($desc) ? $desc : '') ?></span></div>
		</div>
			<div id="content">
			<?php echo drawMsg(); ?>
			<?php echo $body?>
			<div class="clear"></div>
		</div>
	<div id="footer">
	Powered by <a href="http://labtrove.org">LabTrove 2.3</a> &copy; University of Southampton
	</div>
</div>
</body>
</html>
