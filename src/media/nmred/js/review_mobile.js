function showZoomWindow() {
    jQuery('#zoomContainer #zoom').show();
    jQuery("#zoomContainer").show();
    jQuery('.wrapper').hide();
}

function closeZoomWindow() {
    if (!jQuery("#zoomContainerOrderDress").is(':visible')) {
        jQuery('.wrapper').show();
    }
    jQuery('#zoomContainer #zoomStage').hide();
    jQuery('#zoomContainer #zoomTitle').hide();
    jQuery('#zoomContainer #zoomContent').hide();
    jQuery('#zoomContainer').hide();
    jQuery('#zoomContainer #zoom').hide();
}

jQuery(document).ready(function() {

	jQuery('.real-weddings1 .real-weddings-content .item .perbox a.thumb img').each(function(){
		jQuery(this).load(function(){
			jQuery(this).css('margin-left', -((jQuery(this).width() - 180) / 2));
		});
	});

	var review_image_ratio = 1;

	var zoomDiv = '<div id="zoomContainer"><div id="zoom"><a id="zoomClose" href="javascript:void(0);">✕</a><div id="zoomTitle"></div><div id="zoomStage"><img class="zoom-img" src=""></div><div id="zoomContent"></div></div></div>';
    jQuery('body').append(zoomDiv);

    jQuery(document).on('click', '#zoomClose', function() {
        closeZoomWindow();
        if (window_scroll_top != -1) {
        	jQuery(window).scrollTop(window_scroll_top);
        	window_scroll_top = -1;
        }
    });

    var supportsOrientationChange = "onorientationchange" in window,
        orientationEvent = supportsOrientationChange ? "orientationchange" : "resize",
        isAndroid = (/android/gi).test(navigator.appVersion);

	function copyFromRealWeddingsContent(data, review_index) {

		var review_box = jQuery('.real-weddings1 .real-weddings-content .item');
		var reviews_count = review_box.length;

		var htmlstr = '';
		htmlstr += '<div class="review-pic-slider-box" style="width:100%">';
		htmlstr += '<div class="review-pic-slider" style="width:100%">';
		htmlstr += '<ul class="review-pic-slider-content">';
		for(var i = 0; i < data['images'].length; i++) {
			htmlstr += '<li><img src="' + data['images'][i]['url'] + '" /></li>';
		}
		htmlstr += '</ul></div>';
		htmlstr += '<div class="review-slider-bg"></div>';
		htmlstr += '</div>';
		htmlstr += '<div class="review-prologue" style="width:100%"><span class="review-username">' + data['title'] + '</span></div>';
		htmlstr += '<div class="review-content" style="width:100%"><div class="review-content-text">' + data['comment'].replace(/\n/g, "<br />") + '</div></div>';

		htmlstr += '<div class="review-control-real-weddings">';
		if (reviews_count > 0) {
			htmlstr += '<a href="javascript:void(0);" class="view-prev">Previous Review</a>'
			htmlstr += '<div class="review-count"><span class="review-index">' + (review_index + 1) + '</span>/<span class="review-total">' + reviews_count + '</span></div>'
			htmlstr += '<a href="javascript:void(0);" class="view-next">Next Review</a>';
		} else {
			htmlstr += '<div class="review-count"><span>1/1</span></div>';
		}
		htmlstr += '</div>';

		htmlstr += '<div class="bbox"><div class="title">The dress(es) purchased:</div><div class="productlist">';
		for(i in data['products']){
		}
		for(var i = 0; i < data['product_infos'].length; i++) {
			htmlstr += '<a href="' + data['product_infos'][i]['url'] + '"><img src="' + data['product_infos'][i]['img'] + '" /></a>';
		}
		htmlstr += '</div></div>';

		return htmlstr;
	}

	jQuery(document).on('click', '#zoomContent .review-control-real-weddings a', function() {

		var obj = jQuery(this);
		var review_index = obj.parent().find('.review-index').html() - 1;
		var review_total = obj.parent().find('.review-total').html();

		if (obj.attr('class').indexOf('view-next') >= 0) {
			if (review_index + 1 >= review_total) review_index = 0;
			else review_index++;

		} else if (obj.attr('class').indexOf('view-prev') >= 0) {
			if (review_index - 1 < 0) review_index = review_total - 1;
			else review_index--;
		}

		var review_box = jQuery('.real-weddings1 .real-weddings-content .item:eq(' + review_index + ')');

		var relId = review_box.find('.perbox a.thumb').attr('rel');
		jQuery.ajax({
			url: '/showorder/index/review?id=' + relId,
			type: 'post',
			dataType: 'json',
			success: function(result) {
				var data = result.data;
				var htmlstr = copyFromRealWeddingsContent(data, review_index);
				jQuery('.floatgraybox .messagebox .messagebox_body').html(htmlstr);

				jQuery('.review-pic-slider-content img').load(function() {

					showZoomWindow();

					jQuery('.review-pic-slider').newMobileSlide({
				        container: '.review-pic-slider-box',
				        displaySlide: '.review-pic-slider',
				        contentSlide: '.review-pic-slider-content',
				        trigger: '.review-slider-bg',
				        loop: false,
				        imgWidthChange: true,
				        stepsSlide: true
			    	});

					// Fix the heights of slider images
					var width0 = jQuery('.review-pic-slider-content li:eq(0)').width();
					var height0 = jQuery('.review-pic-slider-content li:eq(0)').height();
					review_image_ratio = width0 / height0;
			    	if (jQuery('.review-pic-slider-content li img').length > 1) {
			    		jQuery('.review-pic-slider-content li img').each(function() {
			    			jQuery(this).css('height', height0 + 'px').css('width', 'auto');
			    		});
			    	}

			    	jQuery(window).scrollTop(0);
				});
			}
		});
	});

	jQuery(document).on('click', '.real-weddings1 .real-weddings-content .item .perbox a.thumb', function(){
		var obj = jQuery(this);
		jQuery.ajax({
			url: '/showorder/index/review?id=' + obj.attr('rel'),
			type: 'post',
			dataType: 'json',
			success: function(result) {
				var data = result.data;
				var review_index = obj.parent().parent().index();
				var htmlstr = copyFromRealWeddingsContent(data, review_index);

				window_scroll_top = jQuery(window).scrollTop();

		        jQuery('#zoomContent').html('<div class="floatgraybox"><div class="messagebox"><div class="messagebox_body"></div></div></div>');
		        jQuery('#zoomContainer').css('background-color', '#FFFFFF');
				jQuery('.floatgraybox .messagebox').addClass('reviewimagefloatingbox');
				jQuery('.floatgraybox .messagebox .messagebox_body').append(htmlstr);
				jQuery('#zoomContent').show();
				jQuery('#zoomContent .floatgraybox').css('position', 'relative').show();

				jQuery('.review-pic-slider-content img').load(function() {

					showZoomWindow();

					jQuery('.review-pic-slider').newMobileSlide({
				        container: '.review-pic-slider-box',
				        displaySlide: '.review-pic-slider',
				        contentSlide: '.review-pic-slider-content',
				        trigger: '.review-slider-bg',
				        loop: false,
				        imgWidthChange: true,
				        stepsSlide: true
			    	});

					// Fix the heights of slider images
					var width0 = jQuery('.review-pic-slider-content li:eq(0)').width();
					var height0 = jQuery('.review-pic-slider-content li:eq(0)').height();
					review_image_ratio = width0 / height0;
			    	if (jQuery('.review-pic-slider-content li img').length > 1) {
			    		jQuery('.review-pic-slider-content li img').each(function() {
			    			jQuery(this).css('height', height0 + 'px').css('width', 'auto');
			    		});
			    	}

			    	jQuery(window).scrollTop(0);
				});


			}
		});
	});

	jQuery(window).on('resize',function () {
		var height_resized = jQuery('.review-pic-slider-box').width() / review_image_ratio;

		jQuery('.review-pic-slider-content li img').each(function(i) {
			jQuery(this).height(height_resized);
			jQuery(this).css('width', 'auto');
		});
	});

	jQuery(document).on('click', '.floatgraybox .messagebox.reviewview .messagebox_body .imglist .rarrow a', function(){
		jQuery('.floatgraybox .messagebox.reviewview .messagebox_body .imglist .imgbox .sliderbox').animate({
			marginLeft: -500,
		}, 800, function(){
			jQuery('.floatgraybox .messagebox.reviewview .messagebox_body .imglist .imgbox .sliderbox .perimg:first').appendTo('.floatgraybox .messagebox.reviewview .messagebox_body .imglist .imgbox .sliderbox');
			jQuery('.floatgraybox .messagebox.reviewview .messagebox_body .imglist .imgbox .sliderbox').css('margin-left', 0);
		});
	});

	jQuery(document).on('click', '.floatgraybox .messagebox.reviewview .messagebox_body .imglist .larrow a', function(){
		jQuery('.floatgraybox .messagebox.reviewview .messagebox_body .imglist .imgbox .sliderbox .perimg:last').prependTo('.floatgraybox .messagebox.reviewview .messagebox_body .imglist .imgbox .sliderbox');
		jQuery('.floatgraybox .messagebox.reviewview .messagebox_body .imglist .imgbox .sliderbox').css('margin-left', -500);
		jQuery('.floatgraybox .messagebox.reviewview .messagebox_body .imglist .imgbox .sliderbox').animate({
			marginLeft: 0,
		}, 800);
	});

});

