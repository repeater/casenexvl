jQuery(document).ready(function($) {
	
	$(document).on('click','#qcopd_fa_icon > .field-item > input', function(e){

	    e.preventDefault();

		$('#fa-field-modal').show();
		$("#fa-field-modal").attr("data", this.id);

	});

	$( '.fa-field-modal-close' ).on( 'click', function() {
		$('#fa-field-modal').removeAttr("data");
		$('#fa-field-modal').hide();

	});

	$( '.fa-field-modal-icon-holder' ).on( 'click', function() {

		var icon = $(this).data('icon');
		$getid = $("#fa-field-modal").attr('data');
		$('#'+$getid).val(icon);
		$('#fa-field-modal').removeAttr("data");
		$('#fa-field-modal').hide();
	});
	
	$("#id_search").quicksearch("div.fa-field-modal-icons div.fa-field-modal-icon-holder", {
		noResults: '#noresults',
		stripeRows: ['odd', 'even'],
		loader: 'span.loading',
		minValLength: 2
	});
	
	



});

function showfamodal(data){
	
	document.getElementById('fa-field-modal').style.display = 'block';
	document.getElementById('fa-field-modal').setAttribute("data", data.id);
	//jQuery.('#fa-field-modal').show();
}