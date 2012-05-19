formNameStack('formNameSelectMultipleSubmit()');

var formNameSelectMultipleArray = new Array();

function formNameSelectMultipleSubmit() {
    for (var i = 0; i < formNameSelectMultipleArray.length; i++) {
        select = document.getElementById(formNameSelectMultipleArray[i]);
        for (var j = 0; j < select.options.length; j++) {
            select.options[j].selected = true;
        }
    }
}

function formNameSelectMultipleMove(from, to, sort) {
    var from = document.getElementById(from);
    var to = document.getElementById(to);
    var o = new Array();
    if (from.options.length > 0) {
        for (var i = 0; i < to.options.length; i++) {
            o[o.length] = new Option(to.options[i].text, to.options[i].value);
        }
        var i = 0; do {
            if (from.options[i].selected) {
                o[o.length] = new Option(from.options[i].text, from.options[i].value, from.options[i].defaultSelected, from.options[i].selected);
                from.options[from.selectedIndex] = null;
            } else i++;
        } while (i < from.options.length);
        if (sort) o.sort(function (a,b) { if ((a.text + "") < (b.text + "")) return -1; if ((a.text + "") > (b.text + "")) return 1; return 0; });
        for (var i = 0; i < o.length; i++) to.options[i] = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
    }
}

function formNameSelectMultipleSortUp(select) {
    var select = document.getElementById(select);
    var o = select.options;
    var s = -1;
    for (var i = 0; i < select.options.length; i++) {
        j = i - 1; if (o[i].selected) {
            if (j > s) {
                var t1 = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
                var t2 = new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);
                o[i] = t2; o[j] = t1;
            } else s = i;
        }
    }
}

function formNameSelectMultipleSortDown(select) {
    var select = document.getElementById(select);
    var o = select.options;
    var s = o.length;
    for (var i = (o.length - 1); i > -1; i = i - 1) {
        j = i + 1; if (o[i].selected) {
            if (j < s) {
                var t1 = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
                var t2 = new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);
                o[i] = t2; o[j] = t1;
            } else s = i;
        }
    }
}