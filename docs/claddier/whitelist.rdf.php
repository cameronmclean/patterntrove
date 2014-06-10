<?php
include("../../lib/default_config.php");
	
header('Content-type: application/rdf+xml');
$repos = runQuery("SELECT * FROM claddier_whitelist", 'get claddier whitelist');
echo '<?xml version="1.0" encoding="UTF-8"?>

<rdf:RDF 
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:wl ="http://epubs.cclrc.ac.uk/vocab/trackback/"
>
';
while ($repo = db_get_next_row($repos)) {
	echo '  <wl:repository rdf:about="' . $repo['repository_url'] . '">
    <wl:hostname>' . $repo['repository_name'] . '</wl:hostname>
    <wl:ipaddress>' . $repo['repository_ip_address'] . '</wl:ipaddress>
  </wl:repository>';
}
echo '</rdf:RDF>';
