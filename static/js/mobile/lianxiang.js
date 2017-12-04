jQuery(function ($) {
    var kouei_search = $("#scbar_txt");
    kouei_search.focus(function () {
        this.placeholder = '';
    });
    kouei_search.keyup(function () {
        var word = $(this).val();

    });

    $.post("search.php?searchsubmit=yes", {
        mod : 'forum',
        formhash : '277a0deb',
        srchtype : 'title',
        srchlocality : 'forum::index',
        searchsubmit : true
    }, function (data){
        console.log(data);
        $.ajax({
            url: data,
            dataType:'jsonp',
            success:function(txt){
                console.log(txt);
//                $.each(txt,function(tid,val){
//                    console.log(val.subject);
//                });
            }});
    });

//        $.ajax({
//            url:'search.php?searchsubmit=yes',
//            dataType:'jsonp',
//            success:function(txt){
//                console.log(txt);
////                $.each(txt,function(tid,val){
////                    console.log(val.subject);
////                });
//            }})
})