<?php
//Live copy stuff
function meta_meta($blog_id, $pagename = "", $construct = NULL){

global $ct_config,$request;

if(!$pagename) $pagename = $request['blog_sname'];



$ret = "";
if(!$construct)
$construct = meta_metas($blog_id);

if(is_array($construct)){
	
	
foreach(@$construct as $key=>$value){
	
	if(!@in_array(strtolower($key), $ct_config['blog_hide_meta'] )){

		if(count($value)>$ct_config['blog_infobar_max_no']){
			$copy = $value;
			arsort($copy);
			$i = 0;
			$local = array();
			foreach($copy as $k=>$v){
				$local[$k] = $i++; 
			}
			$add = " class=\"infohideable\"";
		}else{
			$local = false;
			$add = "";
		}
	

		

	$meta_part = strtotitle(str_replace("_"," ",$key));
$ret .= "<div class=\"infoSection\">".$meta_part."</div>";


	$ret .= "<ul id=\"infobar_meta_".ereg_replace("[^A-Za-z0-9]", "", $key)."\" $add>";
	

	foreach($value as $val=>$count){
		
		if($local!==false && $local[$val]>=$ct_config['blog_infobar_max_no']){
				$class = "infohide";
			}else{
				$class = "";
			}
		
		$vals = ucwords(str_replace("_"," ",$val));
		if(isset($request['meta']) && $request['meta']==$key && $request['value']==$val)
		$ret .= "\t\t<li>".$vals." <span class=\"num_posts\">(".$count.")</span></li>\n";
		else
		$ret .= "\t\t<li class=\"$class\"><a href=\"".render_link($pagename,array('meta' => $key , 'value' => $val))."\">".$vals."</a> <span class=\"num_posts\">(".$count.")</span></li>\n";

	}
	$ret .="</ul>";

	}
}


}


return $ret;
}


function meta_metas($blog_id)
{
	global $ct_config;

	$sql = "SELECT bit_meta FROM  blog_bits WHERE  bit_meta LIKE '%<meta>%</meta>%' AND  bit_blog = $blog_id AND bit_edit = 0";
	$tresult = runQuery($sql,'Fetch Page Groups');

	$set = 0;
	while($row = db_get_next_row($tresult))
	{
		$metadata = readxml($row['bit_meta']);
		$metadata = $metadata['METADATA']['META'];
		if(is_array($metadata))
		{
			foreach($metadata as $key => $value)
			{
				$splitvalue = explode(";",$value);
				foreach($splitvalue as $thebit)
				$construct[$key][$thebit] = $construct[$key][$thebit] +1;
				$set = 1;
			}
		}
	}

	return ($set==1) ? $construct : NULL;
}

function meta_metac($blog_id){

	global $ct_config;

			$construct = meta_metas($blog_id);
			if(is_array($construct))
			foreach($construct as $key => $value){
				$xml .= "\t\t<$key>\n";
					$i = 0;				
					foreach($value as $kk=>$vv){
						$xml .= "\t\t\t<i$i><name>$kk</name><count>$vv</count></i$i>\n";
						$i++;
					}
				$xml .= "\t\t</$key>\n";
			}

return $xml;
}

?>
