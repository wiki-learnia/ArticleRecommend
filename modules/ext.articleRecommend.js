$(document).ready(function(){
    
    // init the first link
    $('.arLink:first').css('background', '#c4c4c4');
    $('.arLink:first').css('color', '#c4c4c4');
    
    
    // handle a click on a linkbox
    $('.arLinks').children().click(function(){
        $('.arLinks').children().css('background', '#efefef');
        $('.arLinks').children().css('color', '#efefef');
        // calculate the time
        
        var basicTime = 500;
        var time = 0;
        var width = $('.arRecContainer').width() / $('.arLinks').children().length;
        var goal = $('#'+$(this).attr('goto')).position().left - $('#'+$(this).attr('goto')).parent().position().left;
        var now = $('#'+$(this).attr('goto')).parent().parent().scrollLeft();
        var way = Math.abs(goal - now)
        var toGo = way / width;
        
        for(var i = 1; i <= toGo; i++) {
            time = time + basicTime / i;
        }
        
        $('#'+$(this).attr('goto')).parent().parent().animate({scrollLeft: goal}, time);
        $(this).css('background', '#c4c4c4');
        $(this).css('color', '#c4c4c4');
    });
});