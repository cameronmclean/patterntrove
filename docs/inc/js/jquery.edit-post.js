function populateMetaValueNew(data, selval)
{
	var select = document.getElementById("metadata_value_new_select");
	select.options.length = 0;
	for(var i = 0; i < data.length; i++)
	{			
		var obj = data[i];
		if(obj['category'] == selval)
		{
			select.options[select.options.length] = new Option(obj['label']);
		}			
	}
}

function validateMetadata()
{
	var retval = true;
	if( $('#metadata_key_new').val().length > 0  ||  $('#metadata_value_new').val().length > 0)
	{		
		if(! ($('#metadata_key_new').val().length > 0  &&  $('#metadata_value_new').val().length > 0))
		{
			alert('Please provide both a key and value for metadata');
			retval = false;
		}
	}
	return retval;
}


var metadata_row_id = 100000;

$(function() {	
	
	$('#metadata_add_button').click(function(){
		selkey = $('#metadata_key_new').val();
		selval = $('#metadata_value_new').val();
		if(selkey.length < 1 || selval.length < 1)
		{
			alert('Please provide both a key and value for metadata');
			return false;
		}
		var ins = '<tr id="meta_row_'+metadata_row_id+'"><td></td>';
		ins += '<td><input type="text" name="meta_key[]" value="'+ selkey +'" style="width:176px" class="required" /></td>';
		ins += '<td><input type="text" name="meta_value[]" value="'+ selval +'" style="width:176px" class="meta_box_auto required" /></td>';
		ins += '<td><a  onClick="removeMetadata('+metadata_row_id+')" title="Remove" style="background-image: url(\'inc/icons/table_delete.png\');" class="button icononly"></td></tr>';
		$("#metadata_table tr:last").before(ins);
		metadata_row_id += 1;
		$('#metadata_key_new').val("");
		$('#metadata_value_new').val("");
	});
	
});

function removeMetadata(id){

	$('#meta_row_'+id).remove();
}

$.widget( "ui.combobox", {	
  _create: function() {
 var self = this;
 var select = this.element.hide(),
   selected = select.children( ":selected" ),
   value = selected.val() ? selected.text() : "";  
	var input = $( "<input />" )
   .insertAfter(select)
   .val( value )
   .attr('id', this.options.input_id)
   .attr('name', this.options.input_name)
   .attr('title', "Select or type a new value")
   .autocomplete({
  delay: 0,
  minLength: 0,
  source: function(request, response) {
    re = $.ui.autocomplete.escapeRegex(request.term);
	var matcher = new RegExp( "^" + re, "i" );
    response( select.children("option" ).map(function() {
   var text = $( this ).text();
   if ( this.value && ( !request.term || matcher.test(text) ) )
     return {
    label: text.replace(
      new RegExp(
     "(?![^&;]+;)(?!<[^<>]*)(" +
     $.ui.autocomplete.escapeRegex(request.term) +
     ")(?![^<>]*>)(?![^&;]+;)", "gi"),
      "<strong>$1</strong>"),
    value: text,
    option: this
     };
    }) );
  },
  select: function( event, ui ) {
    ui.item.option.selected = true;
    self._trigger( "selected", event, {
   item: ui.item.option
    });
  },
  change: function(event, ui) {
    if ( !ui.item ) {
   var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
   valid = false;
   select.children( "option" ).each(function() {
     if ( this.value.match( matcher ) ) {
    this.selected = valid = true;
    return false;
     }
   });
   if ( !valid ) {
     select.val( "" );
     return false;
   }
    }
  }
   })
   .addClass("combo_input ui-widget ui-widget-content ");
 input.data( "autocomplete" )._renderItem = function( ul, item ) {
   return $( "<li></li>" )
  .data( "item.autocomplete", item )
  .append( "<a>" + item.label + "</a>" )
  .appendTo( ul );
 };
 $( "<button/>" ) 
 .attr( "tabIndex", -1 )
 .attr('title', "Select, or type a new value")
 .insertAfter( input )
 .button({
   icons: {
  primary: "ui-icon-circle-triangle-s"
   },
   text: false
 })
 .removeClass( "ui-corner-all" )
 .addClass("combo_button")
 .click(function() {
  
   // pass empty string as value to search for, displaying all results
   input.autocomplete("search", "");
   input.focus();
   return false;
 });
  }
});

 $(document).ready(function(){
       
	   $('#metadata_key_new_select').combobox({
		   input_id: 'metadata_key_new',
		   input_name: 'meta_key[]'		   
	   });
	   
	   $('#metadata_value_new_select').combobox({
		   input_id: 'metadata_value_new',
		   input_name: 'meta_value[]'		   
	   });
	   
	   $('#section_select').combobox({
		   input_id: 'section',
		   input_name: 'section'		   
	   });
	   
	   $('#section').addClass('required');			
	   $('#section').removeClass('valid');
	   
	   $('#section').blur(function() {
			selval = $(this).val();
			if(selval.length < 1){			
				$('#section').addClass('required');			
				$('#section').removeClass('valid');
			}else{
				$('#section').removeClass('required');		
			}		
		});
		
		$('#post_form').validate();		
	   
    });


