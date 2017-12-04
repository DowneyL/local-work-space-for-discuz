i = 2;
$(function() {
    var fid = fidnum;
    var totalpage = Math.ceil(forumdisplaycount / forumdisplaynum);
    var winH = $(window).height();
    var extra = $("#extra").html();
    if($('.blog-list').length > 0 && totalpage > 1){
       $(window).scroll(function() {
            if (i <= totalpage) {
                var pageH = $(document.body).height();
                var scrollT = $(window).scrollTop();
                var aa = (pageH - winH - scrollT) / winH;
                $("#nodata").show().html("<img src='http://localhost/static/image/mobile/img/loading1.gif'/>");
                if (aa < 0.001) {
                    getJson(fid, i, extra);
                }
            } else {
                showEmpty();
            }
       });
    }else if( $('.blog-list').length > 0 && totalpage == 1 ){
        $("#nodata").css({"height":"98px", "line-height":"98px"});
        showEmpty();
    }

});

function getJson(id, page, extra) {
    $.getJSON('http://localhost/forum.php?mod=forumdisplay_json&fid='+ id +'&mobile=2&page=' + page, function(json) {
        if (json) {
            var str = "";
            var array = ['1','2','3','4']
            $.each(json, function(index, array) {
                var str = 	'<div class="col-md-6 col-sm-6">' +
                                '<div class="blog-list-item">' +
                                    '<div class="item-des">' +
                                    '<a href="forum.php?mod=viewthread&tid='+ array['tid'] +'&extra='+ extra +'" class="item-title" >'+array['subject']+'</a>' +
                                    '<div class="item-tag">';
                
                if(($.inArray(array['displayorder'], array)) + 1 != 0){
                     str = str + '<span class="item-top label label-danger">置顶</span>' +
                                 '<script>' +
                                 'var blogListItem = $(".blog-list-item");' +
                                 'blogListItem.attr(\'style\',\'background:#fff url("{STATICURL}image/mobile/img/bg04.jpg") top left no-repeat;\')</script>';
                }else if(array['digest'] > 0){
                    str = str + '<span class="label label-primary">精华</span>';
                }else if(array['attachment'] == 2 && mobilesimpletype == 0){
                    str = str + '<span class="label label-info">图文</span>';
                }

                    str = str + '</div>' +
                                '<div class="item-time">' +
                                    '<span><i class="fa fa-clock-o" aria-hidden="true"></i><a>'+ array['dateline']+'</a></span>' +
                                '</div><div class="summary">';
                if(array['price'] == 0 && array['readperm'] == 0){
                    str = str + array['message'];
                }else{
                    str = str + '内容隐藏需要，请点击进去查看';
                }
                    str = str + '</div></div>'+
                                '<div class="item-more">' +
                                '<span><i class="fa fa-user" aria-hidden="true"></i><a>' + array['author'] + '</a></span>' +
                                '<span><i class="fa fa-eye" aria-hidden="true"></i><a>' + array['views'] + '</a></span>' +
                                '<span><i class="fa fa-comment" aria-hidden="true"></i><a>' + array['replies'] + '</a></span>';
                if(array['highlight']){
                    str = str + '<span><i class="fa fa-flag" aria-hidden="true" style="color:#888"></i><a></a></span>';
                }
                    str = str + '</div><div class="clearfix"></div></div></div>';
                
                $(".blog-list").append(str);
            });
            $("#nodata").hide()
        } else {
            showEmpty();
        }
    });
    i++;
}

function showEmpty() {
    $("#nodata").show().html("暂无更多内容");
}
