// The number of milliseconds in one day
var ONE_DAY = 1000 * 60 * 60 * 24
// dateformat which gets used
var dateFormat = "%Y-%m-%d";

function days_between(date1, date2) {
    // Convert both dates to milliseconds
    var date1_ms = date1.getTime();
    var date2_ms = date2.getTime();
    // Calculate the difference in milliseconds
    var difference_ms = date2_ms - date1_ms;
    // Convert back to days and return
    return Math.ceil(difference_ms/ONE_DAY);
}

function generatePassword() {
    var length = 6,
        charset = "abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
        retVal = "";
    for (var i = 0, n = charset.length; i < length; ++i) {
        retVal += charset.charAt(Math.floor(Math.random() * n));
    }
    return retVal;
}


function updateDaysFromDates(i, currentDate, expDate) {
    if (days_between(currentDate, expDate) < 1) // only update positive days
        return;
    $("_subloginsublogins_row_"+i+"_days_to_expire").value = days_between(currentDate, expDate);
    $("_subloginsublogins_row_"+i+"_expire_date").value = expDate.print(dateFormat);
}
function updateDateFromDays(i, currentDate, days) {
    if (days == "" || !parseInt(days))
        $("_subloginsublogins_row_"+i+"_expire_date").value = "";
    else {
        var newDate = new Date(currentDate.getTime() + days * ONE_DAY);
        $("_subloginsublogins_row_"+i+"_expire_date").value = newDate.print(dateFormat);
    }
}

origTableInputAddItemCallbackAfter = tableInputAddItemCallbackAfter;

tableInputAddItemCallbackAfter = function(obj, alreadyInserted)
{
    var id = obj.itemsCount;
    // register calendar-handler for each field
    if ($("_subloginsublogins_row_"+obj.itemsCount+"_expire_date"))
    {
		Calendar.setup({
			inputField : "_subloginsublogins_row_"+obj.itemsCount+"_expire_date",
			ifFormat : dateFormat,
			button : "_subloginsublogins_row_"+obj.itemsCount+"_expire_date_trig",
			showsTime: false,
			align : "Bl",
			singleClick : true,
			onSelect: function(calObj, printedDate){
				// when selecting a date with the calender update the days_to_expire field
				updateDaysFromDates(id, currentDate, calObj.date);
			}
		});
		// register days_to_expire handler - when editing this field the expire_date should be updated
		$("_subloginsublogins_row_"+id+"_days_to_expire").observe('change', function(event) {
			var id = $(this).readAttribute("rel");
			var days = $("_subloginsublogins_row_"+id+"_days_to_expire").value;
			updateDateFromDays(id, currentDate, days);
		});
	
		if (alreadyInserted)
		{
			var exp_date = Date.parseDate($("_subloginsublogins_row_"+id+"_expire_date").value, dateFormat);
			updateDaysFromDates(id, currentDate, exp_date);
		}
		else
		{
			$("_subloginsublogins_row_"+id+"_password").value = generatePassword();
			$("_subloginsublogins_row_"+id+"_days_to_expire").value = 90;
			
			if ($("_subloginsublogins_row_"+id+"_active"))
				$("_subloginsublogins_row_"+id+"_active").checked = true;
			if ($("_subloginsublogins_row_"+id+"_send_backendmails"))
				$("_subloginsublogins_row_"+id+"_send_backendmails").checked = true;
			if ($("_subloginsublogins_row_"+id+"_create_sublogins"))
				$("_subloginsublogins_row_"+id+"_create_sublogins").checked = false;
			
			updateDateFromDays(id, currentDate, 90);
		}

		if (origTableInputAddItemCallbackAfter)
			return origTableInputAddItemCallbackAfter(obj);
	}
    return true;
}

/** when an inactive field gets activated - set the days back to 90 */
document.observe('change', function(e, el) {
    var el = e.findElement('.active');
    if (el) {
        el = $(el);
        if (el.checked) {
            var id = el.readAttribute("rel");
            $("_subloginsublogins_row_"+id+"_days_to_expire").value = 90;
            var days = $("_subloginsublogins_row_"+id+"_days_to_expire").value;
            updateDateFromDays(id, currentDate, days);
        }
    }
});


