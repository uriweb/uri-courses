/**
 * Helper functions for URI WYSIWYG
 */

class URICOURSES {

	// escapes quotes on every element in an array (if element is a string)
	static escapeQuotesDeep(a) {
		for(i in a) {
			if(typeof a[i] === 'string') {
				a[i] = this.escapeQuotes(a[i]);
			}
		}
		return a;
	}

	// replace " with %25
	static escapeQuotes(s) {
		return s.replace(/"/g, '%25');
	}
	
	// unescapes quotes on every element in an array (if element is a string)
	static unEscapeQuotesDeep(a) {
		for(i in a) {
			if(typeof a[i] === 'string') {
				a[i] = this.unEscapeQuotes(a[i]);
			}
		}
		return a;
	}

	// replace %25 with "
	static unEscapeQuotes(s) {
		return s.replace(/%25/g, '"');
	}
	
	/* Replace shortcode with HTML
     * @param content string The editor content
     * @param shortcodeName string The shortcode name
     * @param selfclosing bool Whether the shortcode is self-closing
     * @param callback function The callback function
     */
	static replaceShortcodes( content, shortcodeName, selfclosing, callback ) {

		var re = selfclosing ? new RegExp('\\[' + shortcodeName + '([^\\]]*)\\]', 'g') : new RegExp('\\[' + shortcodeName + '.+?\\[/' + shortcodeName + '\\]', 'g');
        
		return content.replace( re, function( match ) {
			return callback( match );
		});
	}

	
	/* Parses a short code and returns an array of attributes
     * @param sc string The shortcode
     */
	static parseShortCodeAttributes(sc) {
	
		var attributes, atts, innerContent, x, t;
        
		attributes = {};
		atts = sc.match(/[\w-]+="[^"]*"/gi);

		for ( x in atts ) {
			t = atts[x].split('=');
			t[1] = t[1].replace(/\"/gi, '');	
			attributes[t[0]] = t[1];
		}
        
        innerContent = sc.match(/\].+?\[/gi);
        if (innerContent) {
            attributes['content'] = innerContent[0].replace(/^\]|\[$/gi,'').trim();
        }
                
		return attributes;
	}
	
	// invokes the wp media picker from a tinymce modal
	static mediaPicker(e) {
		e.preventDefault();
		var imgurl, altEl, picker, alt;
		
		imgurl = document.getElementById('img');
		altEl = document.getElementById('alt');
		picker = wp.media.frames.file_frame = wp.media({
			title: 'Select an image',
			button: {text: 'Add an image'},
			multiple: false
		});		
		
		picker.on('select', function() {
			var attachment = picker.state().get('selection').first().toJSON();
			imgurl.value = attachment.sizes.full.url;
			if(!altEl.value){
				if(attachment.alt) {
					alt = attachment.alt;
				} else if(attachment.title) {
					alt = attachment.title;
				} else {
					alt = '';
				}
				altEl.value = alt;
			}
			URIWYSIWYG.mediaPickerPreview(imgurl.value, altEl.value);
		});
		picker.open();
	}
	
	static mediaPickerPreview(src, alt) {
		var preview;
		preview = document.createElement('img');
		preview.src = src;
		preview.alt = alt;
		document.getElementById('wysiwyg-img-preview').innerHTML = '';
		document.getElementById('wysiwyg-img-preview').appendChild(preview);
	}
	
}