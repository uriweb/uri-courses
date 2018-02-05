// https://code.tutsplus.com/tutorials/guide-to-creating-your-own-wordpress-editor-buttons--wp-30182

(function() {

	function renderCourse( shortcode ) {
		var parsed, safeData, classes, out;

		parsed = URICOURSES.parseShortCodeAttributes( shortcode );

		return shortcode;

		safeData = window.encodeURIComponent( shortcode );
		classes = 'mceNonEditable uri-courses';
		
// 		out = '<div data-shortcode="' + safeData + '"';
// 		out += ' class="' + classes + '">';
// 		if(!parsed.subject) { parsed.text = 'COURSE LIST FOR ' + parsed.subject; }
// 		out += shortcode + '</div>';
// 		
// 		return out;
	}


	
	function restoreButtonCourse( content ) {
		var html, els, i, t;
		
		// convert the content string into a DOM tree so we can parse it easily
		html = document.createElement('div');
		html.innerHTML = content;
		els = html.querySelectorAll('.uri-courses');
		
		for(i=0; i<els.length; i++) {
			t = document.createTextNode( window.decodeURIComponent(els[i].getAttribute('data-shortcode')) );
			els[i].parentNode.replaceChild(t, els[i]);
		}
		
		//return the DOM tree as a string
		return html.innerHTML;
	}

	
	function generateCourseShortcode(params) {
		var attributes = [];
		for(i in params) {
			attributes.push(i + '="' + params[i] + '"');
		}
		return '[courses ' + attributes.join(' ') + ']';
	}


	tinymce.create('tinymce.plugins.uri_courses_button', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {

			// add the button that the WP plugin defined in the mce_buttons filter callback
			// @todo use a dashicon: https://wordpress.stackexchange.com/questions/138167/how-to-use-the-new-dashicons-for-custom-tinymce-buttons
			ed.addButton('uri_courses_button', {
				title : 'Courses',
				text : '',
				cmd : 'CoursesButton',
				image : url + '/i/courses@2x.png'
			});
					
			// add a js callback for the button
			ed.addCommand('CoursesButton', function(args) {
			
				// create an empty object if args is empty
				if(!args) {
					args = {}
				}
				// create an empty property so nothing is null
				var possibleArgs = ['subject'];
				possibleArgs.forEach(function(i){
					if(!args[i]) {
						args[i] = '';
					}
				});
				// prevent nested quotes... escape / unescape instead?
				args = URICOURSES.unEscapeQuotesDeep(args);

				ed.windowManager.open({
					title: 'Insert / Update Course List',
					body: [
						{type: 'textbox', name: 'subject', label: 'Subject Code', value: args.subject, placeholder: 'AAF'},
					],
					onsubmit: function(e) {
						// Insert content when the window form is submitted
						e.data = URICOURSES.escapeQuotesDeep(e.data);						
						shortcode = generateCourseShortcode(e.data);
						ed.execCommand('mceInsertContent', 0, shortcode);
					}
				},
				{
					wp: wp,
				});

			});

			ed.on( 'BeforeSetContent', function( event ) {
				event.content = URICOURSES.replaceShortcodes( event.content, 'uri-courses', true, renderCourse );
			});

			ed.on( 'PostProcess', function( event ) {
				if ( event.get ) {
					event.content = restoreButtonCourse( event.content );
				}
			});

		},


		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
				return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'URI Courses',
				author : 'John Pennypacker',
				authorurl : 'https://today.uri.edu',
				infourl : 'https://www.uri.edu/communications',
				version : "0.1"
			};
		}


	});

	// Register plugin
	tinymce.PluginManager.add( 'uri_courses_button', tinymce.plugins.uri_courses_button );


})();
