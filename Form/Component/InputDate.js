function formNameUpdateDays(input) {
    var form, day, month, year, td, from, value, date, selectedValue;
    form = document.forms['formName'];
    day = form[input + '[day]'];
    month = form[input + '[year_month]'].value.substr(4, 2) - 1;
    year = form[input + '[year_month]'].value.substr(0, 4);
    if (year != 0 && month >= 0) {
        // 28 to 31 days
        td = new Date();
        from = 1;
        value = 0;
        if (value == 0) {
            date; value = 31; do {
                date = new Date(year, month, value--);
            } while (month < date.getMonth());
        }
        // Check if from stamp is greater than today + release
        if (month == td.getMonth() && year == td.getFullYear()) {
            from = (from > (td.getDate())) ? from : td.getDate();
        }
        // Update the selector
        selectedValue = day.value;
        while (day.options.length) day.options[0] = null;
        for (i = from; i < value + 2; i++) {
            option = new Option(((i > 9) ? i : '0' + i), i, false, false);
            day.options[j = day.length] = option;
            if (i == selectedValue) day.selectedIndex = j;
        }
    } else {
        while (day.options.length) day.options[0] = null;
        day.options[0] = new Option('...', null, false, false);
    }
}

function formNameUpdateDate(input, date) {
    var form, month, selectedValue, year_month, day, i;
    form = document.forms['formName'];
    month = date.getMonth() > 8 ? String(date.getMonth() + 1) : '0' + String(date.getMonth() + 1);
    selectedValue = String(date.getFullYear()) + month;
    year_month = form[input + '[year_month]'];
    for (i = 0; i < year_month.length; i++) {
        if (year_month.options[i].value == selectedValue) {
            year_month.options[i].selected = true;
            break;
        }
    } formNameUpdateDays(input);
    day = form[input + '[day]'];
    for (i = 0; i < day.length; i++) {
        if (day.options[i].value == date.getDate()) {
            day.options[i].selected = true;
            break;
        }
    }
}

function formNameUpdateNights(from_input, to_input, nights_input, from_calendar_input, to_calendar_input) {
    var form, from, to, nights;
    form = document.forms['formName'];
    from = new Date(form[from_input + '[year_month]'].value.substr(0, 4), form[from_input + '[year_month]'].value.substr(4, 2) - 1, form[from_input + '[day]'].value);
    to = new Date(form[to_input + '[year_month]'].value.substr(0, 4), form[to_input + '[year_month]'].value.substr(4, 2) - 1, form[to_input + '[day]'].value);
    nights = Math.round((to - from) / 86400000);
    form[nights_input].value = nights;
    formNameOnNightsChange(from_input, to_input, nights_input, from_calendar_input, to_calendar_input);
}

function formNameOnDayChange(input, from_input, to_input, nights_input, calendar_input, from_calendar_input, to_calendar_input) {
    var form, day, year_month, date;
    if (input == from_input) {
        formNameOnNightsChange(from_input, to_input, nights_input, from_calendar_input, to_calendar_input);
    } else if (nights_input != null) {
        formNameUpdateNights(from_input, to_input, nights_input, from_calendar_input, to_calendar_input);
    } else {
        form = document.forms['formName'];
        day = Number(form[input + '[day]'].value);
        year_month = form[input + '[year_month]'];
        date = new Date(year_month.value.substr(0, 4), year_month.value.substr(4, 2) - 1, day);
        $('#' + calendar_input).val(date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate());
    }
}

function formNameOnMonthChange(input, from_input, to_input, nights_input, calendar_input, from_calendar_input, to_calendar_input) {
    formNameUpdateDays(input);
    formNameOnDayChange(input, from_input, to_input, nights_input, calendar_input, from_calendar_input, to_calendar_input);
}

function formNameOnNightsChange(from_input, to_input, nights_input, from_calendar_input, to_calendar_input) {
    var form, from_day, to_day, year_month, from_date, to_date;
    form = document.forms['formName'];
    if (form[nights_input].value < 1) form[nights_input].value = 1;
    if (form[nights_input].value > 90) form[nights_input].value = 90;
    from_day = Number(form[from_input + '[day]'].value);
    to_day = from_day + Number(form[nights_input].value);
    year_month = form[from_input + '[year_month]'];
    from_date = new Date(year_month.value.substr(0, 4), year_month.value.substr(4, 2) - 1, from_day);
    to_date = new Date(year_month.value.substr(0, 4), year_month.value.substr(4, 2) - 1, to_day);
    formNameUpdateDate(to_input, to_date);
    $('#' + from_calendar_input).val(from_date.getFullYear() + '-' + (from_date.getMonth() + 1) + '-' + from_date.getDate());
    $('#' + to_calendar_input).val(to_date.getFullYear() + '-' + (to_date.getMonth() + 1) + '-' + to_date.getDate());

}

function formNameOnCalendarChange(input, from_input, to_input, nights_input, calendar_input, from_calendar_input, to_calendar_input) {
    var new_date = jQuery('#' + calendar_input).val().split('-');
    jQuery(':input[name=\'' + input + '[year_month]\']').val(new_date[0] + new_date[1]);
    formNameUpdateDays(input);
    jQuery(':input[name=\'' + input + '[day]\']').val(new_date[2]);
    formNameOnDayChange(input, from_input, to_input, nights_input, calendar_input, from_calendar_input, to_calendar_input);
}