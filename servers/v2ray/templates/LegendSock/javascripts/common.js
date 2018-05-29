$(document).ready(function($) {
    $(".alert li:not(:first)").css("display","none");
    var B=$(".alert li:last");
    var C=$(".alert li:first");
    setInterval(function(){
        if(B.is(":visible")){
            C.fadeIn(800).addClass("in");B.hide()
        } else {
            $(".alert li:visible").addClass("in");
            $(".alert li.in").next().fadeIn(800);
            $("li.in").hide().removeClass("in")}
    },3000); // 每3秒钟切换一条，你可以根据需要更改
});