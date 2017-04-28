// Awesome js classes, source: http://joshgertzen.com/object-oriented-super-class-method-calling-with-javascript/
function Class() { }

Class.prototype.construct = function () {};

Class.__asMethod__ = function (func, superClass) {
    return function () {
        var currentSuperClass = this.$;

        this.$  = superClass;
        var ret = func.apply(this, arguments);
        this.$  = currentSuperClass;
        return ret;
    };
};

Class.extend = function (def) {
    var classDef = function () {
        if (arguments[0] !== Class) {
            this.construct.apply(this, arguments);
        }
    };

    var proto      = new this(Class);
    var superClass = this.prototype;

    for (var n in def) {
        var item = def[n];

        if (item instanceof Function) {
            item = Class.__asMethod__(item, superClass);
        }

        proto[n] = item;
    }

    proto.$ = superClass;

    classDef.prototype = proto;

    //Give this new class the same static extend method
    classDef.extend = this.extend;
    return classDef;
};

$.fn.highlight = function (pat) {
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

function capitalize(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

var keyCode = {
    BACKSPACE: 8, COMMA: 188, DELETE: 46, DOWN: 40, END: 35, ENTER: 13, ESCAPE: 27, HOME: 36, LEFT: 37,
    PAGE_DOWN: 34, PAGE_UP: 33, PERIOD: 190, RIGHT: 39, SPACE: 32, TAB: 9, UP: 38, SHIFT: 16, S: 83
};