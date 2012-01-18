/**
 * storage for last notification id
 * 
 * @var int
 */
var lastn = 0;

/**
 * called every x seconds to check for new notifications
 */
var notificationsTimer = setInterval(notifications = function notifications() {
	$.getJSON(BASE_URL + 'notifications/?nid=' + lastn, function(data) {

		if (data.users.length == 0) {
			$('.mainpage-users-container').hide();
		} else {
			$('.mainpage-users-container').show();
			$('.mainpage-users').html('');
			
			for (var i = 0; i < data.users.length; i++) {
				var u = data.users[i];
				
				$('.mainpage-users').append('<a onclick="return loadpage(this)" title="' + u.username + '" href="' + BASE_URL + 'user/' + u.uid + '/"><img src="' + u.avatar + '"</a>');
			}
		}

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

		/*for (var i = 0; i < data.news.length; i++) {
			var n = data.news[i];

			var elem = $('<li><h5>' + n.inserttime + '</h5>' + n.text + '</li>');
			elem.hide();
			$('.mainpage-news').prepend(elem);
			elem.fadeIn('slow');
		}*/

		if (data.lastn > 0) {
			lastn = data.lastn;
		}
	});
}, 10000);

function updateNotificationsCount() {
	var unread = $('.notifications-container .unread').size();
	if (unread > 0) {
		$(document).title('reset');
		$(document).title('prepend', '(' + unread + ') ');
		$('.notifications .label').addClass('important');
	} else {
		$(document).title('reset');
		$('.notifications .label').removeClass('important');
	}
	$('.notifications .label').html(unread);
}

/**
 * renders notifications container (remove notifications
 * to fit container to screen)
 */
function notificationRenderer() {
	var height = $(window).height() - 20 - $('.notifications-container').offset().top;

	var elemheight = 0;
	$('.notifications-container .notification, .notifications-container h6').each(function() {
		if ($(this).height() + elemheight > height) {
			if ($(this).find('.unread').size() == 0) {
				$(this).hide();
			}
		} else {
			elemheight += $(this).height();
		}
	});
	
	console.log(height + ' ' + elemheight);

	$('.notifications-container').css('height', elemheight);
}

function shownotifications() {
	if (!$('.notifications').hasClass('notifications-active')) {
		 $('.notifications').addClass('notifications-active');
		 $('.notifications-container').show();
		 
		 notificationRenderer();
	} else {
		$('.notifications').removeClass('notifications-active');
		 $('.notifications-container').hide();
		 
		 $('.notifications-container .unread').removeClass('unread');
		 $.get(BASE_URL + 'notifications/?read=1');
		 updateNotificationsCount();
		 
	}
}