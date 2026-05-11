/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as noticesStore } from '@wordpress/notices';

export const useGenerateSlug = () => {
	const { title, id } = useSelect(
		( select ) => ( {
			title: select( editorStore ).getEditedPostAttribute( 'title' ),
			id: select( editorStore ).getCurrentPostId(),
		} ),
		[]
	);

	const { editPost } = useDispatch( editorStore );
	const { createErrorNotice } = useDispatch( noticesStore );
	const [ isBusy, setIsBusy ] = useState( false );

	const generateSlug = async () => {
		setIsBusy( true );
		try {
			const response = await apiFetch< { slug: string } >( {
				path: '/wp-abilities/v1/abilities/slug-automator/generate-slug/run',
				method: 'POST',
				data: { input: { title, context: String( id ) } },
			} );
			await editPost( { slug: response.slug } );
		} catch ( error: any ) {
			createErrorNotice( error?.message ?? 'Failed to generate slug.', {
				type: 'snackbar',
			} );
		} finally {
			setIsBusy( false );
		}
	};

	return { generateSlug, isBusy, canGenerate: !! title };
};
