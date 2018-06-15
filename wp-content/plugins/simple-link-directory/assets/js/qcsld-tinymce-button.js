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
      
	  
      var count = $('.sld_item_count:checked').val();
      var orderby = $('#sld_orderby').val();
      var filterorderby = $('#sld_filter_orderby').val();
	  
      var item_orderby = $('#sld_item_orderby').val();
      var order = $('#sld_order').val();
      var filterorder = $('#sld_filter_order').val();
	  
	  var listId = $('#sld_list_id').val();
	  var catSlug = $('#sld_list_cat_id').val();

	  var list_title_font_size = $('#sld_list_title_font_size').val();
	  var list_title_line_height = $('#sld_list_title_line_height').val();

	  var title_font_size = $('#sld_title_font_size').val();
	  var subtitle_font_size = $('#sld_subtitle_font_size').val();
	  var title_line_height = $('#sld_title_line_height').val();
	  var subtitle_line_height = $('#sld_subtitle_line_height').val();

	  var paginate = $('.sld_enable_pagination:checked').val();
	  var tooltip = $('.sld_enable_tooltip:checked').val();

	  var per_page = $('#sld_items_per_page').val();

	  var filter_area = $('#sld_filter_area').val();
	  var topspacing = $('#sld_topspacing').val();

	  var sld_category_orderby = $('#sld_category_orderby').val();
	  var sld_category_order = $('#sld_category_order').val();
	  
	  
	  var infinityscroll = $('#infinityscroll:checked').val();
	  var favorite = $('#sld_favorite').val();
		
		var sld_left_filter = $('.sld_left_filter:checked').val();
		
	  if( style == '' )
	  {
		alert("Please select a valid template style.");
		return;
	  }

	  if(mode=='categorytab'){
	  	var shortcode = 'sld-tab';
	  }else{
          var shortcode = 'qcopd-directory';
	  }
	  
	  var shortcodedata = '['+shortcode;
		  		  
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
		  
		  var style = $('#sld_style').val();
		

		  
		  if( column !== '' ){
			  shortcodedata +=' column="'+column+'"';
		  }
		  

		  
		  if( typeof(upvote) != 'undefined' ){
			  shortcodedata +=' upvote="'+upvote+'"';
		  }else{
			  shortcodedata +=' upvote="off"';
		  }
		  
		  if( typeof(search)!= 'undefined' ){
			  shortcodedata +=' search="'+search+'"';
		  }else{
			  shortcodedata +=' search="false"';
		  }
		  
		 
		  
		  if( typeof(count)!= 'undefined' ){
			  shortcodedata +=' item_count="'+count+'"';
		  }else{
			  shortcodedata +=' item_count="false"';
		  }
		  
		  if( orderby !== '' ){
			  shortcodedata +=' orderby="'+orderby+'"';
		  }
		  
		  if( typeof(filterorderby) != 'undefined' && filterorderby !== '' ){
			  shortcodedata +=' filterorderby="'+filterorderby+'"';
		  }
		  
		  if( order !== '' ){
			  shortcodedata +=' order="'+order+'"';
		  }
		  if( typeof(filterorder) != 'undefined' && filterorder !== '' ){
			  shortcodedata +=' filterorder="'+filterorder+'"';
		  }
		  
		  if( typeof(paginate) != 'undefined' ){
			  shortcodedata +=' paginate_items="true"';
		  }else{
			  shortcodedata +=' paginate_items="false"';
		  }
		  
		  if( typeof(paginate) != 'undefined' && per_page !== '' ){
			  shortcodedata +=' per_page="'+per_page+'"';
		  }
		  
		  if( typeof(infinityscroll) != 'undefined' && infinityscroll !== '' ){
			  shortcodedata +=' infinityscroll="'+infinityscroll+'"';
		  }
		  
		 
		  if( typeof(favorite) != 'undefined' && favorite !== '' ){
			  shortcodedata +=' favorite="'+favorite+'"';
		  }
		  
		  if( typeof(sld_left_filter) != 'undefined' && sld_left_filter !== '' ){
			  shortcodedata +=' enable_left_filter="'+sld_left_filter+'"';
		  }
		  
		  if( typeof(tooltip) != 'undefined' ){
			  shortcodedata +=' tooltip="true"';
		  }else{
			  shortcodedata +=' tooltip="false"';
		  }

			if(mode=='categorytab'){
				if(typeof(sld_category_orderby)!='undefined' || sld_category_orderby!=''){
					shortcodedata +=' category_orderby="'+sld_category_orderby+'"';
				}

				if(typeof(sld_category_order)!='undefined' || sld_category_order!=''){
					shortcodedata +=' category_order="'+sld_category_order+'"';
				}
			}


		  if(typeof(list_title_font_size)!='undefined' || list_title_font_size!=''){
              shortcodedata +=' list_title_font_size="'+list_title_font_size+'"';
		  }

		  if(typeof(item_orderby)!='undefined' || item_orderby!=''){
              shortcodedata +=' item_orderby="'+item_orderby+'"';
		  }

		  if(typeof(list_title_line_height)!='undefined' || list_title_line_height!=''){
              shortcodedata +=' list_title_line_height="'+list_title_line_height+'"';
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

        if(typeof(filter_area)!='undefined' || filter_area!=''){
            shortcodedata +=' filter_area="'+filter_area+'"';
        }

        if(typeof(topspacing)!='undefined' || topspacing!=''){
            shortcodedata +=' topspacing="'+topspacing+'"';
        }
		  
		  shortcodedata += ']';
		
		  tinyMCE.activeEditor.selection.setContent(shortcodedata);
		  
		  $('#sm-modal').remove();


    }).on( 'change', '#sld_mode',function(){
	
		var mode = $('#sld_mode').val();
		
		if( mode == 'one' ){
			$('#sld_list_div').css('display', 'block');
			$('#sld_list_cat').css('display', 'none');
			$('#sld_con_orderby').css('display', 'none');
			$('#sld_con_order').css('display', 'none');

            $('#sld_cat_orderby').css('display', 'none');
            $('#sld_cat_order').css('display', 'none');
			$('#sld_infinity_scroll').hide();
			$('#sld_item_per_page').hide();

		}
		else if( mode == 'category' ){
			$('#sld_list_cat').css('display', 'block');
			$('#sld_list_div').css('display', 'none');
            $('#sld_con_orderby').css('display', 'block');
            $('#sld_con_order').css('display', 'block');

            $('#sld_cat_orderby').css('display', 'none');
            $('#sld_cat_order').css('display', 'none');
			$('#sld_infinity_scroll').hide();
			$('#sld_item_per_page').hide();

		}else if(mode=='categorytab'){
            $('#sld_cat_orderby').css('display', 'block');
            $('#sld_cat_order').css('display', 'block');
            $('#sld_list_div').css('display', 'none');
            $('#sld_list_cat').css('display', 'none');
            $('#sld_con_orderby').css('display', 'block');
            $('#sld_con_order').css('display', 'block');
			$('#sld_infinity_scroll').hide();
			$('#sld_item_per_page').hide();
		}
		else{
			$('#sld_list_div').css('display', 'none');
			$('#sld_list_cat').css('display', 'none');
            $('#sld_con_orderby').css('display', 'block');
            $('#sld_con_order').css('display', 'block');
            $('#sld_cat_orderby').css('display', 'none');
            $('#sld_cat_order').css('display', 'none');
		}
		
	}).on( 'change', '#sld_style',function(){
	
		var style = $('#sld_style').val();

		if( style == '' ){
			alert("Please select a valid template style.");
			return;
		}

		if( style != 'style-10' ){
			$('.sld-off-field').css('display', 'block');
		}
		else
		{
			$('.sld-off-field').css('display', 'none');
		}

		if( style == 'simple' ){
			if($('#sld_mode').val()!=='categorytab' && $('#sld_mode').val()!=='category' && $('#sld_mode').val()!=='one'){
				$('#sld_infinity_scroll').show();
				
			}
			
			$('.tt-template').css('display', 'block');
			
		}
		else
		{
			$('#sld_infinity_scroll').hide();
			$('#sld_item_per_page').hide();
			$('.tt-template').css('display', 'none');
			
		}
		
if( style == 'simple' || style == 'style-1' || style == 'style-2' || style == 'style-8' || style == 'style-9' || style == 'style-12' || style == 'style-13' ){
			$('#sld_column_div').css('display', 'block');
		}
		else{
    		$('#sld_column_div').css('display', 'block');
		}
		
		if( style == 'simple' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/" target="_blank">View Demo for Default Style</a>');
		}
		else if( style == 'style-1' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-1/" target="_blank">View Demo for Style-1</a>');
		}
		else if( style == 'style-2' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-2/" target="_blank">View Demo for Style-2</a>');
		}
		else if( style == 'style-3' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-3/" target="_blank">View Demo for Style-3</a>');
		}
		else if( style == 'style-4' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-4/" target="_blank">View Demo for Style-4</a>');
		}
		else if( style == 'style-5' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-5/" target="_blank">View Demo for Style-5</a>');
		}
		else if( style == 'style-6' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-6/" target="_blank">View Demo for Style-6</a>');
		}
		else if( style == 'style-7' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-7/" target="_blank">View Demo for Style-7</a>');
		}
		else if( style == 'style-8' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-8/" target="_blank">View Demo for Style-8</a>');
		}
		else if( style == 'style-9' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-9/" target="_blank">View Demo for Style-9</a>');
		}
		else if( style == 'style-10' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-10/" target="_blank">View Demo for Style-10</a>');
		}
		else if( style == 'style-11' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-11/" target="_blank">View Demo for Style-11</a>');
		}
		else if( style == 'style-12' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-12/" target="_blank">View Demo for Style-12</a>');
		}
		else{
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/" target="_blank">View Demo for Default Style</a>');
		}		
		
	});

}(jQuery));
