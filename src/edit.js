import { useBlockProps } from '@wordpress/block-editor';
import App from './components/App/App.jsx';
import './editor.scss';

export default function Edit() {
	const isEditPage = true;

	return (
		<div {...useBlockProps()}>
			<App isEditPage={isEditPage} />
		</div>
	);
}
