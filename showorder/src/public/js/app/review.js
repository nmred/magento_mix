$(document).ready(function() {
	$(document).on('click', '.reviews .rarrow a', function(){
		$('.reviews .listbox .slider').animate({
			marginLeft: -956,
		}, 800, function(){
			$('.reviews .perslider:first').appendTo('.reviews .listbox .slider');
			$('.reviews .listbox .slider').css('margin-left', 0);
		});
	});

	$(document).on('click', '.reviews .larrow a', function(){
		$('.reviews .perslider:last').prependTo('.reviews .listbox .slider');
		$('.reviews .listbox .slider').css('margin-left', -956);
		$('.reviews .listbox .slider').animate({
			marginLeft: 0,
		}, 800);
	});

	$('.reviews .listbox .slider .perslider .persliderbox .perbox a.thumb img').each(function(){
		$(this).load(function(){
			$(this).css('margin-left', -(($(this).width() - 180) / 2));
		});
	});
})
