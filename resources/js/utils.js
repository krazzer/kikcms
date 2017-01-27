$.fn.searchAble = function (onSearch) {
    var lastInput    = '';
    var lastSearch   = '';
    var $searchField = this;
    var $removeIcon  = $searchField.next('.glyphicon-remove');

    $removeIcon.click(function () {
        $searchField.val('');
        $searchField.trigger('keyup');
    });

    $searchField.on('keyup', function (e) {
        var currentSearch = $searchField.val();

        if (currentSearch == '') {
            $removeIcon.hide();
        } else {
            $removeIcon.show();
        }

        if (e.keyCode == keyCode.ENTER) {
            lastSearch = currentSearch;
            onSearch(currentSearch);
            return;
        }

        lastInput = currentSearch;

        setTimeout(function () {
            if (currentSearch == lastInput && currentSearch != lastSearch) {
                lastSearch = currentSearch;
                onSearch(currentSearch);
            }
        }, 500);
    });
};

$.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

var KikCmsClass = function () {
};

KikCmsClass.prototype =
{
    translations: {},
    errorMessages: {},
    isDev: false,

    action: function (actionUrl, parameters, onSuccess, onError) {
        var ajaxCompleted = false;
        var self          = this;
        var retries       = 0;

        setTimeout(function () {
            if (ajaxCompleted == false) {
                KikCMS.showLoader();
            }
        }, 250);

        var xmlHttpRequest = function () {
            $.ajax({
                url: actionUrl,
                type: 'post',
                dataType: 'json',
                data: parameters,
                success: function (result, responseText, response) {
                    ajaxCompleted = true;
                    self.hideLoader();

                    onSuccess(result, responseText, response);
                },
                error: function (result) {
                    // try again on connection failure
                    if (result.readyState == 0 && result.status == 0 && retries < 2) {
                        retries++;
                        xmlHttpRequest();
                        return;
                    }

                    ajaxCompleted = true;
                    self.showError(result, onError);
                }
            });
        };

        xmlHttpRequest();
    },

    showError: function (result, onError) {
        if (typeof(onError) != 'undefined') {
            onError();
        }

        this.hideLoader();

        var key = this.translations.error[result.status] ? result.status : 'unknown';

        if (this.isDev && result.status != 440) {
            $("#ajaxDebugger").html(result.responseText).show();
        } else {
            alert(this.translations.error[key].title + "\n\n" + this.translations.error[key].description);
        }
    },

    showLoader: function () {
        this.getLoader().addClass('show');
    },

    hideLoader: function () {
        this.getLoader().removeClass('show');
    },

    getLoader: function () {
        return $('#cmsLoader');
    },

    initWebForms: function ($element) {
        var self = this;

        $element.find('.webForm').each(function () {
            var $webForm = $(this);

            $webForm.find('.autocomplete').each(function () {
                var $field       = $(this);
                var fieldKey     = $field.attr('data-field-key');
                var webFormClass = $webForm.attr('data-class');

                self.action('/cms/webform/getAutocompleteData', {
                    field: fieldKey,
                    webFormClass: webFormClass
                }, function (data) {
                    $field.typeahead({
                        items: 30,
                        source: data
                    });
                });
            });
        });
    },

    tl: function (key, params) {
        var translation = this.translations[key];

        $.each(params, function (key, value) {
            translation = translation.replace(new RegExp(':' + key, 'g'), value);
        });

        return translation;
    }
};

var KikCMS = new KikCmsClass();

$(function () {
    KikCMS.initWebForms($(document));
});

var keyCode = {
    BACKSPACE: 8, COMMA: 188, DELETE: 46, DOWN: 40, END: 35, ENTER: 13, ESCAPE: 27, HOME: 36, LEFT: 37,
    PAGE_DOWN: 34, PAGE_UP: 33, PERIOD: 190, RIGHT: 39, SPACE: 32, TAB: 9, UP: 38
};

jQuery.fn.highlight = function (pat) {
    function innerHighlight(node, pat) {
        var skip = 0;
        if (node.nodeType == 3) {
            var pos = node.data.toUpperCase().indexOf(pat);
            pos -= (node.data.substr(0, pos).toUpperCase().length - node.data.substr(0, pos).length);
            if (pos >= 0) {
                var spannode       = document.createElement('span');
                spannode.className = 'highlight';
                var middlebit      = node.splitText(pos);
                middlebit.splitText(pat.length);
                var middleclone = middlebit.cloneNode(true);
                spannode.appendChild(middleclone);
                middlebit.parentNode.replaceChild(spannode, middlebit);
                skip = 1;
            }
        }
        else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
            for (var i = 0; i < node.childNodes.length; ++i) {
                i += innerHighlight(node.childNodes[i], pat);
            }
        }
        return skip;
    }

    return this.length && pat && pat.length ? this.each(function () {
        innerHighlight(this, pat.toUpperCase());
    }) : this;
};