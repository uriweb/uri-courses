( function( blocks, element, i18n ) {

	var el = element.createElement,
			registerBlockType = blocks.registerBlockType;

	function CoursesShortcode( props ) {
		return el( 'div', {
			'data-subject': props.subject
		}, '[courses subject="' + props.subject + '"]');
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
			children;

			function setSubject( event ) {
				var selected = event.target.querySelector( 'input[type=text]' );
				props.setAttributes( { subject: selected.value } );
				event.preventDefault();
				//event.target.parentNode.removeChild(event.target);
			}

			children = [];
			if ( subject ) {
				children.push( CoursesShortcode( { subject: subject } ) );
			}

			// @todo use @wordpress/components for this
			children.push(
				el( 'form', { onSubmit: setSubject, className: 'components-placeholder' },
					el( 'fieldset', {}, 
						el( 'label', { className:'components-placeholder__label'}, 'Course Code' ),
						el( 'input', { type: 'text', value: subject, placeholder: 'ABC', className:'components-placeholder__input' } ),
						el( 'button', { type: 'submit', className: 'button button-large' }, 'Save' )
					)
				)
			);
			return el( 'div', { className: 'courses-wrapper' }, children );
		},

		save: function( props ) {
			return CoursesShortcode( { subject: props.attributes.subject } );
		}


	} );

})(
	window.wp.blocks,
	window.wp.element
);

