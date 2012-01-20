/*!
 * storage for last notification id
 * 
 * @var int
 */
var lastn = 0;

/*!
 * called every x seconds to check for new notifications
 */
var notificationsTimer = setInterval(notifications = function notifications() {
	$.getJSON(BASE_URL + 'notifications/?nid=' + lastn, function(data) {
		
		if (data.users.length == 0) {
			// sorry, no active users
			$('.mainpage-users-container').hide();
		} else {
			$('.mainpage-users-container').show();
			$('.mainpage-users').html('');
			
			// add online users
			for (var i = 0; i < data.users.length; i++) {
				var u = data.users[i];
				$('.mainpage-users').append('<a onclick="return loadpage(this)" title="' + u.username + '" href="' + BASE_URL + 'user/' + u.uid + '/"><img src="' + u.avatar + '"</a>');
			}
		}

		// fetch notifications
		for (var i = 0; i < data.notifications.length; i++) {
			var n = data.notifications[i];

			$('.notifications-container .today').removeClass('hide');

			var html = $('<p>' + n.text + ' <small>' + n.inserttime + '</small></p>');
			if (n.unread) {
				html.addClass('unread');
			}

			var html2 = $('<p class="media-grid pull-left"><a href="' + n.thread + '" onclick="return loadpage(this)"><img src="' + n.thumbnail + '" /></a></p>');
			var html3 = $('<p class="span5 pull-left" style="margin-left:10px">' + n.content + '</p>');
			var html4 = $('<div style="clear:both"></div>');

			var parent = $('<div class="notification"></div>');
			parent.append(html);
			parent.append(html2);
			parent.append(html3);
			parent.append(html4);

			$('.notifications-container .today').after(parent);
			parent.hide();
			parent.fadeIn();
		}
		
		notificationRenderer();
		updateNotificationsCount();

		if (data.lastn > 0) {
			// set last notification id
			lastn = data.lastn;
		}
	});
}, 10000);

/*!
 * counts all unread notifications and add to counter
 */
function updateNotificationsCount() {
	var unread = $('.notifications-container .unread').size();
	if (unread > 0) {
		// change pagetitle
		$(document).title('reset');
		$(document).title('prepend', '(' + unread + ') ');
		$('.notifications .label').addClass('important');
	} else {
		// reset pagetitle, no unread notifications
		$(document).title('reset');
		$('.notifications .label').removeClass('important');
	}
	
	
	$('.notifications .label').html(unread);
}

/*!
 * renders notifications container (removes notifications
 * to fit container to screen)
 */
function notificationRenderer() {
	// real height
	var height = $(window).height() - 20 - $('.notifications-container').offset().top;

	var elemheight = 0;
	
	// count height from all elements in container
	$('.notifications-container .notification, .notifications-container h6').each(function() {
		if ($(this).height() + elemheight > height) {
			if ($(this).find('.unread').size() == 0) {
				// hide element which has no place
				$(this).hide();
			}
		} else {
			// element fits to container
			elemheight += $(this).height();
		}
	});
	
	// fix height
	$('.notifications-container').css('height', elemheight);
}

/*!
 * called if user clicks on notifications link
 */
function shownotifications() {
	if (!$('.notifications').hasClass('notifications-active')) {
		// show notifications
		$('.notifications').addClass('notifications-active');
		$('.notifications-container').show();
		 
		notificationRenderer();
	} else {
		// hide notifications
		$('.notifications').removeClass('notifications-active');
		$('.notifications-container').hide();
		
		// change state from all notifications
		$('.notifications-container .unread').removeClass('unread');
		$.get(BASE_URL + 'notifications/?read=1');
		
		updateNotificationsCount(); 
	}
}