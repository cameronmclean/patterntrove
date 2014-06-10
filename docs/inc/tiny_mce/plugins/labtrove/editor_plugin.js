/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function(){tinymce.create("tinymce.plugins.BBCodePlugin",{init:function(ed,url){var t=this,dialect=ed.getParam("bbcode_dialect","punbb").toLowerCase();ed.onBeforeSetContent.add(function(ed,o){o.content=t["_"+dialect+"_bbcode2html"](o.content)});ed.onPostProcess.add(function(ed,o){if(o.set)o.content=t["_"+dialect+"_bbcode2html"](o.content);if(o.get)o.content=t["_"+dialect+"_html2bbcode"](o.content)})},getInfo:function(){return{longname:"BBCode Plugin",author:"Moxiecode Systems AB",authorurl:"http://tinymce.moxiecode.com",
infourl:"http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/bbcode",version:tinymce.majorVersion+"."+tinymce.minorVersion}},_punbb_html2bbcode:function(s){s=tinymce.trim(s);function rep(re,str){s=s.replace(re,str)}s=s+"\n\x3c!--HTML--\x3e";rep(/<pre.*?class=\"code (.*?)\".*?>/gi,"[code=$1]");rep(/<pre.*?class=\"code\".*?>/gi,"[code]");rep(/<\/pre.*>/gi,"[/code]");rep(/<a.*? href=\"\/post:(.*?)\".*?>(.*?)<\/a>/gi,"[blog=$1]$2[/blog]");return s},_punbb_bbcode2html:function(s){s=tinymce.trim(s);function rep(re,
str){s=s.replace(re,str)}rep(/\n\n/gi,"<p>");rep(/\[b\]/gi,"<strong>");rep(/\[\/b\]/gi,"</strong>");rep(/\[i\]/gi,"<em>");rep(/\[\/i\]/gi,"</em>");rep(/\[u\]/gi,"<u>");rep(/\[\/u\]/gi,"</u>");rep(/\[s\]/gi,'<span style="text-decoration: line-through;">');rep(/\[\/s\]/gi,"</span>");rep(/\[url=([^\]]+)\](.*?)\[\/url\]/gi,'<a href="$1">$2</a>');rep(/\[url\](.*?)\[\/url\]/gi,'<a href="$1">$1</a>');rep(/\[img\](.*?)\[\/img\]/gi,'<img src="$1" />');rep(/\[color=(.*?)\](.*?)\[\/color\]/gi,'<font color="$1">$2</font>');
rep(/\[quote.*?\](.*?)\[\/quote\]/gi,'<span class="quoteStyle">$1</span>');rep(/\[list\](.*?)\[\/list\]/gi,"<ul>$1</ul>");rep(/\[list=1\](.*?)\[\/list\]/gi,"<ol>$1</ol>");rep(/\[size=(\d*?)\](.*?)\[\/size\]/gi,'<span style="font-size:$1;">$2</span>');rep(/\[\*\]/gi,"<li>");rep(/\[code\]/gi,'<pre class="code">');rep(/\[code=([^\]]+?)\]/gi,'<pre class="code $1">');rep(/\[blog=([^\]]+?)\](.*?)\[\/blog\]/gi,'<a href="/post:$1">$2</a>');rep(/\[\/code\]/gi,"</pre>");rep(/\[table\]/gi,'<table class="table_st" cellspacing="0">');
rep(/\[\/table\]/gi,"</table>");rep(/\[row\]/gi,'<tr><td class="table_st">');rep(/\[\/row\]/gi,"</td></tr>");rep(/\[mrow\]/gi,'<tr class="table_title"><td class="table_st">');rep(/\[\/mrow\]/gi,"</td></tr>");rep(/\[mcol\]/gi,'</td><td class="table_st">');rep(/\[col\]/gi,'</td><td class="table_st">');rep(/\[col=(.*?)]/gi,'</td><td class="table_st" align="$1">');rep(/\<\!--HTML--\>/gi,"");return s}});tinymce.PluginManager.add("labtrove",tinymce.plugins.BBCodePlugin)})();
