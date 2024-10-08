import { createRoot } from 'react-dom/client'; // eslint-disable-line import/no-extraneous-dependencies
import domReady from '@wordpress/dom-ready'; // eslint-disable-line import/no-unresolved
import App from './components/App/App.jsx';

domReady(() => {
	// eslint-disable-next-line no-undef
	const root = createRoot(
		document.getElementsByClassName(
			'wp-block-create-block-jsonplaceholder-posts'
		)[0]
	);
	root.render(<App />, root);
});
