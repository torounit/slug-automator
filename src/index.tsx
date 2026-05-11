/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';
import { PluginPostStatusInfo, store as editorStore } from '@wordpress/editor';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { useGenerateSlug } from './use-generate-slug';

const SlugAutomatorPluginStatusInfo = () => {
	const slug = useSelect(
		( select ) => select( editorStore ).getEditedPostAttribute( 'slug' ),
		[]
	);

	const { generateSlug, isBusy, canGenerate } = useGenerateSlug();

	return (
		<PluginPostStatusInfo>
			<Button
				onClick={ generateSlug }
				isBusy={ isBusy }
				disabled={ isBusy || ! canGenerate }
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
