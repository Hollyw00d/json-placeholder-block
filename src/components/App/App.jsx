import { useState, useEffect } from '@wordpress/element';
import Posts from '../Posts/Posts.jsx';

export default function App({ isEditPage }) {
	const [jsonData, setJsonData] = useState('Data loading...');

	// REST API to get the selected JSONPlaceholder post URL from WP options
	const wpRestJsonData = `${window.location.origin}/wp-json/jsonplaceholder/v1/jsonplaceholder-option`;

	// Proxy endpoint to safely fetch 3rd-party data via WordPress (with CORS enabled)
	const wpRestProxy = `${window.location.origin}/wp-json/myapi/v1/proxy`;

	useEffect(() => {
		async function fetchData() {
			try {
				const response = await fetch(wpRestJsonData);
				const data = await response.json();
				const jsonplaceholderUrl = data.jsonplaceholder_url;

				const proxyUrl = `${wpRestProxy}?url=${encodeURIComponent(jsonplaceholderUrl)}`;
				const getJsonResponse = await fetch(proxyUrl);
				const getJsonData = await getJsonResponse.json();

				const finalData = Array.isArray(getJsonData)
					? getJsonData
					: [getJsonData];

				if(!finalData[0]?.success || finalData[0]?.data === null) throw new Error('No data found!');

				setJsonData(finalData);
			} catch (error) {
				setJsonData('No data found!');
			}
		}

		fetchData();
	}, [wpRestJsonData, wpRestProxy]);

	return (
		<div>
			{Array.isArray(jsonData) && jsonData[0].success ? (
				<Posts jsonData={jsonData} isEditPage={isEditPage} />
			) : (
				<h2>{jsonData}</h2>
			)}
		</div>
	);
}
