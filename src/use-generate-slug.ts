/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as noticesStore } from '@wordpress/notices';

export const useGenerateSlug = () => {
	const { title, id, slug } = useSelect(
		( select ) => ( {
			title: select( editorStore ).getEditedPostAttribute( 'title' ),
			id: select( editorStore ).getCurrentPostId(),
			slug: select( editorStore ).getEditedPostAttribute( 'slug' ),
		} ),
		[]
	);

	const { editPost } = useDispatch( editorStore );
	const { createErrorNotice } = useDispatch( noticesStore );
	const [ isBusy, setIsBusy ] = useState( false );

	const generateSlug = async () => {
		setIsBusy( true );
		const { executeAbility } = await import(
			/* webpackIgnore: true */ '@wordpress/abilities'
		);
		try {
			const response = await executeAbility(
				'slug-automator/generate-slug',
				{
					title,
					context: { type: 'post', id },
					avoid: slug ?? '',
				}
			);

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
