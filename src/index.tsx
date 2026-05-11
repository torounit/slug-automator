/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';
import { PluginPostStatusInfo, store as editorStore } from '@wordpress/editor';
import { Button } from '@wordpress/components';

const SlugAutomatorPluginStatusInfo = () => {
	const slug = useSelect( ( select ) =>
		select( editorStore ).getEditedPostAttribute( 'slug' )
	);

	const generateSlug = () => {

	}

	return (
		<PluginPostStatusInfo>
			<Button
				onClick={() => {
					generateSlug();
				}}
				variant="secondary"
				style={ { width: '100%', justifyContent: 'center' } }
			>
				{ slug ? 'Regenerate Slug' : 'Generate Slug' }
			</Button>
		</PluginPostStatusInfo>
	);
};

registerPlugin( 'slug-automator', {
	render: SlugAutomatorPluginStatusInfo,
} );
