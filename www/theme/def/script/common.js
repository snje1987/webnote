(function ($) {
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
                    }
                });
            }
            modal.find('.modal-body').html(listCache[path]);
        })
    });//}}}
})(jQuery);
