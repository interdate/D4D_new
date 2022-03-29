jQuery(document).ready(function($){	
	$('.hslider').lightSlider({
		gallery:false,
		item:1,
		freeMove:false,
		thumbItem:0,
		slideMargin: 0,
		speed: 3000,
		pause: 9000,
		mode: 'fade',
		auto: true,
		loop: true,
		pager: false,
		enableDrag: true,
		enableTouch: true,
		swipeThreshold: 40,
		onSliderLoad: function() {
			$('.imageslide').removeClass('cS-hidden');
		}   
		   
	});			
	
	//GO TOP
	$('.go-top').click(function () {
	 $('html, body').animate({
	 scrollTop:0
	 }, 'showscroll');
	 return false;
	});	
	
	$(window).scroll(function () {
		if ($(this).scrollTop() >= 200) {
		 $('.go-top').addClass("showscroll");
		}else {
			$('.go-top').removeClass("showscroll");
			}
			
			$('.header').toggleClass('sticky', $(document).scrollTop() >= 100);
	});	

  $(document).scroll(function() {
  var y = $(this).scrollTop();
  if (y > 100) {
    $('.go-top').fadeIn();
  } else {
    $('.go-top').fadeOut();
  }
});

});	


  



 

 
		
