formNameStack('formNameSelectMultipleSubmit()');

var formNameSelectMultipleArray = new Array();

function formNameSelectMultipleSubmit() {
    var i, j;
    for (i = 0; i < formNameSelectMultipleArray.length; i++) {
        select = document.getElementById(formNameSelectMultipleArray[i]);
        for (j = 0; j < select.options.length; j++) {
            select.options[j].selected = true;
        }
    }
}

function formNameSelectMultipleMove(from, to, sort, callback) {
    var from_element = document.getElementById(from), to_element = document.getElementById(to), o = new Array(), i;
    if (from_element.options.length > 0) {
        for (i = 0; i < to_element.options.length; i++) {
            o[o.length] = new Option(to_element.options[i].text, to_element.options[i].value);
        }
        i = 0; do {
            if (from_element.options[i].selected) {
                o[o.length] = new Option(from_element.options[i].text, from_element.options[i].value, from_element.options[i].defaultSelected, from_element.options[i].selected);
                from_element.options[from_element.selectedIndex] = null;
            } else i++;
        } while (i < from_element.options.length);
        if (sort) o.sort(function (a,b) { if ((a.text + "") < (b.text + "")) return -1; if ((a.text + "") > (b.text + "")) return 1; return 0; });
        for (i = 0; i < o.length; i++) to_element.options[i] = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
    }
    if (arguments.length == 4) {
        callback.call(from_element);
    }
}

function formNameSelectMultipleSortUp(select) {
    var select_element = document.getElementById(select), o = select_element.options, s = -1, i, j, t1, t2;
    for (i = 0; i < select_element.options.length; i++) {
        j = i - 1; if (o[i].selected) {
            if (j > s) {
                t1 = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
                t2 = new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);
                o[i] = t2; o[j] = t1;
            } else s = i;
        }
    }
}

function formNameSelectMultipleSortDown(select) {
    var select_element = document.getElementById(select), o = select_element.options, s = o.length, i, t1, t2;
    for (i = (o.length - 1); i > -1; i = i - 1) {
        j = i + 1; if (o[i].selected) {
            if (j < s) {
                t1 = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
                t2 = new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);
                o[i] = t2; o[j] = t1;
            } else s = i;
        }
    }
}