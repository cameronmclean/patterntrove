tinyMCEPopup.requireLangPack();

var InsertLatexDialog = {
	init : function() {
		var ed = tinyMCEPopup.editor;
		var n = ed.selection.getNode();
		
		var f = document.forms[0];
		
		f.render.onclick = this.preview;
		
		var timeout;
		f.latex_formula.onchange = function() {
			clearTimeout(timeout);
			
			timeout = setTimeout(f.render.onclick, 300);
		};
		f.latex_formula.onfocus = function() {
			// Do nothing!
		};
		
		var content = tinymce.plugins.InsertLatex._getSelection(n);
		
		if (tinymce.trim(content) != '') {
			f.latex_formula.value = content;

			this.preview();
		}
	},

	insert : function() {
		var ed = tinyMCEPopup.editor;
		
		var f = document.forms[0];
		
		var content = f.latex_formula.value;
		
		ed.execCommand('mceInsertLatexContent', content);
		
		tinyMCEPopup.close();
	},
	
	preview : function() {
		var ed = tinyMCEPopup.editor;
		
		var f = document.forms[0];
		
		var content = f.latex_formula.value;
		
		if ((content != null) && (tinymce.trim(content) != '')) {
			var preview_wrapper = document.getElementById('preview_wrapper');
			var preview_spinner = document.getElementById('preview_spinner');
			var preview_image = document.getElementById('preview_image');
			
			var src = tinymce.plugins.InsertLatex._encodeLatex(content);
			
			if ((src != null) && (tinymce.trim(src) != '')) {
				preview_wrapper.style.display = '';
				preview_spinner.style.display = '';
				
				if (preview_image == null) {
					preview_image = document.createElement('IMG');
					preview_image.setAttribute('id', 'preview_image');
					preview_wrapper.appendChild(preview_image);
				}
				
				preview_image.style.display = 'none';
				
				preview_image.onload = function() {
					preview_wrapper.style.display = '';
					preview_spinner.style.display = 'none';
					preview_image.style.display = '';
				}
				
				preview_image.setAttribute('src', src);
			} else {
				preview_wrapper.style.display = 'none';
				preview_spinner.style.display = 'none';

				if (preview_image != null) {
					preview_wrapper.removeChild(preview_image);
				}
			}
		} else {
			preview_wrapper.style.display = 'none';
			preview_spinner.style.display = 'none';
			
			if (preview_image != null) {
				preview_wrapper.removeChild(preview_image);
			}
		}
		
		return;
	},
};

tinyMCEPopup.onInit.add(InsertLatexDialog.init, InsertLatexDialog);
