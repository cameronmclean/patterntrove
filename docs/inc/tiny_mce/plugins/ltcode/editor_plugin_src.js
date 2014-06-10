
	
tinymce.create('tinymce.plugins.myltcode', {
        createControl: function(n, cm) {
                switch (n) {
                        case 'ltcode':
                                var c = cm.createMenuButton('ltcode', {
                                        title : 'Insert Code',
                                        image : 'inc/tiny_mce/plugins/ltcode/img/code.png',
                                        icons : false
                                });

                                c.onRenderMenu.add(function(c, m) {
                                        var sub;

										
                                        m.add({title : 'ASP', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code asp">{$selection}</pre>');
                                        }});
                                        m.add({title : 'Bash', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code bash">{$selection}</pre>');
                                        }});
                                        m.add({title : 'C', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code c">{$selection}</pre>');
                                        }});
                                        m.add({title : 'C++', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code cpp">{$selection}</pre>');
                                        }});
                                        m.add({title : 'CSS', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code css">{$selection}</pre>');
                                        }});
                                        m.add({title : 'Matlab', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code matlab">{$selection}</pre>');
                                        }});
                                        m.add({title : 'Java', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code java">{$selection}</pre>');
                                        }});
                                        m.add({title : 'Perl', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code perl">{$selection}</pre>');
                                        }});
                                        m.add({title : 'PHP', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code php">{$selection}</pre>');
                                        }});
                                        m.add({title : 'RDF', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code xml">{$selection}</pre>');
                                        }});
										m.add({title : 'R/S+', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code rsplus">{$selection}</pre>');
                                        }});
                                        m.add({title : 'SQL', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code sql">{$selection}</pre>');
                                        }});
                                        m.add({title : 'Text', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code text">{$selection}</pre>');
                                        }});
                                        m.add({title : 'XML', onclick : function() {
                                                tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<pre class="code xml">{$selection}</pre>');
                                        }});

                                        
                                });

                                // Return the new menu button instance
                                return c;
                }

                return null;
        }
});

// Register plugin with a short name
tinymce.PluginManager.add('ltcode', tinymce.plugins.myltcode);
