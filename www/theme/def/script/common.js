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
    $.show_poprelay = function () {//{{{
        $('#poprelay').remove();
        $('body').append('<div id="poprelay"></div>');
        var poprelay = $('#poprelay');
        poprelay.show().height($(document).height()).width($(document).width());
    };//}}}
    $.hide_poprelay = function () {//{{{
        $('#poprelay').remove();
    };//}}}
    $.fn.mbx_pop = function () {
        $(this).each(function () {
            var $this = $(this);
            var link = $this.find('a').first();
            var load = false;
            var pop;
            var load_info = function () {
                var path = link.attr('href');
                var path = path.replace(/\/view\//, '/ajax/siblings-');
                var data;
                $.ajax({
                    url: path,
                    async: false,
                    dataType: "json",
                    success: function (json) {
                        if (!json) {
                            load = false;
                            return;
                        }
                        data = json;
                        load = true;
                    },
                    error: function () {
                        load = false;
                    }
                });
                var height = $this.height();
                pop = $('<div />');
                pop.css({
                    'position': 'absolute',
                    'width': '200px',
                    'top': height + 'px',
                    'left': '50%',
                    'background-color': '#02236e',
                    'margin-left': '-100px'
                });

                var len = data.length;
                for (var i = 0; i < len; i++) {
                    if (data[i]['name'] !== link.html()) {
                        var a = $('<a />');
                        a.attr('href', '/book/view/' + data[i]['path']).html(data[i]['name']);
                        a.css({
                            'display': 'block',
                            'color': 'white',
                            'text-align': 'center',
                            'line-height': '20px',
                            'padding': '3px 0',
                            'border-bottom': '1px solid black'
                        });
                        pop.append(a);
                    }
                }
                $this.append(pop);
            };

            $this.mouseenter(function () {
                if (load === false) {
                    load = true;
                    load_info();
                }
                pop.show();
            });
            $this.mouseleave(function () {
                pop.hide();
            });
        });
    };
    $(document).ready(function () {//{{{
        $('a.ajax').ajax_link();
        $('#page pre code').each(function (i, block) {
            hljs.highlightBlock(block);
        });
        $('#mbx .dir').mbx_pop();
    });//}}}
})(jQuery);
