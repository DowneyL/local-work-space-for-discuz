jQuery(function ($) {
    /*
     $.getUrlParam = function (uri, name) {
     var reg = new RegExp("(^|[&?])" + name + "=([^&]*)(&|$)");
     var r = uri.substr(1).match(reg);
     if (r != null) return encodeURI(r[2]); return null;
     };
     */
    $.setSearchHide = function (elem, flag) {
        elem.css('display', 'none');
        if (flag) {
            elem.find('ul').html('');
        }
    };

    $.setSearchPosition = function (srch_elem, list_elem) {
        var X = srch_elem.offset().left - 10;
        var Y = srch_elem.offset().top + 28;
        //alert(X + ' | ' + Y);
        list_elem.css({'left': X + 'px', 'top': Y + 'px'});
    };

    $.setPositionHandle = function (srch_elem, list_elem, plist_elem) {
        var klist_length = srch_elem.length;
        if (klist_length > 1) {
            $.setSearchPosition($(srch_elem[0]), list_elem);
            $.setSearchPosition($(srch_elem[1]), plist_elem);
        } else {
            $.setSearchPosition(srch_elem, list_elem);
        }
    };

    $.getData = function (srch_elem, list_elem, formhash) {
        srch_elem.focusin(function () {
            this.placeholder = '';
            var li_length = list_elem.find('ul').find('li').length;
            if (this.value.length > 0 && li_length > 0) {
                list_elem.slideDown('fast');
            }
        });

        srch_elem.focusout(function () {
            if (this.value.length > 0) {
                $.setSearchHide(list_elem, 0);
            }
        });

        srch_elem.keyup(function () {
            var word = $.trim($(this).val());
            var search_input = list_elem.find('input[name=srchtxt]');
            search_input.val(word);
            word = encodeURI(word);
            // console.log(word);
            if (word.length > 0) {
                $.post("search.php?searchsubmit=yes", {
                    mod: 'forum_json',
                    formhash: formhash,
                    srchtype: 'title',
                    srchlocality: 'forum::index',
                    srchtxt: word,
                    searchsubmit: true
                }, function (data) {
                    // console.log(data);
                    var li = "";
                    var list_length = 0;
                    $.each(data, function (id, val) {
                        list_length++;
                        if (val.subject.length > 30) {
                            val.subject = val.subject.substr(0, 30) + '...';
                        }
                        li += "<li><a href='forum.php?mod=viewthread&tid=" + id + "&extra=page%3D1'>" + val.subject + "<span>" + val.replies + " ¸ö»Ø¸´</span></a></li>";
                    });
                    if (list_length) {
                        list_elem.find('ul.kslb').html(li);
                        list_elem.slideDown('fast');
                        var klist_a = list_elem.find('a');
                        var klist_btn = list_elem.find('button');
                        klist_a.mousedown(function () {
                            this.click();
                        });
                        klist_btn.mousedown(function () {
                            this.click();
                        });
                    } else {
                        $.setSearchHide(list_elem, 1);
                    }
                }, 'json');
            } else {
                $.setSearchHide(list_elem, 1);
            }
        });
    };

    var ksearch = $(".kouei_auto_search");
    var ksrch_length = ksearch.length;
    var klist = $('#kouei_search_list');
    var formhash = $('#scbar_form').find('input[name=formhash]').value;
    var kplist = $('#kouei_search_list_post');

    $.setPositionHandle(ksearch, klist, kplist);

    $(window).resize(function () {
        $.setPositionHandle(ksearch, klist, kplist);
    });

    if (ksrch_length > 1) {
        $.getData($(ksearch[0]), klist, formhash);
        $.getData($(ksearch[1]), kplist, formhash);
    } else if (ksrch_length = 1) {
        $.getData(ksearch, klist, formhash);
    } else {
        return '';
    }
});
