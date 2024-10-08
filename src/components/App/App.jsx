import { useState, useEffect } from '@wordpress/element';
import Posts from '../Posts/Posts.jsx';

export default function App() {
	const [jsonData, setJsonData] = useState('Data loading...');
	const wpRestJsonData = `${window.location.origin}/wp-json/jsonplaceholder/v1/jsonplaceholder-option`;

	useEffect(() => {
		async function fetchData() {
			try {
				const response = await fetch(wpRestJsonData);
				const data = await response.json();
				const jsonplaceholderUrl = data.jsonplaceholder_url;
				const getJsonResponse = await fetch(jsonplaceholderUrl);
				const getJsonData = await getJsonResponse.json();
				if (!Array.isArray(getJsonData)) {
					const arr = [];
					arr.push(getJsonData);
					setJsonData(arr);
				} else {
					setJsonData(getJsonData);
				}
			} catch (error) {
				setJsonData('No data found!');
			}
		}

		fetchData();
	}, [wpRestJsonData]);

	return (
		<div>
			{Array.isArray(jsonData) ? (
				<Posts jsonData={jsonData} />
			) : (
				<h2>{jsonData}</h2>
			)}
		</div>
	);
}
