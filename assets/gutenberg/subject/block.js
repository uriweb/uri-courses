(function ( blocks, element, i18n ) {

	var el = element.createElement,
			registerBlockType = wp.blocks.registerBlockType,
			__ = wp.i18n.__,
			editable = wp.blocks.Editable;

	/**
	 * The preview that renders in the block editor
	 * @todo: make this editable
	 */
	function CoursesShortcodeEditorPreview( props ) {
		var lipsum = el( 'div', { className: 'uri-courses-lorem-ipsum' },
			el( 'h4', {}, 'Fusce-pharetra' ),
			el( 'p', {}, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus a tellus erat.' ),
			el( 'h4', {}, 'Cras-scelerisque' ),
			el( 'p', {}, 'Suspendisse lacinia leo metus, in facilisis ex eleifend non. Nulla pharetra pellentesque ante et ornare.' ),
		);
		
		return el( 'div', { 'data-subject': props.subject }, 
			el( 'div', { className: 'uri-courses-shortcode' }, '[courses subject="' + props.subject.toUpperCase() + '"]'),
			lipsum
		);
	}

	

	blocks.registerBlockType( 'uri-courses/by-subject', {
		title: 'URI Courses',
		icon: 'book-alt',
		category: 'widgets',
		attributes: {
			subject: {
				type: 'string',
				source: 'attribute',
				attribute: 'data-subject',
				selector: 'div',
			}
		},

		edit: function( props ) {
					
			var subject = props.attributes.subject,
			focusedEditable = props.focus ? props.focus.editable || 'subject' : null,
			content = [];

			/**
			 * Builds the form in the block editor
			 */
			function CoursesCreateForm( subject ) {
				// @todo use @wordpress/components for this instead of manually adding class names
				return el( 'form', { onSubmit: CoursesSetSubject, className: 'components-placeholder uri-courses-form ' + props.className },
					el( 'fieldset', { }, 
						el( 'label', { className:'components-placeholder__label'}, 'Course Code' ),
						el( blocks.RichText, {
							tagName: 'text',
							inline: false,
							className: 'components-placeholder__input input-control',
							multiline: false,
							formattingControls: [],
							placeholder: __( 'Subject' ),
							value: subject,
							onChange: function( newSubject ) {
								props.setAttributes( { subject: newSubject.toString() } );
							},
							focus: focusedEditable === 'subject' ? focus : null,
							onFocus: function( focus ) {
								props.setFocus( _.extend( {}, focus, { editable: 'subject' } ) );
							}
						}),
					)
				);
			}

			/**
			 * The shortcode form handler for the block editor
			 */
			function CoursesSetSubject( event ) {
				var selected = event.target.querySelector( 'textarea' );
				props.setAttributes( { subject: selected.value } );
				event.preventDefault();
				//event.target.parentNode.removeChild(event.target);
			}
		
			if ( props.isSelected ) {
				// display the editor form that accepts the shortcode input
				content.push( CoursesCreateForm( subject ) );
			} else {
				content.push( CoursesShortcodeEditorPreview( { subject: subject } ) );
			}
			
			return el( 'div', { className: 'courses-wrapper' }, content );

		},
		

		/**
		 * this is what gets saved to the database and displayed on pages
		 * Just the shortcode gets the work done.
		 */
		save: function( props ) {
			var subject = props.attributes.subject;
			return el( 'div', { 'data-subject': subject }, '[courses subject="' + subject.toUpperCase() + '"]');
		}


	} );

})(
	window.wp.blocks,
	window.wp.element,
	window.wp.i18n
);

