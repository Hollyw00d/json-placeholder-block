export default function SettingsPageLink() {
	const settingsPageLink = `${window.location.origin}/wp-admin/options-general.php?page=json-placeholder-block`;

	return (
		<div className="settings-page-link">
			<p>
				<a href={settingsPageLink} target="_blank" rel="noreferrer">
					JSONPlaceholder.org Posts Block Settings Page Link
				</a>
			</p>
		</div>
	);
}
