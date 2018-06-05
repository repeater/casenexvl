;(function( $ ) {
    tinymce.PluginManager.add('qcsld_shortcode_btn', function( editor,url )
    {
        var shortcodeValues = [];

        editor.addButton('qcsld_shortcode_btn', {
			title : 'Add SLD Shortcode',
            //text: 'SLD',
            icon: 'icon qc_sld_btn',
            onclick : function(e){
                $.post(
                    ajaxurl,
                    {
                        action : 'show_qcsld_shortcodes'
                        
                    },
                    function(data){
                        $('#wpwrap').append(data);
                    }
                )
            },
            values: shortcodeValues
        });
    });

    var selector = '';

    $(document).on( 'click', '.modal-content .close', function(){
        $(this).parent().parent().remove();
    }).on( 'click', '#qcsld_add_shortcode',function(){
	
      var mode = $('#sld_mode').val();
      var column = $('#sld_column').val();
      var style = $('#sld_style').val();
      var upvote = $('.sld_upvote:checked').val();
      var search = $('.sld_search:checked').val();
	  var embeding = $('.sld_embeding:checked').val();
      var count = $('.sld_item_count:checked').val();
      var orderby = $('#sld_orderby').val();
      var order = $('#sld_order').val();
		var title_font_size = $('#sld_title_font_size').val();
		var subtitle_font_size = $('#sld_subtitle_font_size').val();
		var title_line_height = $('#sld_title_line_height').val();
		var subtitle_line_height = $('#sld_subtitle_line_height').val();
		var sld_itemorderby = $('#sld_itemorderby').val();
	  
	  var listId = $('#sld_list_id').val();
	  var catSlug = $('#sld_list_cat_id').val();
	  
	  var shortcodedata = '[qcopd-directory';
		  		  
		  if( mode !== 'category' ){
			  shortcodedata +=' mode="'+mode+'"';
		  }
		  
		  if( mode == 'one' && listId != "" ){
			  shortcodedata +=' list_id="'+listId+'"';
		  }
		  
		  
		  if( mode == 'category' && catSlug != "" ){
			  shortcodedata +=' category="'+catSlug+'"';
		  }
		  
		  if( style !== '' ){
			  shortcodedata +=' style="'+style+'"';
		  }
		  if( sld_itemorderby !== '' ){
			  shortcodedata +=' item_orderby="'+sld_itemorderby+'"';
		  }
		  
		  var style = $('#sld_style').val();
		
		  if( style == 'simple' || style == 'style-1' || style == 'style-2' || style == 'style-8' || style == 'style-9' ){
		  
			  if( column !== '' ){
				  shortcodedata +=' column="'+column+'"';
			  }
		  
		  }
		  
		  if( typeof(upvote) != 'undefined' ){
			  shortcodedata +=' upvote="'+upvote+'"';
		  }
		  
		  if( typeof(search)!= 'undefined' ){
			  shortcodedata +=' search="'+search+'"';
		  }
		  if( typeof(embeding)!= 'undefined' ){
			  shortcodedata +=' enable_embedding="'+embeding+'"';
		  }else{
			  shortcodedata +=' enable_embedding="false"';
		  }
		  
		  if( typeof(count)!= 'undefined' ){
			  shortcodedata +=' item_count="'+count+'"';
		  }
		  
		  if( orderby !== '' && mode!=='one'){
			  shortcodedata +=' orderby="'+orderby+'"';
		  }
		  
		  if( order !== '' && mode!=='one'){
			  shortcodedata +=' order="'+order+'"';
		  }

        if(typeof(title_font_size)!='undefined' || title_font_size!=''){
            shortcodedata +=' title_font_size="'+title_font_size+'"';
        }
        if(typeof(subtitle_font_size)!='undefined' || subtitle_font_size!=''){
            shortcodedata +=' subtitle_font_size="'+subtitle_font_size+'"';
        }
        if(typeof(title_line_height)!='undefined' || title_line_height!=''){
            shortcodedata +=' title_line_height="'+title_line_height+'"';
        }
        if(typeof(subtitle_line_height)!='undefined' || subtitle_line_height!=''){
            shortcodedata +=' subtitle_line_height="'+subtitle_line_height+'"';
        }
		  
		  shortcodedata += ']';
		
		  tinyMCE.activeEditor.selection.setContent(shortcodedata);
		  
		  $('#sm-modal').remove();


    }).on( 'change', '#sld_mode',function(){
	
		var mode = $('#sld_mode').val();
		
		if( mode == 'one' ){
			$('#sld_list_div').css('display', 'block');
			$('#sld_list_cat').css('display', 'none');
			$('#sld_orderby_div').css('display', 'none');
			$('#sld_order_div').css('display', 'none');
		}
		else if( mode == 'category' ){
			$('#sld_list_cat').css('display', 'block');
			$('#sld_list_div').css('display', 'none');
			$('#sld_orderby_div').css('display', 'block');
			$('#sld_order_div').css('display', 'block');
		}
		else{
			$('#sld_list_div').css('display', 'none');
			$('#sld_list_cat').css('display', 'none');
			$('#sld_orderby_div').css('display', 'block');
			$('#sld_order_div').css('display', 'block');
		}
		
	}).on( 'change', '#sld_style',function(){
	
		var style = $('#sld_style').val();
		
		if( style == 'simple' || style == 'style-1' ){
			$('#sld_column_div').css('display', 'block');
		}
		else{
			$('#sld_column_div').css('display', 'none');
		}
		
		if( style == 'simple' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/" target="_blank">http://dev.quantumcloud.com/sld/</a>');
		}
		else if( style == 'style-1' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-1/" target="_blank">http://dev.quantumcloud.com/sld/style-1/</a>');
		}
		else if( style == 'style-2' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-2/" target="_blank">http://dev.quantumcloud.com/sld/style-3/</a>');
		}
		else if( style == 'style-3' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-3/" target="_blank">http://dev.quantumcloud.com/sld/style-5/</a>');
		}
		else{
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/" target="_blank">http://dev.quantumcloud.com/sld/</a>');
		}		
		
	});

}(jQuery));
