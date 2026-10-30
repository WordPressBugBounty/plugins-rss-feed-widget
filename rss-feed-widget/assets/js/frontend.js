( function () {
	'use strict';

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var strings = window.rfwFront || {};

	function initSlider( root ) {
		if ( root.getAttribute( 'data-rfw-ready' ) ) { return; }
		var list = root.querySelector( '.rfw-list' );
		if ( ! list ) { return; }
		var slides = Array.prototype.filter.call( list.children, function ( n ) { return n.tagName === 'LI'; } );
		if ( slides.length < 2 ) { return; }

		root.setAttribute( 'data-rfw-ready', '1' );
		root.classList.add( 'rfw-js' );
		root.style.setProperty( '--rfw-speed', ( parseInt( root.getAttribute( 'data-rfw-speed' ), 10 ) || 500 ) + 'ms' );

		var pause = parseInt( root.getAttribute( 'data-rfw-pause' ), 10 ) || 8000;
		var autoplay = root.getAttribute( 'data-rfw-autoplay' ) === '1' && ! reduceMotion;
		var index = 0;
		var timer = null;
		var hovering = false;

		var pager = document.createElement( 'div' );
		pager.className = 'rfw-pager';
		var dots = slides.map( function ( slide, i ) {
			var b = document.createElement( 'button' );
			b.type = 'button';
			b.className = 'rfw-dot';
			b.setAttribute( 'aria-label', ( strings.goTo || 'Go to slide' ) + ' ' + ( i + 1 ) );
			b.addEventListener( 'click', function () { show( i ); schedule(); } );
			pager.appendChild( b );
			return b;
		} );
		root.appendChild( pager );

		function show( i ) {
			index = ( i + slides.length ) % slides.length;
			slides.forEach( function ( s, n ) { s.classList.toggle( 'is-active', n === index ); } );
			dots.forEach( function ( d, n ) {
				if ( n === index ) { d.setAttribute( 'aria-current', 'true' ); } else { d.removeAttribute( 'aria-current' ); }
			} );
		}

		function schedule() {
			clearTimeout( timer );
			if ( ! autoplay || hovering || document.hidden ) { return; }
			timer = setTimeout( function () { show( index + 1 ); schedule(); }, pause );
		}

		root.addEventListener( 'mouseenter', function () { hovering = true; clearTimeout( timer ); } );
		root.addEventListener( 'mouseleave', function () { hovering = false; schedule(); } );
		root.addEventListener( 'focusin', function () { hovering = true; clearTimeout( timer ); } );
		root.addEventListener( 'focusout', function () { hovering = false; schedule(); } );
		document.addEventListener( 'visibilitychange', schedule );

		show( 0 );
		schedule();
	}

	function init( scope ) {
		( scope || document ).querySelectorAll( '.rfw-feed.rfw-layout-slider' ).forEach( initSlider );
		if ( ( scope || document ).querySelector( '.rfw-yt-items[data-rfw-fullscreen="1"]' ) ) {
			document.body.classList.add( 'rfw-yt-fullscreen' );
		}
	}

	window.rfwInit = init;

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () { init( document ); } );
	} else {
		init( document );
	}
}() );
