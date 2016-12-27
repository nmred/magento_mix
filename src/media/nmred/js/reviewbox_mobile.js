jQuery(document).ready(function() {
	 function copyFromReviewContent(review_box) {
        var review_content = review_box.find('.content-full').html().replace(/\n/g, "<br />");
        var review_username = review_box.find('.username').html();
        var review_date = convertDateString(review_box.find('.date').html());
        var reviews_count = jQuery('.review-pic-box .reviews-count span');
        var htmlstr = '';

        htmlstr += '<div class="review-pic-slider-box" style="width:100%">';
        htmlstr += '<div class="review-pic-slider">';
        htmlstr += '<ul class="review-pic-slider-content">';
        review_box.find('.reviewthumb').each(function() {
            htmlstr += '<li><img src="' + jQuery(this).find('img').attr('rel') + '" /></li>';
        });
        htmlstr += '</ul></div>';
        htmlstr += '<div class="review-slider-bg"></div>';
        htmlstr += '</div>';

        htmlstr += '<div class="review-prologue" style="width:100%"><span class="review-username">' + review_username + '</span> reviewed on ' + review_date + ':</div>';
        htmlstr += '<div class="review-content" style="width:100%"><div class="review-content-text">' + review_content + '</div></div>';
        htmlstr += '<div class="review-control">';
        if (reviews_count.length > 0) {
            htmlstr += '<a href="javascript:void(0);" class="view-prev">Previous Review</a>'
            htmlstr += '<div class="review-count"><span class="review-index">' + (review_box.index() + 1) + '</span>/<span class="review-total">' + reviews_count.html() + '</span></div>'
            htmlstr += '<a href="javascript:void(0);" class="view-next">Next Review</a>';
        } else {
            htmlstr += '<div class="review-count"><span>1/1</span></div>';
        }
        htmlstr += '<a href="javascript:void(0);" class="close">Close</a>';
        htmlstr += '</div>';

        return htmlstr;
    }
	 function convertDateString(dateString) {
		 var dateArray = dateString.split(/[-/\.]0?/);
		 var monthNameAbbr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		 var monthNameFull = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		 if (dateArray.length == 3 && parseInt(dateArray[1]) <= 12 && parseInt(dateArray[1]) >= 1) {
			 return monthNameAbbr[parseInt(dateArray[1]) - 1] + ' ' + dateArray[2] + ', ' + dateArray[0];
		 } else {
			 return dateString;
		 }
	 }

    jQuery(document).on('click', '.reviewbox .reviewlistbox .colbox .ct a.reviewthumb, .reviewbox .colbox .ct a.readmore', function() {

		console.info("dede");
        var obj = jQuery(this);
        //floatGrayBox();
        var measureboxheight = 480;
        if(((jQuery(window).height() - measureboxheight) / 2) > 0){
            var mtop = ((jQuery(window).height() - measureboxheight) / 2) - 60;
        }else{
            var mtop = 30;
        }

        var review_box = obj.parent().parent();

        var htmlstr = copyFromReviewContent(review_box);

        //jQuery('#zoomTitle').html('Review by ' + review_username);
        jQuery('#zoomContent').html('<div class="floatgraybox"><div class="messagebox"><div class="messagebox_body"></div></div></div>');
        //jQuery('#zoomContent').html(htmlstr);
        jQuery('#zoomContainer').css('background-color', '#FFFFFF');
        jQuery('.floatgraybox .messagebox').addClass('reviewimagefloatingbox');
        jQuery('.floatgraybox .messagebox .messagebox_body').append(htmlstr);
        //jQuery('#zoomTitle').show();
        jQuery('#zoomContent').show();
        jQuery('#zoomContent .floatgraybox').css('position', 'relative').show();

        window_scroll_top = jQuery(window).scrollTop();
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
		if (jQuery('.review-pic-slider-content li img').length >= 1) {
			jQuery('.review-pic-slider-content li img').each(function() {
				jQuery(this).css('height', 'auto').css('width', width0 + 'px');
			});
		}

        jQuery(window).scrollTop(0);

        return false;
    });

    jQuery(document).on('click', '#zoomContent .review-control a[class!="close"]', function() {

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

        var review_box = jQuery('.reviewbox .reviewlistbox .review-pic-content li.item:eq(' + review_index + ')');
        var htmlstr = copyFromReviewContent(review_box);
        jQuery('.floatgraybox .messagebox .messagebox_body').html(htmlstr);

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
		if (jQuery('.review-pic-slider-content li img').length >= 1) {
			jQuery('.review-pic-slider-content li img').each(function() {
				jQuery(this).css('height', 'auto').css('width', width0 + 'px');
			});
		}

        return false;
    });

	jQuery(document).on('click', '#zoomContent .review-control a.close', function() {
		jQuery('#zoomClose').click();
	});

	jQuery(document).on('click', "#review .view-next", function() {			
		var obj = jQuery(this);		
		var index = parseInt(obj.attr('rel'));
		var total = parseInt(jQuery("#review_list_total").html());
		var next  = index + 1;
		if (next >= total) {
			next = 0;	
		}
		var id = 'review_li_index_' + next;
		jQuery("#review_li_index_" + index).hide();
		jQuery("#" + id).show();
		jQuery('#review_list_next').attr('rel', next);
		jQuery('#review_list_prev').attr('rel', next);
	});

	jQuery(document).on('click', "#review .view-prev", function() {			
		var obj = jQuery(this);		
		var index = parseInt(obj.attr('rel'));
		var total = parseInt(jQuery("#review_list_total").html());
		var prev  = index - 1;
		if (prev < 0) {
			prev = total - 1;	
		}
		var id = 'review_li_index_' + prev;
		jQuery("#review_li_index_" + index).hide();
		jQuery("#" + id).show();
		jQuery('#review_list_next').attr('rel', prev);
		jQuery('#review_list_prev').attr('rel', prev);
	});
});

