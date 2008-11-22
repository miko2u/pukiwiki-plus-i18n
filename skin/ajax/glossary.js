//
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: glossary.js,v 1.2 2008/11/22 20:25:00 upk Exp $
// Copyright (C)
//   2005,2008 PukiWiki Plus! Team
//
// Glossary Popup function
// Usage: onmouseover="showGlossaryPopup(url,event)" onmouseout="hideGlossaryPopup()"

popupDiv = false;
tUrl = "";	// temporary URL
gUrl = "";	// global URL
egX = 0;
egY = 0;
ecX = 0;
ecY = 0;

///////////////////////////////////////
// Show Popup Glossary
function showGlossaryPopup(url,ev)
{
	// DOM Not Implemented
	if (!document.createElement) { return; }
	// Page Not Loaded
	if (!document.getElementById('popUpContainer')) {
		return;
	}

	if (!popupDiv || url != gUrl) {
		tUrl = url;
		if (document.all) {
			ecX = event.clientX;
			ecY = event.clientY;
		} else {
			egX = ev.pageX;
			egY = ev.pageY;
		}
		popup_onload = function(htmldoc) {
			hideGlossaryPopup();
			gUrl = tUrl;
			var x_adjust = 0;
			var y_adjust = 0;
			if (!popupDiv) {
				popupDiv = document.createElement('div');
				popupDiv.setAttribute('id', 'ajaxpopup');
				if (document.all) {
					var body = (document.compatMode=='CSS1Compat') ? document.documentElement : document.body;
					popupDiv.style.pixelLeft = body.scrollLeft + ecX + x_adjust;
					popupDiv.style.pixelTop = body.scrollTop + ecY + y_adjust;
				} else if (document.getElementById) {
					popupDiv.style.left = egX + x_adjust + "px";
					popupDiv.style.top = egY + y_adjust + "px";
				}
				popupDiv.innerHTML = htmldoc.responseText;
				popupDiv.onclick = hideGlossaryPopup;
				var popUpContainer = document.getElementById("popUpContainer");
				if (popUpContainer) {
					popUpContainer.appendChild(popupDiv);
				} else {
					document.body.appendChild(popupDiv);
				}
			}
		}
		var html = new TextLoader(popup_onload,null);
		html.load(url,null);
	}
}

///////////////////////////////////////
// Hide Popup Glossary
function hideGlossaryPopup()
{
	// DOM Not Implemented
	if (!document.createElement) { return; }

	if (popupDiv) {
		popupDiv.style.visibility = "hidden";
		popupDiv.parentNode.removeChild(popupDiv);
		popupDiv = false;
	}
}
