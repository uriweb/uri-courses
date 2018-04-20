( function( blocks, element, i18n ) {

	var el = element.createElement,
			registerBlockType = wp.blocks.registerBlockType;

	/**
	 * The preview that renders in the block editor
	 * @todo: make this editable
	 */
	function CoursesShortcodeEditorPreview( props ) {
		var lipsum = el( 'div', { className: 'uri-courses-lorem-ipsum' },
			el( 'h4', {}, 'Fusce pharetra' ),
			el( 'p', {}, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus a tellus erat.' ),
			el( 'h4', {}, 'Cras scelerisque' ),
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
			content = [];

			/**
			 * Builds the form in the block editor
			 */
			function CoursesCreateForm( subject ) {
				// @todo use @wordpress/components for this instead of manually adding class names
				return el( 'form', { onSubmit: CoursesSetSubject, className: 'components-placeholder uri-courses-form' },
					el( 'fieldset', {}, 
						el( 'label', { className:'components-placeholder__label'}, 'Course Code' ),
						el( 'input', { type: 'text', value: subject, placeholder: 'ABC', className:'components-placeholder__input' } ),
						el( 'button', { type: 'submit', className: 'button button-large' }, 'Save' )
					)
				);
			}

			/**
			 * The shortcode form handler for the block editor
			 */
			function CoursesSetSubject( event ) {
				var selected = event.target.querySelector( 'input[type=text]' );
				props.setAttributes( { subject: selected.value } );
				event.preventDefault();
				//event.target.parentNode.removeChild(event.target);
			}

			// @todo: make a shortcode editable
			
			if ( ! subject || props.isSelected ) {
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
			return el( 'div', {
				'data-subject': subject
			}, '[courses subject="' + subject.toUpperCase() + '"]');
		}


	} );

})(
	window.wp.blocks,
	window.wp.element
);

