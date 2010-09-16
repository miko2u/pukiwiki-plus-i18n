// White Papers
// Time Zone Calculation
// http://www.desisoftsystems.com/white-papers/timeZoneCalculation/
//
// Conclusion
// In the interest of promoting open standards, you are free to use 
// the source code in this white paper and the code available in this JavaScript source file:
//
// http://www.desisoftsystems.com/tzCalculation_LocalTimeZone.js
//
// to implement this solution to this world-wide problem.
//
function tzCalculation_LocalTimeZone (
	theDomainForTheCookie,
	printTheTimeZone)
{	// Beginning of tzCalculation_LocalTimeZone
    var rightNow;
    var rightNow_UTC_MillisecondsSinceTimBegan;
    var rightNow_Local_MillisecondsSinceTimBegan;
    var rightNow_MillisecondsDifference;
    var rightNow_MinutesDifference;
    var rightNow_timeZoneString;
    var rightNow_MinutesPart;
    var rightNow_HoursDifference;

	rightNow = new Date();

	rightNow_UTC_MillisecondsSinceTimBegan = Date.UTC (
		rightNow.getUTCFullYear (),
		rightNow.getUTCMonth (),
		rightNow.getUTCDate (),
		rightNow.getUTCHours (),
		rightNow.getUTCMinutes (),
		rightNow.getUTCSeconds ()
		);
	rightNow_Local_MillisecondsSinceTimBegan = Date.UTC (
		rightNow.getFullYear (),
		rightNow.getMonth (),
		rightNow.getDate (),
		rightNow.getHours (),
		rightNow.getMinutes (),
		rightNow.getSeconds ()
		);

	rightNow_MillisecondsDifference = rightNow_Local_MillisecondsSinceTimBegan - rightNow_UTC_MillisecondsSinceTimBegan;

	rightNow_MinutesDifference = (rightNow_MillisecondsDifference / 1000) / 60;

	if (0 > rightNow_MinutesDifference)
	{
		rightNow_timeZoneString = "-";
	}
	else
	{
		rightNow_timeZoneString = "+";
	}

	rightNow_MinutesPart = rightNow_MinutesDifference % 60;

	if (rightNow_MinutesPart != 0)
	{
		rightNow_MinutesDifference -= rightNow_MinutesPart;

		if (0 > rightNow_MinutesPart)
		{
			rightNow_MinutesPart = Math.abs (rightNow_MinutesPart);
		}
	}

	if (0 > rightNow_MinutesDifference)
	{
		rightNow_MinutesDifference = Math.abs (rightNow_MinutesDifference);
	}

	rightNow_HoursDifference = rightNow_MinutesDifference / 60;

	if (10 > rightNow_HoursDifference)
	{
		rightNow_timeZoneString = rightNow_timeZoneString + '0';
	}

	rightNow_timeZoneString = rightNow_timeZoneString + rightNow_HoursDifference;

	if (10 > rightNow_MinutesPart)
	{
		rightNow_timeZoneString = rightNow_timeZoneString + '0';
	}

	rightNow_timeZoneString = rightNow_timeZoneString + rightNow_MinutesPart;

	if (printTheTimeZone)
	{
		document.writeln (rightNow_timeZoneString);
	}

	document.cookie = 'timezone=' + rightNow_timeZoneString + '; domain=' + theDomainForTheCookie;

	return rightNow_timeZoneString;
}	// End of tzCalculation_LocalTimeZone
