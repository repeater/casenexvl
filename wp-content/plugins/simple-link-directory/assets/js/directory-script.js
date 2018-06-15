function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
jQuery(document).ready(function($)
{
    
    //Masonary Grid
    $grid = $('.qc-grid').packery({
      itemSelector: '.qc-grid-item',
      gutter: 10
    });

    //Filter Directory Lists
	$(document).on("click",".filter-area a", function(event){
   

        event.preventDefault();

        var filterName = $(this).attr("data-filter");

        $(".filter-area a").removeClass("filter-active");
        $(this).addClass("filter-active");

        if( filterName == "all" )
        {

            $("#opd-list-holder .qc-grid-item").css("display", "block");
        }
        else
        {

            $("#opd-list-holder .qc-grid-item").css("display", "none");
            $("#opd-list-holder .qc-grid-item."+filterName+"").css("display", "block");
        }

        $('.qc-grid').packery({
          itemSelector: '.qc-grid-item',
          gutter: 10
        });

    });

    //UpvoteCount
	$(document).on("click",".upvote-btn", function(event){
    
		
		
		var chk = $(this);
		event.preventDefault();

		if ( chk.data('requestRunning') ) {
			return;
		}

		chk.data('requestRunning', true);
		
		
		
		if(typeof(allowupvote)!=='undefined' && allowupvote && !bookmark.is_user_logged_in){
			var slduserMessage = 'You have to login to upvote an item';
			
			if(upvoteloginurl!=''){
				if (confirm(slduserMessage)) {
					window.location.href = upvoteloginurl;
				}
			}else{
				alert('You have to login to upvote an item.');		
			}

			return;
		}
		
		
		if($(this).hasClass('sld_upvote_animation')){
			$(this).removeClass('sld_upvote_animation')
		}
		
		$(this).addClass('sld_upvote_animation');
		
        var data_id = $(this).attr("data-post-id");
        var data_title = $(this).attr("data-item-title");
        var data_link = $(this).attr("data-item-link");

        var parentLI = $(this).closest('li').attr("id");
		var uniqueId = $(this).attr("data-unique");

        var selectorBody = $('.qc-grid-item span[data-post-id="'+data_id+'"][data-item-title="'+data_title+'"][data-item-link="'+data_link+'"]');

        var selectorWidget = $('.widget span[data-post-id="'+data_id+'"][data-item-title="'+data_title+'"][data-item-link="'+data_link+'"]');

        var bodyLiId = $(".qc-grid-item").find(selectorBody).closest('li').attr("id");
        var WidgetLiId = $(selectorWidget).closest('li').attr("id");

        //alert( bodyLiId );
		setCookie('usnidg',ajax_object.ajax_nonce,1);
        $.post(ajaxurl, {
            action: 'qcopd_upvote_action', 
            post_id: data_id,
            meta_title: data_title,
            meta_link: data_link,
            li_id: parentLI,
			uniqueid: uniqueId,
			security:ajax_object.ajax_nonce
                
        }, function(data) {
            var json = $.parseJSON(data);
            //console.log(json.cookies);
            //console.log(json.exists);
            if( json.vote_status == 'success' )
            {
                $('#'+parentLI+' .upvote-section .upvote-count').html(json.votes);
                $('#'+parentLI+' .upvote-section .upvote-btn').css("color", "green");
                $('#'+parentLI+' .upvote-section .upvote-count').css("color", "green");

                $('#'+bodyLiId+' .upvote-section .upvote-count').html(json.votes);
                $('#'+bodyLiId+' .upvote-section .upvote-btn').css("color", "green");
                $('#'+bodyLiId+' .upvote-section .upvote-count').css("color", "green");

                $('#'+WidgetLiId+' .upvote-section .upvote-count').html(json.votes);
                $('#'+WidgetLiId+' .upvote-section .upvote-btn').css("color", "green");
                $('#'+WidgetLiId+' .upvote-section .upvote-count').css("color", "green");
            }
        });
       
    });
	$(document).on("click",".sld-upvote-btn-single", function(event){
    
		
		
		var chk = $(this);
		event.preventDefault();

		if ( chk.data('requestRunning') ) {
			return;
		}

		chk.data('requestRunning', true);
		
		if($(this).hasClass('sld_upvote_animation')){
			$(this).removeClass('sld_upvote_animation')
		}
		$(this).addClass('sld_upvote_animation');
        var data_id = $(this).attr("data-post-id");
        var data_title = $(this).attr("data-item-title");
        var data_link = $(this).attr("data-item-link");
		var uniqueId = $(this).attr("data-unique");
        //alert( bodyLiId );
        $.post(ajaxurl, {            
            action: 'qcopd_upvote_action', 
            post_id: data_id,
            meta_title: data_title,
            meta_link: data_link,
            li_id: '',
			uniqueid: uniqueId,
			security:ajax_object.ajax_nonce
                
        }, function(data) {
            var json = $.parseJSON(data);
            //console.log(json.cookies);
            //console.log(json.exists);
            if( json.vote_status == 'success' )
            {
                $('.upvote-section-style-single .upvote-count').html(json.votes);
                $('.upvote-section-style-single .upvote-on').css("color", "green");
                $('.upvote-section-style-single .upvote-count').css("color", "green");

            }
        });
       
    });
	
	/*$(document).on("click",".open-mpf-sld-more", function(e){
		e.preventDefault();
		var data_id = $(this).attr("data-post-id");
        var data_title = $(this).attr("data-item-title");
        var data_link = $(this).attr("data-item-link");
		var container = $(this).attr("data-mfp-src");
		
		$.post(ajaxurl, {            
            action: 'qcopd_load_long_description', 
            post_id: data_id,
            meta_title: data_title,
            meta_link: data_link,

        }, function(data) {
			$(container+' .sld_more_text').html(data);
        });
	})*/
	
	$('.sld_load_more').click(function(e){
		e.preventDefault();
		var data_id = $(this).attr("data-post-id");
        var data_title = $(this).attr("data-item-title");
        var data_link = $(this).attr("data-item-link");
		var container = $(this).attr("data-mfp-src");
		
		$.post(ajaxurl, {            
            action: 'qcopd_load_long_description', 
            post_id: data_id,
            meta_title: data_title,
            meta_link: data_link,

        }, function(data) {
			$(container+' .sld_more_text').html(data);
        });
	})
	
	$('.sld_load_video').click(function(e){
		e.preventDefault();
		
        var video_link = $(this).attr("data-videourl");
		var container = $(this).attr("data-mfp-src");
		
		$.post(ajaxurl, {            
            action: 'qcopd_load_video', 
            videurl: video_link,

        }, function(data) {
			$(container+' .sld_video').html(data);
        });
	})
	
	
	$(document).on('click','.bookmark-btn',function(event){

		event.preventDefault();
		var data_id = $(this).attr("data-post-id");
        var item_code = $(this).attr("data-item-code");
		var is_bookmarked = $(this).attr("data-is-bookmarked");
		var li_id = $(this).attr('data-li-id');
		
		var parentLi = $(this).closest('li').attr('id');
		
		var obj = $(this);
		
		if(!bookmark.is_user_logged_in){
			if(typeof login_url_sld ==="undefined" || login_url_sld==''){
				if(typeof(slduserMessage)!=="undefined" && slduserMessage!==''){
					alert(slduserMessage);
				}else{
					alert('You need to log in to add items to your favorite list.');
				}
				
			}else{
				
				if(typeof(slduserMessage)!=="undefined" && slduserMessage!==''){
					
					if (confirm(slduserMessage)) {
						window.location.href = login_url_sld;
					} else {
						// Do nothing!
					}	
					
				}else{
					if (confirm('You need to log in to add items to your favorite list.')) {
						window.location.href = login_url_sld;
					} else {
						// Do nothing!
					}	
				}
				
							
			}
		}else{
			
			if(is_bookmarked==0){
				
				$.post(ajaxurl, {
				action: 'qcopd_bookmark_insert_action', 
				post_id: data_id,
				item_code: item_code,
				userid: bookmark.userid,

				}, function(data) {
					
					obj.attr('data-is-bookmarked',1);
					obj.children().removeClass('fa-star-o').addClass('fa-star');
					
					if(typeof(template)!=="undefined"){
						var newliid = parentLi+'_clone';
						var cloneElem = $('#'+parentLi).clone();
						cloneElem.prop({ id: newliid});
						cloneElem.find('.upvote-section').remove();
						cloneElem.find('.bookmark-section').find('span').attr("data-li-id",newliid);
						cloneElem.find('.bookmark-section').find('span').find('i').removeClass('fa-star').addClass('fa-times-circle');
						cloneElem.prependTo("#sld_bookmark_ul");
						$('.qc-grid').packery({
						  itemSelector: '.qc-grid-item',
						  gutter: 10
						});
					}
					
					
				});
				
			}else{
				$.post(ajaxurl, {
				action: 'qcopd_bookmark_remove_action', 
				post_id: data_id,
				item_code: item_code,
				userid: bookmark.userid,

				}, function(data) {
					if(typeof li_id === "undefined" || li_id==''){
						obj.attr('data-is-bookmarked',0);
						obj.children().removeClass('fa-star').addClass('fa-star-o');
					}else{
						obj.closest('li').remove();
						$('.qc-grid').packery({
						  itemSelector: '.qc-grid-item',
						  gutter: 10
						});
					}
					
					
				});
			}
			
			
			
		}
		
	})
	
	
	

});

jQuery(document).ready(function($){
	if(typeof(statistic)!=='undefined' && statistic==true){
		
		$('#opd-list-holder ul li a').on('click', function(e){
			e.preventDefault();
			
			if($(this).data('itemurl')!='' && $(this).data('itemid')!=''){
				var itemid = $(this).data('itemid');
				var itemurl = $(this).data('itemurl');
				var itemsid = $(this).data('itemsid');
				
				if(typeof($(this).attr('target'))==='undefined'){
					window.open(itemurl,"_self");
				}else{
					window.open(itemurl);
				}
				
				
				$.post(ajaxurl, {
					action: 'qcopd_item_click_action', 
					itemid: itemid,
					itemurl: itemurl,
					itemsid: itemsid,
				}, function(data) {
					//window.location.href = itemurl;
					
				});
				
			}
		})
		
	}
})




jQuery(document).ready(function($)
{


    $(".sld_search_filter").keyup(function(){
 
        // Retrieve the input field text and reset the count to zero
        var filter = $(this).val(), count = 0;
 
        // Loop through the comment list
        $("#opd-list-holder ul li").each(function(){

            var dataTitleTxt = $(this).children('a').attr('data-title');
            var dataurl = $(this).find('a').attr('href');
			console.log(dataurl);


            if( typeof(dataurl) == 'undefined' ){
                dataurl = "-----";
            }


            if( typeof(dataTitleTxt) == 'undefined' ){
                dataTitleTxt = "-----";
            }

            var parentH3 = $(this).parentsUntil('.qc-grid-item').children('h3').text();
 
            // If the list item does not contain the text phrase fade it out
            if ($(this).text().search(new RegExp(filter, "i")) < 0 && dataurl.search(new RegExp(filter, "i")) < 0 && dataTitleTxt.search(new RegExp(filter, "i")) < 0 && parentH3.search(new RegExp(filter, "i")) < 0 ) {
                $(this).fadeOut();
				$(this).removeClass("showMe");		
 
            // Show the list item if the phrase matches and increase the count by 1
            }
            else {
                $(this).show();
				$(this).addClass("showMe");
                count++;
            }

        });
		
		$(".qcopd-single-list, .qcopd-single-list-1, .opd-list-style-8, .opd-list-style-9, opd-list-style-12, .sld-container").each(function(){
            
			var visibleItems = $(this).find("li.showMe").length;
			
			//console.log(visibleItems);
			
			if(visibleItems==0){
				$(this).hide();
				$(this).parent('.qcopd-list-column').hide();
			}else{
				$(this).show();
				$(this).parent('.qcopd-list-column').show();
			}
		});
		setTimeout(function(e){
			$grid = $('.qc-grid');
			$grid.packery('destroy').packery({
			  itemSelector: '.qc-grid-item',
			  gutter: 10
			});
		},1000);
		
		$grid = $('.qc-grid');
		$grid.packery('destroy').packery({
		  itemSelector: '.qc-grid-item',
		  gutter: 10
		});
		
 
    });

    $('#live-search').on('submit',function(e){
        e.preventDefault();
    })

    $('#captcha_reload').on('click', function(e){
        e.preventDefault();
        $.post(
            ajaxurl,
            {
                action : 'qcld_sld_change_captcha',
            },
            function(data){
                $('#sld_captcha_image').attr('src', data);
            }
        );
    })
	
	$('.qcld_sld_tablinks').on('click',function(){
			$('.qc-grid').packery({
			  itemSelector: '.qc-grid-item',
			  gutter: 10
			});
	})
	

	$('.open-mpf-sld-more').magnificPopup({
	  type:'inline',
	  midClick: true
	});
	$('.open-mpf-sld-video').magnificPopup({
	  type:'inline',
	  mainClass: 'mfp-with-nopadding',
	  midClick: true
	});
	
	
	$('#sld_enable_recurring').on('click', function(){
		
        if(this.checked){
            $('#paypalProcessor').hide();
            $('#paypalProcessor_recurring').show();
		}
        else{
            $('#paypalProcessor').show();
            $('#paypalProcessor_recurring').hide();
		}
		
	})

	$('#sld_claim_list').on('change',function(e){
		e.preventDefault();
		if($(this).val()!==''){
			$.post(
				ajaxurl,
				{
					action : 'qcld_sld_show_list_item',
					listid: $(this).val()
				},
				function(data){
					$('#sld_list_item').html(data);
				}
			);
		}else{
			$('#sld_list_item').html('');
		}
		
	})
	
});
jQuery(window).load(function(){
	
	jQuery('.qc-grid').packery({
	  itemSelector: '.qc-grid-item',
	  gutter: 10
	});
	

	
})