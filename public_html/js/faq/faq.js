function initMenu() {
  $('#faqaccd ul').hide();
  $('#faqaccd ul:first').show();
  $('#faqaccd li a').click(         
    function() {
  //console.log($(this).attr('class'));  

      var checkElement = $(this).next();
      if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
   checkElement.slideDown('normal');
   $(this).attr('class','faqselected');
   return false;
        }
  else
  {
   //alert("two");
   checkElement.slideUp('normal');
   $(this).attr('class','faqnormal');
  }
      }
    );
  }
$(document).ready(function() {initMenu();});
