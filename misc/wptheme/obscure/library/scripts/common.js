/*
 * all callback functions
*/

function clearTitleText() {
	jQuery(this).find("span").text("");
}
function typewriter() {
	var element = jQuery(this).find("span");
	var str = jQuery(this).find("img").attr("alt")
	var progress = 0;
	element.text('');
	var timer = setInterval(function() {
		element.text(str.substring(0, progress++) + (progress & 1 ? '' : ''));
		if (progress > str.length) clearInterval(timer);
	}, 100);
}