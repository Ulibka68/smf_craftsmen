/******************************************************************
/* Javascript for Yet Another Spoiler Mod
/*****************************************************************/
var YASM_viewed = [0];

function YASM_toggle(element, log)
{
	// Hide or show the spoiler area & links of this bbcode:
	var div1 = element.parentNode.parentNode;
	var div2 = div1.getElementsByTagName('div')[1].getElementsByTagName('div')[0];
	var hidden = (div2.style.display != '');
	div2.style.display = (hidden ? '' : 'none');
	var div3 = div1.getElementsByClassName('YASM_links');
	var links = div3[0].getElementsByTagName('a');
	links[0].style.display = (!hidden ? '' : 'none');
	links[1].style.display = (hidden ? '' : 'none');

	// We need to log this user as having seen it, if the poster has requested it:
	YASM_log(log);
}

function YASM_log(log)
{
	// C'mon!  Make sure we're trying to log a valid spoiler!
	if (log > 0)
	{
		// Have we logged it this page load?
		var idx = YASM_viewed.indexOf(log);
		if (idx = -1)
		{
			// Send the AJAX request to load the spoiler read:
			var ajax =	new XMLHttpRequest();
			ajax.open('GET', smf_scripturl + '?action=YASM_log;id=' + log, true);
			ajax.send();

			// Add this spoiler log to the list to prevent unnecessary calls:
			YASM_viewed.push(log);
		}
	}
}
