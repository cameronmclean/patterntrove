<?php
include("../../lib/default_config.php");
$title = $ct_config['blog_title'];
$desc = $ct_config['blog_desc'];
$body = "";
$head .= "<style type=\"text/css\"><!--
table { border: 1px solid black; margin: 0.5em 0 0 0; }
th { border: 1px solid black; background-color: #f5f5f5; text-align: center; padding: 0.5em;}
td { border: 1px solid black; padding: 0.5em;}
--></style>";
if(!empty($_SESSION['blog_id']) && file_exists("../lib/config/blog_{$_SESSION['blog_id']}.php"))
        include("../lib/config/blog_{$_SESSION['blog_id']}.php");

include("../style/{$ct_config['blog_style']}/blogstyle.php");
$blogpost['title'] = "Claddier (Trackback) Service for LabTrove";
$blogpost['post'] = '<h3>Queries</h3>
<table>
  <tr><th>Method</th><th>Description</th><th>Response</th><th>Example</th></tr>
  <tr><td rowspan="3"><b>GET</b></td><td>End-point for the information page (this page).</td><td>text/html</td><td>/claddier</td></tr>
  <tr><td>End-point for Trackback RSS feed.<br/><small>This feed displays <u>backward</b> (cited by) citations.</small></td><td>application/rss+xml</td><td>/claddier/<b>id</b>/?__mode=rss</td></tr>
  <tr><td>End-point for Claddier RDF metadata.</td><td>application/rdf+xml</td><td>/claddier/<b>id</b>/?__mode=rdf</td></tr>
  <tr><td><b>POST</b></td><td>End-point for Claddier (and Trackback) requests.</td><td>application/xml</td><td>/claddier/<b>id</b>/</td></tr>
</table>
<p>This service accepts <b>GET</b> and <b>POST</b> messages only.</p>
<p>Please note that, in order to maintain compatibility with legacy Trackback clients, the following URI\'s are equivalent:</p>
<ul>
  <li>/claddier/id</li>
  <li>/claddier/id/</li>
  <li>/claddier?tb_id=id</li>
  <li>/claddier/?tb_id=id</li>
</ul>
<h3>Trackback RSS Feed</h3>
The backwards (cited by) citataions for each LabTrove blog post can be monitored by subscribing to the <b>RSS 2.0</b> feed.<br/>
Please note that the <b>default</b> Trackback behaviour is to enclose the feed contents in a <b>&lt;response&gt;</b> element.<br/>
In order to subscribe to a feed using an &quot;out of the box&quot; RSS client, please use the &quot;suppress&quot; parameter.
<br/><br/>
<h3>Claddier RDF Metadata</h3>
This end-point provides a machine-readable description of the LabTrove blog post using the Dublin Core metadata specification, encoded as an RDF/XML document.<br/>
When accessed using a compatible Web browser, a human readable version of the document can be displayed using XSLT (XML stylesheet transforms).
<br/><br/>
<h3>Query Parameters</h3>
<table>
  <tr><th>Parameter</th><th>Description</th></tr>
  <tr><td><b>tb_id</b></td><td>Trackback (LabTrove blog post) identifier.<br/><small>This parameter is an alternative to the &quot;/&quot; syntax.</small></td></tr>
  <tr><td><b>__mode</b></td><td>Trackback GET operation.<br/><small>RDF and RSS are currently supported.</small></td></tr>
</table>
<br/>
<h3>Claddier White-list</h3>
An RDF document specifying the IP addresses white-listed for this trove is available <a href="' . $ct_config['blog_path'] .'claddier/whitelist.rdf">here.</a>  This white-listed can be managed by an admin <a href="' . $ct_config['blog_path'] .'claddier/admin">here</a>.
';

$body .= blog_style_post($blogpost);
include('../page.php');

?>
