function encodeLatexRendererURL(a){
	var b=labtrove_url+"inc/equation/latex.php?q=";
	if(a===null||typeof a==="undefined"){
		return b
	}else{
		return b+encodeURIComponent($.trim(a))
	}
}
function decodeLatexRendererURL(a){
	if(a===null||typeof a==="undefined"){
		return null
	}else{
		a=$.url(a)
	}
	var b=$.url(labtrove_url+"inc/equation/latex.php");
	var c=true;
	$.each(["protocol","host","port","path"],function(){
		if(a.attr(this)!=b.attr(this)){
			c=false
		}
	});
	if(c){
		return decodeURIComponent(a.param("q"))
	}else{
		return null
	}
}

(function() {
	var isUndefined = function(o) {
		return (typeof(o) === "undefined") || (o === null);
	};
	
	var isDefined = function(o) {
		return !isUndefined(o);
	}
	
	tinymce.PluginManager.requireLangPack("insertlatex");

	tinymce.create("tinymce.plugins.InsertLatex", {
		init : function(ed, url) {
			this.editor = ed;
			this.url = url;
			
			ed.onInit.add(function() {
				if (ed && ed.plugins.contextmenu) {
					ed.plugins.contextmenu.onContextMenu.add(function(plugin, menu, element) {
						// if (tinymce.plugins.InsertLatex._isLatex(element)) {
							menu.add({
								title : "insertlatex.desc",
								icon : "insertlatex",
								cmd : "mceInsertLatex"
							});
						// } 
					});
				}
			});
			
			ed.addCommand("mceInsertLatex", function() {
				ed.windowManager.open({
					file : url + "/dialog.htm",
					width : 600 + parseInt(ed.getLang("insertlatex.delta_width", 0)),
					height : 420 + parseInt(ed.getLang("insertlatex.delta_height", 0)),
					inline : 1,
					resizeable: 1,
					scrollbars: 1
				}, {
					plugin_url : url
				});
			});
			
			ed.addCommand("mceInsertLatexContent", function(content) {
				if (isDefined(content)) {
					var src = tinymce.plugins.InsertLatex._encodeLatex(content);
					
					if (isDefined(src)) {
						var n = ed.selection.getNode();

						if (tinymce.plugins.InsertLatex._isLatex(n)) {
							ed.dom.setAttrib(n, "class", tinymce.plugins.InsertLatex._className());
							ed.dom.setAttrib(n, "alt", content);
							ed.dom.setAttrib(n, "title", content);
							ed.dom.setAttrib(n, "src", src);
						} else {
							var img = document.createElement("IMG");
							
							img.setAttribute("class", tinymce.plugins.InsertLatex._className());
							img.setAttribute("alt", content);
							img.setAttribute("title", content);
							img.setAttribute("src", src);

							var p = document.createElement("P");
							p.appendChild(img);

							ed.execCommand("mceInsertContent", false, p.innerHTML);
						}

						ed.execCommand("mceRepaint");
					}
				}
			});

			ed.addButton("insertlatex", {
				title : "insertlatex.desc",	
				cmd : "mceInsertLatex",
				image : url + "/img/latex.png"
			});

			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive("insertlatex", tinymce.plugins.InsertLatex._isLatex(n));
			});
		},

		createControl : function(n, cm) {
			return null;
		},

		getInfo : function() {
			return {
				longname  : "Insert LaTeX",
				author    : "Mark Borkum (University of Southampton)",
				authorurl : "mailto:m.i.borkum@soton.ac.uk",
				version   : "1.0"
			};
		}
	});
	
	tinymce.plugins.InsertLatex._className = function() {
		return "equation";
	};

	tinymce.plugins.InsertLatex._encodeLatex = encodeLatexRendererURL;

	tinymce.plugins.InsertLatex._decodeLatex = decodeLatexRendererURL;

	tinymce.plugins.InsertLatex._isLatex = function(n) {
		if (isDefined(n) && (n.nodeName == "IMG") && (n.className == tinymce.plugins.InsertLatex._className())) {
			var content = tinymce.plugins.InsertLatex._decodeLatex(n.src);
			
			if (isDefined(content)) {
				return true;
			}
		}
		
		return false;
	};
	
	tinymce.plugins.InsertLatex._getSelection = function(n) {
		if (tinymce.plugins.InsertLatex._isLatex(n)) {
			var content = tinymce.plugins.InsertLatex._decodeLatex(n.src);
			
			if (isDefined(content)) {
				return content;
			}
		}
		
		return "";
	};

	tinymce.PluginManager.add("insertlatex", tinymce.plugins.InsertLatex);
})();
