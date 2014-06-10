<?php

$ct_config['hooks']['on_post_new'][] = array("function"=>"chemspider_generate_info_async", "params"=>array("bit_rid", "bit_edit"));
$ct_config['hooks']['on_post_edit'][] = array("function"=>"chemspider_generate_info_async", "params"=>array("bit_rid", "bit_edit"));
$chemspider_base_url = "http://www.chemspider.com/";

function chemspider_extract_keywords($content) {
	global $ct_config;
	$tmpfname = tempnam(sys_get_temp_dir(), 'oscar-'.md5(time().rand())); 
	chmod($tmpfname, 0644);
	$fh = fopen($tmpfname, "w");
	fwrite($fh, strip_tags($content));
    	fclose($fh);
	$command = '`which java` QueryOscarClient ' . $tmpfname . ' 2>>/tmp/oscar_log.txt';
	$ph = popen($command, 'r');
	$keywords = array();
	while (($kc_array = fgetcsv($ph, 1000, ",")) !== FALSE) {
		if (empty($kc_array[1]) || $kc_array[1] == "NaN") {
			$kc_array[1] = 0;
		}
		else {
			$kc_array[1] = round($kc_array[1], 5);
		}
		if (!isset($keywords[strtolower($kc_array[0])]) || $keywords[strtolower($kc_array[0])]['confidence'] < $kc_array[1]) {
			$keywords[strtolower($kc_array[0])] = array('keyword' => $kc_array[0] , 'confidence' => $kc_array[1]);
		}
	}
	pclose($ph);
	unlink($tmpfname);
	$keyword_confidences = array();
	foreach ($keywords as $k => $keyword) {
		if ($keyword['confidence'] > 0.4) {
			$keyword_confidences[$keyword['keyword']] = $keyword['confidence'];
		}
	}
       	return array(array_keys($keyword_confidences), $keyword_confidences);
}

function chemspider_query_keyword($keyword) {
	global $ct_config, $chemspider_base_url;
	$searchurl = "{$chemspider_base_url}Search.asmx/SimpleSearch?query=" . urlencode($keyword) . "&token={$ct_config['chemspider']['security_token']}";
	$response = @file_get_contents($searchurl);
	$xml = "";
	try {
		$xml = new SimpleXMLElement($response);
	}
	catch (Exception $exception) {
		return false;
	}
	if (empty($xml)) {
		return false;
	}	
	$xml->registerXPathNamespace("x", $chemspider_base_url);
	$csids = $xml->xpath('x:int');
	if (!empty($csids[0])) {
		$chem_info['ChemSpider url'] = $chemspider_base_url . "RecordView.aspx?id=" . $csids[0];
                $chem_info['id'] = $csids[0];
                $chem_info['image'] = $chemspider_base_url . "ImagesHandler.ashx?id=" . $csids[0];
		$searchurl_allinfo = $searchurl = "{$chemspider_base_url}MassSpecAPI.asmx/GetExtendedCompoundInfo?CSID={$csids[0]}&token={$ct_config['chemspider']['security_token']}";
		$response_allinfo = file_get_contents($searchurl_allinfo);
        	$xml_allinfo = new SimpleXMLElement($response_allinfo);
		$xml_allinfo->registerXPathNamespace("x", $chemspider_base_url);
		$fields = array(
			'molecular weight' => 'MolecularWeight',
			'molecular formula' => 'MF',
			'smiles' => 'SMILES',
			'inchi' => 'InChI',
			'inchikey' => 'InChIKey',
			'average mass' => 'AverageMass',
			'monoisotopic mass' => 'MonoisotopicMass',
			'nominal mass' => 'NominalMass',
			'common name' => 'CommonName'
		);
		foreach ($fields as $k => $v) {
			$xpaths = $xml_allinfo->xpath('x:'.$v);
			$chem_info[$k] = (string)$xpaths[0];
		}
		return $chem_info;		
	}
	return false;
}	

function chemspider_generate_info($bit_rid, $bit_content) {
	list($keywords, $confidences) = chemspider_extract_keywords($bit_content);
	$keywords_string = implode(",", $keywords);
	$keywords_string_escaped = '"' . str_replace(',', '","', addslashes($keywords_string)) . '"';
	$sql = "SELECT * FROM `chemspider_keywords` WHERE `kw_value` IN ($keywords_string_escaped)";
	$result = runQuery($sql, "get chemspider keywords in list");
	$keywords_db = array();
	while ($keyword = db_get_next_row($result)) {
		$keywords_db[$keyword['kw_value']] = $keyword;
	}
	foreach ($keywords as $keyword) {
		$keyword_info = chemspider_query_keyword($keyword);
		$keyword_info_checksum = "";
		if (!empty($keyword_info)) {
			$keyword_info_checksum = chemspider_generate_keyword_checksum($keyword_info);
		}	
		if (!isset($keywords_db[$keyword])) {
			runQuery("INSERT INTO `chemspider_keywords` (`kw_value`, `kw_md5`) VALUES('$keyword', '$keyword_info_checksum')", "insert chemspider keyword");
			$keywords_db[$keyword]['kw_id'] = db_insert_id();
			chemspider_update_keyword_properties($keywords_db[$keyword]['kw_id'], $keyword_info);		
		}
		elseif ($keywords_db[$keyword]['kw_md5'] != $keyword_info_checksum) {
			runQuery("UPDATE `chemspider_keywords` SET (`kw_md5`) VALUES('$keyword_info_checksum')", "update chemspider keyword");
			chemspider_update_keyword_properties($keywords_db[$keyword]['kw_id'], $keyword_info);
		}
	}
	$sql = "SELECT `chemspider_blog_keywords`.*, `chemspider_keywords`.`kw_value` FROM `chemspider_blog_keywords` INNER JOIN `chemspider_keywords` ON `chemspider_blog_keywords`.`blogkw_kw_id` = `chemspider_keywords`.`kw_id` WHERE `blogkw_bit_rid` = '$bit_rid'";
        $result = runQuery($sql, "get chemspider blog keywords in list");
	$blog_keywords = array();
	while ($blog_keyword = db_get_next_row($result)) {
                $blog_keywords[$blog_keyword['kw_value']] = $blog_keyword;
        }
	foreach ($keywords as $keyword) {
                if (!isset($blog_keywords[$keyword])) {
                        runQuery("INSERT INTO `chemspider_blog_keywords` (`blogkw_bit_rid`, `blogkw_kw_id`, `blogkw_confidence`) VALUES('$bit_rid', '" . $keywords_db[$keyword]['kw_id'] . "', '" . addslashes($confidences[$keyword]) . "')", 'insert chemspider blog keyword');
                }
                elseif ($blog_keywords[$keyword]['blogkw_confidence'] != $confidences[$keyword]) {
                        runQuery("UPDATE `chemspider_blog_keywords` SET `blogkw_confidence` = '" . addslashes($confidences[$keyword]) . "' WHERE `blogkw_bit_rid` = '$bit_rid' AND `blogkw_kw_id` = '" . $keywords_db[$keyword]['kw_id'] . "'", 'update chemspider blog keyword');
                        unset($blog_keywords[$keyword]);
                }
		else {
			unset($blog_keywords[$keyword]);
		}
        }
        foreach ($blog_keywords as $blog_keyword) {
                runQuery("DELETE FROM `chemspider_blog_keywords` WHERE `blogkw_kw_id` = '" . $blog_keyword['blogkw_kw_id'] . "' AND `blogkw_bit_rid` = '$bit_rid'", 'delete chemspider blog keyword');
        }
}

function chemspider_update_keyword_properties($keyword_id, $properties) {
	if (empty($properties)) {
		return;
	}
	$sql = "SELECT * FROM `chemspider_keyword_properties` WHERE `kwprop_kw_id` = '$keyword_id'";
	$result = runQuery($sql, "get chemspider keyword properties");
	$properties_db = array();
	while ($property = db_get_next_row($result)) {
		$properties_db[$property['kwprop_value']] = $property;
	}
	foreach ($properties as $key => $value) {
		if (!isset($properties_db[$key])) {
			runQuery("INSERT INTO `chemspider_keyword_properties` (`kwprop_kw_id`, `kwprop_key`, `kwprop_value`) VALUES('$keyword_id', '" . addslashes($key) . "', '" . addslashes($value) . "')", 'insert chemspider keyword property');
		}
		elseif ($properties_db[$key] != $value) {
			runQuery("UPDATE `chemspider_keyword_properties` SET `kwprop_value` = '" . addslashes($value) . "' WHERE `kwprop_kw_id` = '$keyword_id' AND `kwprop_key` = '" . addslashes($key) . "'", 'update chemspider keyword property');
			unset($properties_db[$key]);
		}
	}
	foreach (array_keys($properties_db) as $key) {
		runQuery("DELETE FROM `chemspider_keyword_properties` WHERE `kwprop_kw_id` = '$keyword_id' AND `kwprop_key` = '" . addslashes($key) . "'", 'delete chemspider keyword property');
	}
}	

function chemspider_generate_keyword_checksum($keyword_info) {
	foreach ($keyword_info as $k => $v) {
		$keyvalue_array[] = "$k=$v";
	}
	return md5(implode(",", $keyvalue_array));
}

function chemspider_render_text($chem_info) {
	$chem_info_text = "";
	foreach ($chem_info as $k => $v) {
		$chem_info_text .= "$k: $v";
	}
	return $chem_info_text;
}

function chemspider_generate_info_async($bit_rid, $bit_edit) {
	if ($bit_edit == 0) {
	        $cwd = explode("/", getcwd());
        	$cwd_last = $cwd[sizeof($cwd)-1];
	        $rel_path = ".";
        	if ($cwd_last == "docs") {
                	$rel_path = "..";
	        }
        	elseif (in_array($cwd_last, array("rest", "soap"))) {
                	$rel_path = "../../..";
	        }
        	pclose(popen('cd ' . $rel_path . '/lib/scripts/; `which php` ./chemspider_generate_info.php ' . escapeshellarg(addslashes($bit_rid)) . ' > /dev/null &', 'r'));
	}
}	

function chemspider_generate_post_info_box($bit_rid) {
	global $ct_config;
	$body = "<br/><h2>Chemspider Info</h2>\n";

	$sql = "SELECT * FROM `chemspider_keywords` INNER JOIN `chemspider_blog_keywords` ON `chemspider_keywords`.`kw_id` = `chemspider_blog_keywords`.`blogkw_kw_id` WHERE `blogkw_bit_rid` = '" . addslashes($bit_rid) . "'";
	$body .=  "\t\t\t<div id=\"chemspider_keywords\">";
	$keywords_result = runQuery($sql, 'get chemspider keywords for blog post revision');
	if (db_get_number_of_rows($keywords_result) == 0) {
		$body .= "Unable to find chemical names in the post</div>\n";
	}
	else {
		$keywords = array();
		$body .= "\n\t\t\t\t<div>";
		$keyword = db_get_next_row($keywords_result);
		while (!empty($keyword)) {
			$keyword_value_entity = str_replace(array(' '), array('_'), $keyword['kw_value']);
			$keywords[$keyword_value_entity] = $keyword;
			$body .= "\n\t\t\t\t\t<a name=\"cs_keyword_{$keyword['kw_value']}\" style=\"cursor:pointer;\" onClick=\"showChemspiderKeyword('$keyword_value_entity');\">{$keyword['kw_value']}</a>";
			$keyword = db_get_next_row($keywords_result);
			if (!empty($keyword)) {
				$body .= ", ";
			}
		}
		$body .= "\n\t\t\t\t</div>\n";
		$display = "block";
		foreach ($keywords as $keyword_name => $keyword) {
			$keyword_properties = chemspider_get_keyword_properties($keyword['kw_id']);
			$body .=  "\t\t\t\t<div class=\"chemspider_keyword\" id=\"cs_keyword_{$keyword_name}\" style=\"display: $display\">\n\t\t\t\t\t<h3>{$keyword['kw_value']} <font style=\"font-weight: normal; font-size: 0.7em;\">(Confidence: {$keyword['blogkw_confidence']}, Timestamp: {$keyword['blogkw_timestamp']})</font></h3>\n";
			$display = "none";
			if (sizeof($keyword_properties) > 0) {
				$body .= "\n\t\t\t\t\t<div style=\"float: right;\"><a href=\"{$keyword_properties['ChemSpider url']}\"><img src=\"{$keyword_properties['image']}\"/></a></div>\n\t\t\t\t\t<div>\n\t\t\t\t\t\t<table class=\"csprop\">";
				unset($keyword_properties['ChemSpider url']);
				unset($keyword_properties['image']);
				
				foreach ($keyword_properties as $key => $value) {
		                        $body .= "\n\t\t\t\t\t\t\t<tr><th>{$key}:</th><td>{$value}</td></tr>";
				}
				$body .= "\n\t\t\t\t\t\t</table>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div style=\"clear: both;\"></div>";
			}
			$body .= "\n\t\t\t\t</div>\n";
		}
		$body .= "\t\t\t</div>\n";
	}
	$body .= "<script type=\"text/javascript\">
<!--
function showChemspiderKeyword(keyword) {
        $('#chemspider_keywords').find('div.chemspider_keyword').css('display', 'none');
        $('#cs_keyword_'+keyword).css('display', 'block');
}
-->
</script>";
	return $body;
}

function chemspider_get_keyword_properties($keyword_id) {
	$sql = "SELECT * FROM `chemspider_keyword_properties` WHERE `kwprop_kw_id` = '$keyword_id'";
	$result = runQuery($sql, 'get properties for chemspider keyword');
	$keyword_properties = array();
	while ($keyword_property = db_get_next_row($result)) {
		$keyword_properties[$keyword_property['kwprop_key']] = $keyword_property['kwprop_value'];
	}
	return $keyword_properties;
}
		
