<?php
	$ct_config['skip_magic_quotes'] = true;
	include("../../../lib/default_config.php");

	if(!isset($_REQUEST['q']) && !strlen($_REQUEST['q'])){
		die("error");
	}

	$eq = $_REQUEST['q'];

	

 	$outfile = md5($eq).".png";
	
	$cache_folder = "../../{$ct_config['cache_dir']}/equations";

	if(!file_exists($cache_folder."/$outfile")){
		$cache_folder = "../../{$ct_config['cache_dir']}/equations"; 
		if(!file_exists($cache_folder))
			mkdir($cache_folder,0777,true);
			
		$outfile_temp = md5($eq);
		
		$eq_tex = "\\begin{equation*}\n{$eq}\n\\end{equation*}";
		$tex = <<<END
\\documentclass{minimal}
\\newcommand\\use[2][]{\\IfFileExists{#2.sty}{\\usepackage[#1]{#2}}{}}
\\use[utf8]{inputenc}
\\use{amsmath}
\\use{amsfonts}
\\use{amssymb}
\\use{mathrsfs}
\\use{mhchem}
\\use{esdiff}
\\use{cancel}
\\use[dvips,usenames]{color}
\\use{nicefrac}
\\use[fraction=nice]{siunitx}
\\use{mathpazo}
\\begin{document}
{$eq_tex}
\\end{document}
END;
	@mkdir("{$cache_folder}/{$outfile_temp}",0777,true);
	file_put_contents("{$cache_folder}/{$outfile_temp}/in.tex", $tex);
		$cmd = "cp mhchem/mhchem.sty {$cache_folder}/{$outfile_temp}/.";
		`$cmd`;
		$cmd = "latex --interaction=nonstopmode --output-directory={$cache_folder}/{$outfile_temp} {$cache_folder}/{$outfile_temp}/in.tex";
		`$cmd`;
		$cmd = "dvips -E {$cache_folder}/{$outfile_temp}/in.dvi -o {$cache_folder}/{$outfile_temp}/in.ps"; 
		`$cmd`;
		$cmd = " convert -trim -density 120 {$cache_folder}/{$outfile_temp}/in.ps {$cache_folder}/{$outfile}";
		`$cmd`;
		$cmd = "rm -r {$cache_folder}/{$outfile_temp}";
		`$cmd`;
	}
	
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($cache_folder."/$outfile")).' GMT', true, 200);
    header('Content-Length: '.filesize($cache_folder."/$outfile"));
    header('Content-Type: image/png');
	
	readfile($cache_folder."/$outfile");
		

?>