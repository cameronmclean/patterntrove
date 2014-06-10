/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
	tinymce.create('tinymce.plugins.BBCodePlugin', {
		init : function(ed, url) {
			var t = this, dialect = ed.getParam('bbcode_dialect', 'punbb').toLowerCase();

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t['_' + dialect + '_bbcode2html'](o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.set)
					o.content = t['_' + dialect + '_bbcode2html'](o.content);

				if (o.get)
					o.content = t['_' + dialect + '_html2bbcode'](o.content);
			});
		},

		getInfo : function() {
			return {
				longname : 'BBCode Plugin',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/bbcode',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		// Private methods

		// HTML -> BBCode in PunBB dialect
		_punbb_html2bbcode : function(s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};

			s =  s+"\n<!--HTML-->";

			rep(/<pre.*?class=\"code (.*?)\".*?>/gi,"[code=$1]");
			rep(/<pre.*?class=\"code\".*?>/gi,"[code]");
			rep(/<\/pre.*>/gi,"[/code]")
			
			rep(/<a.*? href=\"\/post:(.*?)\".*?>(.*?)<\/a>/gi,"[blog=$1]$2[/blog]");
			
		
			
			// example: <strong> to [b]
	/*		rep(/<a.*?href=\"(.*?)\".*?>(.*?)<\/a>/gi,"[url=$1]$2[/url]");
			rep(/<font.*?color=\"(.*?)\".*?class=\"codeStyle\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");
			rep(/<font.*?color=\"(.*?)\".*?class=\"quoteStyle\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");
			rep(/<font.*?class=\"codeStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");
			rep(/<font.*?class=\"quoteStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");
			rep(/<span style=\"color: ?(.*?);\">(.*?)<\/span>/gi,"[color=$1]$2[/color]");
			rep(/<font.*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[color=$1]$2[/color]");
			rep(/<span style=\"font-size:(.*?);\">(.*?)<\/span>/gi,"[size=$1]$2[/size]");
			rep(/<font>(.*?)<\/font>/gi,"$1");
			rep(/<img.*?src=\"(.*?)\".*?\/>/gi,"[img]$1[/img]");
			rep(/<span class=\"codeStyle\">(.*?)<\/span>/gi,"[code]$1[/code]");
			rep(/<span class=\"quoteStyle\">(.*?)<\/span>/gi,"[quote]$1[/quote]");
			rep(/<strong class=\"codeStyle\">(.*?)<\/strong>/gi,"[code][b]$1[/b][/code]");
			rep(/<strong class=\"quoteStyle\">(.*?)<\/strong>/gi,"[quote][b]$1[/b][/quote]");
			rep(/<em class=\"codeStyle\">(.*?)<\/em>/gi,"[code][i]$1[/i][/code]");
			rep(/<em class=\"quoteStyle\">(.*?)<\/em>/gi,"[quote][i]$1[/i][/quote]");
			rep(/<u class=\"codeStyle\">(.*?)<\/u>/gi,"[code][u]$1[/u][/code]");
			rep(/<u class=\"quoteStyle\">(.*?)<\/u>/gi,"[quote][u]$1[/u][/quote]");
			rep(/<\/(strong|b)>/gi,"[/b]");
			rep(/<(strong|b)>/gi,"[b]");
			rep(/<\/(em|i)>/gi,"[/i]");
			rep(/<(em|i)>/gi,"[i]");
			rep(/<\/u>/gi,"[/u]");
			rep(/<span style=\"text-decoration: ?underline;\">(.*?)<\/span>/gi,"[u]$1[/u]");
			rep(/<u>/gi,"[u]");
			rep(/<span style=\"text-decoration: ?line-through;\">(.*?)<\/span>/gi,"[s]$1[/s]");
			rep(/<blockquote[^>]*>/gi,"[quote]");
			rep(/<\/blockquote>/gi,"[/quote]");
			rep(/<br \/>/gi,"\n");
			rep(/<br\/>/gi,"\n");
			rep(/<br>/gi,"\n");
			rep(/<p>/gi,"");
			rep(/<\/p>/gi,"\n");
			rep(/&nbsp;|\u00a0/gi," ");
			rep(/&quot;/gi,"\"");
			rep(/&lt;/gi,"<");
			rep(/&gt;/gi,">");
			rep(/&amp;/gi,"&");
			
			
			rep(/<ol.*>/gi,"[list=1]");
			rep(/<\/ol.*>/gi,"[/list]");
			
			rep(/<li.*>/gi,"[*]");
			rep(/<\/li.*>/gi,"");*/

			
			
//			rep(/<br.*?\/>/gi,"\n\n");
//			rep(/<br.*>/gi,"\n\n");
//			rep(/<p.*?>/gi,"");
//			rep(/<\/p.*>/gi,"\n\n")
			
			
			return s; 
		},

		// BBCode -> HTML from PunBB dialect
		_punbb_bbcode2html : function(s) {
			s = tinymce.trim(s);

			function rep(re, str) {
			s = s.replace(re, str);
			};

			// example: [b] to <strong>
			rep(/\n\n/gi,"<p>");
			rep(/\[b\]/gi,"<strong>");
			rep(/\[\/b\]/gi,"</strong>");
			rep(/\[i\]/gi,"<em>");
			rep(/\[\/i\]/gi,"</em>");
			rep(/\[u\]/gi,"<u>");
			rep(/\[\/u\]/gi,"</u>");
			rep(/\[s\]/gi,"<span style=\"text-decoration: line-through;\">");
			rep(/\[\/s\]/gi,"</span>");
			rep(/\[url=([^\]]+)\](.*?)\[\/url\]/gi,"<a href=\"$1\">$2</a>");
			rep(/\[url\](.*?)\[\/url\]/gi,"<a href=\"$1\">$1</a>");
			rep(/\[img\](.*?)\[\/img\]/gi,"<img src=\"$1\" />");
			rep(/\[color=(.*?)\](.*?)\[\/color\]/gi,"<font color=\"$1\">$2</font>");
			//rep(/\[code\](.*?)\[\/code\]/gi,"<span class=\"codeStyle\">$1</span>");
			rep(/\[quote.*?\](.*?)\[\/quote\]/gi,"<span class=\"quoteStyle\">$1</span>");
			rep(/\[list\](.*?)\[\/list\]/gi,"<ul>\$1</ul>");
			rep(/\[list=1\](.*?)\[\/list\]/gi,"<ol>\$1</ol>");
			rep(/\[size=(\d*?)\](.*?)\[\/size\]/gi,"<span style=\"font-size:$1;\">$2</span>");
			rep(/\[\*\]/gi,"<li>");
			rep(/\[code\]/gi,"<pre class=\"code\">");
			rep(/\[code=([^\]]+?)\]/gi,"<pre class=\"code $1\">");
			rep(/\[blog=([^\]]+?)\](.*?)\[\/blog\]/gi,"<a href=\"/post:$1\">$2</a>");
			rep(/\[\/code\]/gi,"</pre>");
			
			rep(/\[table\]/gi,"<table class=\"table_st\" cellspacing=\"0\">");
			rep(/\[\/table\]/gi,"</table>");
			rep(/\[row\]/gi,"<tr><td class=\"table_st\">");
			rep(/\[\/row\]/gi,"</td></tr>");
			rep(/\[mrow\]/gi,"<tr class=\"table_title\"><td class=\"table_st\">");
			rep(/\[\/mrow\]/gi,"</td></tr>");
			rep(/\[mcol\]/gi,"</td><td class=\"table_st\">");
			rep(/\[col\]/gi,"</td><td class=\"table_st\">");
			rep(/\[col=(.*?)]/gi,"</td><td class=\"table_st\" align=\"\$1\">");
		
			
			
			rep(/\<\!--HTML--\>/gi,"");
			return s; 
		}
	});

	// Register plugin
	tinymce.PluginManager.add('labtrove', tinymce.plugins.BBCodePlugin);
})();