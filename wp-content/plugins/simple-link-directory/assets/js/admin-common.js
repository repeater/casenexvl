jQuery(document).ready(function($){
	
	$('#sld_reset_upvote').on('click', function(e){
		e.preventDefault();
		$( "input[name*='qcopd_upvote_count']" ).each(function(){
			$(this).val(0);
		})
		$('#sld_show_msg').html('Upvote has been reset successfully. please click Update button.');
	})
	
	$('#sld_reset_all_upvotes').on('click', function(e){		
		e.preventDefault();
		$.post(ajaxurl, {
			action: 'show_qcsld_upvote_reset', 
			},
			function(data) {

				$('#wpwrap').append(data);

			}
		);
		
	})
	$(document).on( 'click', '.modal-content .close', function(){
        $(this).parent().parent().remove();
    })
	$(document).on( 'change', '#sld_list', function(){
        var currentVal = $(this).val();
		if(currentVal!=='all'){
			$.post(ajaxurl, {
				action: 'show_qcsld_list_items',
				listid: currentVal
				},
				function(data) {

					$('.sld_reset_child_item').append(data);
					
				}
			);
		}else{
			$('.sld_reset_child_item').html('');
		}
    })
	
	$(document).on('click','#sld_reset_votes', function(e){
		e.preventDefault();
		
		var list = $('#sld_list').val();
		var item = $('#sld_list_item').val();
		if(typeof(item)=='undefined'){
			item = '';
		}
		$.post(ajaxurl, {
			action: 'qcopd_reset_all_upvotes',
			list: list,
			item: item
			},
			function(data) {

				$('.sld_reset_child_item').append(data);
				
			}
		);
		
		
	})
	
	$('#tab_frontend').on('click',function(e){
		e.preventDefault();
		$('#sld_page_check').html('<p class="sld_page_loading">Loading...</p>');
		var datarr = ['sld_login', 'sld_registration', 'sld_dashboard', 'sld_restore'];
		for(var i=0;i<4;i++){
			
			$.post(ajaxurl, {
				action: 'qcopd_search_sld_page', 
				shortcode: datarr[i],

				},
				
				function(data) {
					$('#sld_page_check .sld_page_loading').hide();
					
					if(data!=='' && !data.match(/not/g)){
						$('#sld_page_check').append('<p style="color:green">'+data+'</p>');
					}else{
						$('#sld_page_check').append('<p style="color:red">'+data+'</p>');
					}
					
					
					
				});
			
			
		}
		//
	})

	$('#sld_flash_button').on('click',function(e){
		e.preventDefault();
		$('#sld_flash_msg').html('<p class="sld_page_loading">Loading...</p>');

			$.post(ajaxurl, {
				action: 'qcopd_flash_rewrite_rules', 
				},
				
				function(data) {

					$('#sld_flash_msg').html('<p class="sld_page_loading" style="color:green;">Rewrite has been Flushed Successfully!</p>');

				}
			);
	})
	
	
});