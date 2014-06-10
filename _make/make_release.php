<?php

$action = $argv[1];

$actions = array("list","set","make","help");

$useage = <<<END

	php make_release.php action

Actions;
	list:
	set:
	make:

END;

if(!in_array($action, $actions)){
	die("action not found, try 'php make_release.php help'\n");
}elseif($action == "help"){
	echo $useage;
	exit();
}

$temp_dir = "/opt/labtrovemake";
$this_dir = dirname(__FILE__);
$svnsubver = "2.3";
$svnpath = "https://labtrove.svn.sourceforge.net/svnroot/labtrove/labtrove_{$svnsubver}";

@mkdir($temp_dir, 0777,true);
$old_rev_file = "{$temp_dir}/last_revision";
if(file_exists($old_rev_file)){
	$oldr = (int)file_get_contents($old_rev_file);
}else{
	$oldr = 1;
}

$revision = `svn info $svnpath | grep "Last Changed Rev:"`;
$revs = explode(": ", $revision);
$revision = trim($revs[1]);

$version = "{$svnsubver}-r{$revision}";


switch($action){
        case "list":
                $list = `svn log -r {$oldr}:{$revision} $svnpath`;
                echo $list;
        break;
        case "set":
				@mkdir("{$temp_dir}/releases", 0777,true);
				passthru(" vi {$temp_dir}/cur_release");
                file_put_contents("{$temp_dir}/releases/$version", "LabTrove $version (".@date("Y-m-d").")
----------------------------------------------------------------\n".file_get_contents("{$temp_dir}/cur_release"));
        break;
		
		case "make":
			if(`head -n 1 {$temp_dir}/releases/$version`!=`head -n 1 {$temp_dir}/releaselist`){
				file_put_contents("{$temp_dir}/newreleaselist", file_get_contents("{$temp_dir}/releases/$version")."\n\n");
				file_put_contents("{$temp_dir}/newreleaselist", file_get_contents("{$temp_dir}/releaselist"),FILE_APPEND);
				rename("{$temp_dir}/newreleaselist", "{$temp_dir}/releaselist");
			}
			$releaselist = file_get_contents("{$temp_dir}/releaselist");
			
			`rm -rf {$temp_dir}/labtrove`;
			echo "Exporting Labtrove from svn\n";
			`svn export {$svnpath} {$temp_dir}/labtrove`;
			
			echo "Creating Labtrove versions and release log\n";
			file_put_contents("{$temp_dir}/labtrove/install/version","LabTrove $version");
			file_put_contents("{$temp_dir}/labtrove/install/releaselog", $releaselist);
			
			echo "Compressing/Copying Files\n";
			include("{$this_dir}/make_tasks.php");
			echo "{$this_dir}/make_tasks.php\n";
			if(is_array($tasks))
			foreach($tasks as $task ){
				echo "\t{$task['task']}: {$task['in']}\n";
				switch($task['task']){
					case "copy":
						copy("{$temp_dir}/labtrove{$task['in']}","{$temp_dir}/labtrove{$task['out']}");
					break;
					case "js":
						jsfile("{$temp_dir}/labtrove{$task['in']}","{$temp_dir}/labtrove{$task['out']}");
					break;
					case "css":
						cssfile("{$temp_dir}/labtrove{$task['in']}","{$temp_dir}/labtrove{$task['out']}");
					break;
				}
				
			}
			
			$tar_file = "{$temp_dir}/tars/labtrove-{$version}.tar.gz";
			echo "Compressing Tar\n";
			@mkdir("{$temp_dir}/tars/", 0777,true);
			`cd $temp_dir; tar zcf $tar_file labtrove`;
			
			echo "Done\n";
			
        break;

}



function jsfile($in,$out){
	$jsurl = "http://closure-compiler.appspot.com/compile";
	
	$script = "";
	$licence = "";
	if(!is_array($in)) $in = array($in);
	foreach($in as $sc){
	 	$scrpt = file_get_contents($sc);
		if(preg_match('/^((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', $scrpt, $matches))
			$licence .= $matches[0]."\n\n";
		$script .= $scrpt."\n\n";
	}
	
	$ch = curl_init($jsurl);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'output_info=compiled_code&output_format=text&compilation_level=WHITESPACE_ONLY&js_code=' . urlencode($script));
	$output = curl_exec($ch);
	curl_close($ch);
	
	file_put_contents($out, $licence.$output);

}

function cssfile($in,$out){
	$cssurl = "http://www.refresh-sf.com/yui/";
	$script = "";
	$licence = "";
	if(!is_array($in)) $in = array($in);
	foreach($in as $sc){
	 	$scrpt = file_get_contents($sc);
		if(preg_match('/^((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', $scrpt, $matches))
			$licence .= $matches[0]."\n\n";
		$script .= $scrpt."\n\n";
	}
	
	$ch = curl_init($cssurl);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'type=CSS&redirect=1&compresstext=' . urlencode($script));
	$output = curl_exec($ch);
	curl_close($ch);
	
	file_put_contents($out, $licence.$output);
}




?>