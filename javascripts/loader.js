/*!
 * called from onclick event of links or buttons
 * 
 * @param link string
 * @returns false
 */
function loadpage(link) {
	return loadlink($(link).attr('href'));
}

/*!
 * called with a link
 * 
 * @param uri link
 * @returns false
 */
function loadlink(uri) {
	// hide all twipsy elements
	$('*[rel=twipsy]').twipsy('hide');
	
	History.pushState({rand: Math.random()}, $(document).attr('title'), uri);
	return false;
}

/*!
 * 
 * 
 * @param uri string
 * @param data serialized data for post
 * @returns
 */
function postlink(uri, data) {			
	var link = uri.replace(BASE_URL, '');

	lastlink = link;

	if (typeof history.pushState != 'undefined') {
		history.pushState({foo: 'bar'}, uri, uri);
	} else {
		document.location.href = '#!' + link;
	}

	if (uri.indexOf('?') > 0) {
		uri = uri + '&ajax=1';
	} else {
		uri = uri + '/?ajax=1';
	}

	$.post(uri, data, function(result) {
		loadlink(result);
	});

	return false;
}
function submitform(form, target) {
	var form = $(form);
	
	if (form.attr('method') == 'post') {
		$.post(form.attr('action'), form.serialize(), function(result) {
			target.html(result);
		});
	}

	// onsubmit cancel
	return false;
}

/*!
 * submits login form and checks if login is correct
 * 
 * @param form
 * @returns
 */
function submitlogin(form) {
	var form = $(form);

	if (form.find('input[type=text][value=]').size() > 0) {
		form.find('input[type=text][value=]').first().select();
		return false;
	}

	form.find('button').button('loading');
	form.find('.alert-message').remove();

	$.post(BASE_URL + 'login', $(form).serialize(), function(result) {
		if (result == 0) {
			$('#modal-loginerror').modal('show');

			// clear loginform
			form.find('input').val('');

			// reset submit button state
			form.find('button').button('reset');
		} else {
			// login successful
			document.location = document.location;
		}
	});
	return false;
}