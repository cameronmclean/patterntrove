(function(){tinymce.PluginManager.requireLangPack("ltpostlink");tinymce.create("tinymce.plugins.ltpostlink",{init:function(ed,url){ed.addCommand("mceLtpostlink",function(){ed.windowManager.open({file:"linkblog.php?blog_id="+blog_id,width:400+ed.getLang("example.delta_width",0),height:500+ed.getLang("example.delta_height",0),inline:1},{plugin_url:url,some_custom_arg:"custom arg"})});ed.addButton("ltpostlink",{title:"ltpostlink.desc",cmd:"mceLtpostlink",image:url+"/img/page_link.png"});ed.onNodeChange.add(function(ed,
cm,n){cm.setActive("ltpostlink",n.nodeName=="A")})},createControl:function(n,cm){return null},getInfo:function(){return{longname:"LabTrove Post Link",author:"LabTrove Team",authorurl:"http://www.labtrove.org",infourl:"",version:"1.0"}}});tinymce.PluginManager.add("ltpostlink",tinymce.plugins.ltpostlink)})();
