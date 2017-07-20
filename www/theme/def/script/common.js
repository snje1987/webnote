(function ($) {
    $.fn.ajax_link = function () {//{{{
        $(this).each(function () {
            var click = false;
            var $this = $(this);
            $this.click(function () {
                if (click === true) {
                    return;
                }
                var href = $this.attr('href');
                var question = $this.attr('question');
                click = true;
                if (typeof question !== 'undefined' && question !== "") {
                    if (!confirm(question)) {
                        return false;
                    }
                }
                $.show_poprelay();
                $.ajax({
                    url: href,
                    async: true,
                    dataType: "json",
                    success: function (data) {
                        click = false;
                        if (!data) {
                            $.hide_poprelay();
                            alert('发生错误');
                            return;
                        }
                        if (data.msg) {
                            alert(data.msg);
                        }
                        if (data.returl && data.returl !== '') {
                            window.location = data.returl;
                        } else {
                            location.reload();
                        }
                    },
                    error: function () {
                        click = false;
                        $.hide_poprelay();
                        alert('发生错误');
                    }
                });
                return false;
            });
        });
    };//}}}
    $.fn.fsubmit = function (options) {//{{{
        var defaults = {
            code: '',
            callback: '',
            check: ''
        };
        $.extend(defaults, defaults, options);
        $(this).each(function (index) {
            var img = null;
            var code_show = false;
            var codesrc = '';
            if (defaults.code !== '') {
                var codebox = $(defaults.code).eq(index);
                var input = codebox.find('input').first();
                codesrc = input.attr('codesrc');
                input.focus(function () {
                    if (code_show === false) {
                        code_show = true;
                        input.val('');
                        input.css({'color': '#000000'});

                        img = $('<img />');
                        img.attr('title', '点击更换验证码');
                        img.attr('src', codesrc);
                        img.click(function () {
                            img.attr('src', codesrc + '?' + Math.random());
                        });
                        img.css({
                            'cursor': 'pointer'
                        });
                        input.after(img);
                    }
                });
            }
            var $this = $(this);
            var submited = false;
            $this.ajaxForm({
                dataType: 'json',
                beforeSerialize: function ($form, options) {
                    $.show_poprelay();
                    if (submited === true) {
                        $.hide_poprelay();
                        return false;
                    }
                    if (defaults.check !== '') {
                        if (!defaults.check()) {
                            $.hide_poprelay();
                            return false;
                        }
                    }
                    submited = true;
                    return true;
                },
                success: function (data) {
                    if (!data) {
                        $.hide_poprelay();
                        alert('发生错误');
                        submited = false;
                        return;
                    }
                    if (data.succeed === true) {
                        if (data.msg) {
                            alert(data.msg);
                        }
                        if (defaults.callback !== '') {
                            defaults.callback(data);
                        }
                        if (data.returl && data.returl !== '') {
                            window.location = data.returl;
                            return;
                        } else {
                            location.reload();
                            return;
                        }
                    } else {
                        if (data.msg) {
                            alert(data.msg);
                        }
                        if (img !== null) {
                            img.attr('src', codesrc + '?' + Math.random());
                        }
                        submited = false;
                        $.hide_poprelay();
                    }
                },
                error: function () {
                    $.hide_poprelay();
                    alert('发生错误');
                    submited = false;
                }
            });
        });
    };//}}}
    $.fn.myeditor = function (options) {//{{{
        var defaults = {
            toolbar: '',
            info: ''
        };
        $.extend(defaults, defaults, options);
        $(this).each(function (index) {
            var $this = $(this);
            var toolbar = '';
            var info = '';
            var calc_word = function () {
                if (info === '') {
                    return;
                }
                var str = $this.val();
                str = str.replace(/(\s|　)/g, '');
                info.html('当前字数：' + str.length);
            };
            if (defaults.info !== '') {
                info = $(defaults.info).eq(index);
                calc_word();
                $this.keyup(function () {
                    calc_word();
                });
            }
            if (defaults.toolbar !== '') {
                toolbar = $(defaults.toolbar).eq(index);
                var autoformat = $('<a class="btn btn-success">整理格式</a>');
                autoformat.click(function () {
                    var str = $this.val();
                    str = str.replace(/　/g, '');
                    str = str.replace(/( |\t)+$/mg, '');
                    $this.val(str);
                    calc_word();
                });
                toolbar.append(autoformat);
            }
        });
    };//}}}
    $.show_poprelay = function () {//{{{
        $('#poprelay').remove();
        $('body').append('<div id="poprelay"></div>');
        var poprelay = $('#poprelay');
        poprelay.show().height($(document).height()).width($(document).width());
    };//}}}
    $.hide_poprelay = function () {//{{{
        $('#poprelay').remove();
    };//}}}
    $(document).ready(function () {//{{{
        $('a.ajax').ajax_link();
        $('#page pre code').each(function (i, block) {
            hljs.highlightBlock(block);
        });
        var listCache = {};
        $('#listModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var path = button.data('path');
            var modal = $(this);
            if (!(path in listCache)) {
                $.ajax({
                    url: path,
                    async: false,
                    dataType: "html",
                    success: function (html) {
                        listCache[path] = html;
                        modal.find('.modal-body').html(listCache[path]);
                    }
                });
            } else {
                modal.find('.modal-body').html(listCache[path]);
            }

        });
    });//}}}
})(jQuery);
