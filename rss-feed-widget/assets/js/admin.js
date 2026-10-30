( function () {
	'use strict';

	var cfg = window.rfwAdmin || { i18n: {} };

	function byId( id ) { return document.getElementById( id ); }

	/* ---- Feed tester ---- */
	var testBtn = byId( 'rfw-test-btn' );
	if ( testBtn ) {
		testBtn.addEventListener( 'click', function () {
			var input = byId( 'rfw-test-url' );
			var out = byId( 'rfw-test-result' );
			out.textContent = cfg.i18n.testing;
			out.className = '';

			var body = new URLSearchParams();
			body.append( 'action', 'rfw_test_feed' );
			body.append( 'nonce', cfg.nonce );
			body.append( 'url', input.value );

			fetch( cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( res ) {
					out.textContent = '';
					if ( res && res.success ) {
						out.className = 'rfw-test-ok';
						var p = document.createElement( 'p' );
						p.textContent = cfg.i18n.feed + ': ' + ( res.data.title || '-' ) + ' | ' + cfg.i18n.items + ': ' + res.data.count;
						out.appendChild( p );
						if ( res.data.titles && res.data.titles.length ) {
							var h = document.createElement( 'strong' );
							h.textContent = cfg.i18n.latest;
							out.appendChild( h );
							var ul = document.createElement( 'ul' );
							res.data.titles.forEach( function ( t ) {
								var li = document.createElement( 'li' );
								li.textContent = t; // textContent: feed data is never parsed as HTML.
								ul.appendChild( li );
							} );
							out.appendChild( ul );
						}
					} else {
						out.className = 'rfw-test-bad';
						out.textContent = ( res && res.data && res.data.message ) || cfg.i18n.failed;
					}
				} )
				.catch( function () {
					out.className = 'rfw-test-bad';
					out.textContent = cfg.i18n.failed;
				} );
		} );
	}

	/* ---- Shortcode builder ---- */
	var builder = byId( 'rfw-builder' );
	if ( builder ) {
		var output = byId( 'rfw-builder-output' );

		var build = function () {
			var parts = [ 'rfw-feed-widget' ];
			Array.prototype.forEach.call( builder.querySelectorAll( '[data-attr]' ), function ( el ) {
				var name = el.getAttribute( 'data-attr' );
				var def = el.getAttribute( 'data-default' ) || '';
				var val;
				if ( el.type === 'checkbox' ) {
					val = el.checked ? '1' : '0';
					if ( val === def ) { return; }
					val = el.checked ? 'true' : 'false';
				} else {
					val = String( el.value ).trim();
					if ( val === '' || val === def ) { return; }
				}
				parts.push( name + '="' + val.replace( /"/g, '%22' ) + '"' );
			} );
			output.value = '[' + parts.join( ' ' ) + ']';
		};

		builder.addEventListener( 'input', build );
		builder.addEventListener( 'change', build );
		build();

		var copyBtn = byId( 'rfw-builder-copy' );
		copyBtn.addEventListener( 'click', function () {
			var done = function () {
				copyBtn.textContent = cfg.i18n.copied;
				setTimeout( function () { copyBtn.textContent = cfg.i18n.copy; }, 1600 );
			};
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( output.value ).then( done );
			} else {
				output.select();
				document.execCommand( 'copy' );
				done();
			}
		} );
	}
}() );
