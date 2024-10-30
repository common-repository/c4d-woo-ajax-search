(function($){
	"use strict";
	$(document).ready(function(){
		var c4dWooAjaxSearch = false;
		// $('.c4d-woo-ajax-search form').on('submit', function(event){
		// 	event.preventDefault();
		// 	return false;
		// });
		$('.c4d-woo-ajax-search form [name="s"]').on('focusin', function(){
			$(this).parents('.c4d-woo-ajax-search').addClass('focus');
		});
		$('.c4d-woo-ajax-search form [name="s"]').on('focusout', function(){
			$(this).parents('.c4d-woo-ajax-search').removeClass('focus');
		});
		$('.c4d-woo-ajax-search form [name="s"]').on('keyup', function(event){
			event.preventDefault();
			var self = this,
			wrap = $(self).parents('.c4d-woo-ajax-search');
			if ($(this).val() == '') return false;
			if ($(this).val().length < 3) return false;
			wrap.addClass('processing');
			if (wrap.find('.c4d-woo-ajax-search__result').length < 1)
			wrap.append('<div class="c4d-woo-ajax-search__result"><div class="loading-icon"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div><div class="search-list"></div></div>');
			if (c4dWooAjaxSearch) {
				c4dWooAjaxSearch.abort();
			}

			c4dWooAjaxSearch = $.ajax({
				url: c4d_woo_ajax_search.ajax_url + '?action=c4d_woo_ajax_search',
				method: 'GET',
				cache: true,
				dataType: 'json',
				data: $(this).parents('form').serializeArray(),
				success: function(res){
					var html = [];
					if ($.isArray(res)) {
						$.each(res, function(index, val){
							var item = '<div class="item"><a href="'+val.url+'">';
							if (val.id < 0 ) {
								item += '<h3 class="title">'+val.title+'</h3>';	
							} else {
								item += '<img src="'+val.img+'"/>';
								item += '<h3 class="title">'+val.title+'</h3>';
								item += '<div class="price">'+val.price+'</div>';	
							}
							item += '</a></div>';
							html.push(item);
						});
					}
					wrap.find('.search-list').html(html.join(''));
					wrap.removeClass('processing');
				},
				complete: function(html){
				
				}
			}).done(function(){
				
			});
			return false
		});
	});
})(jQuery)