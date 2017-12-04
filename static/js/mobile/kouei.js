$(function(){	
    var forumScroll = $(document).scrollTop();
    var navHeight = $('.main-nav').outerHeight();
    $(window).scroll(function() {
        var forumOnScroll = $(document).scrollTop();
        if (forumOnScroll > navHeight){$('.main-nav').addClass('main-nav-hide');}
        else {$('.main-nav').removeClass('main-nav-hide');}

        if (forumOnScroll > forumScroll){$('.main-nav').removeClass('main-nav-show');}
        else {$('.main-nav').addClass('main-nav-show');}

        forumScroll = $(document).scrollTop();
     });
});