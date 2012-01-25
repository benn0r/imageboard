function shoutbox_close() {	
	document.cookie= "hidechat=1";
	
	$('.content').removeClass('sb-extended');
	return false;
}