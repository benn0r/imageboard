/*!
 * title manipulator
 * 
 * Copyright (C) 2012 benn0r <benjamin@benn0r.ch>
 * MIT Licensed
 */
(function($) {
	$.fn.title = function(action, title) {
		
		switch (action) {
			// init title plugin
			case 'init':
				// set default title
				this.data('default', 
						title ? title : this.attr('title'));
				break;
				
			// set new page title
			case 'set':
				this.attr('title', title);
				break;
			
			// append text to the title
			case 'append':
				this.attr('title', this.attr('title') + title);
				break;
			
			// prepend text to the title 
			case 'prepend':
				this.attr('title', title + this.attr('title'));
				break;
			
			// reset title to default
			case 'reset':
				this.attr('title', this.data('default'));
				break;
		}

	};
})(jQuery);