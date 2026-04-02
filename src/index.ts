/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import domReady from '@wordpress/dom-ready';

domReady( () => {
	console.log( 'Slug Automator: block editor loaded.' );
} );
