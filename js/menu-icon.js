// JavaScript Document
jQuery('.item').hover(
    function(){
        var $this = jQuery(this);
        expand($this);
    },
    function(){
        var $this = jQuery(this);
        collapse($this);
    }
);
function expand($elem){
    var angle = 0;
    var t = setInterval(function () {
        if(angle == 1440){
            clearInterval(t);
            return;
        }
        angle += 40;
        jQuery('.link',$elem).stop().animate({rotate: '+=-40deg'}, 0);
    },10);
    $elem.stop().animate({width:'268px'}, 1000)
    .find('.item_content').fadeIn(400,function(){
        jQuery(this).find('p').stop(true,true).fadeIn(600);
    });
}
function collapse($elem){
    var angle = 1440;
    var t = setInterval(function () {
        if(angle == 0){
            clearInterval(t);
            return;
        }
        angle -= 40;
        jQuery('.link',$elem).stop().animate({rotate: '+=40deg'}, 0);
    },10);
    $elem.stop().animate({width:'52px'}, 1000)
    .find('.item_content').stop(true,true).fadeOut()
          .find('p').stop(true,true).fadeOut();
}