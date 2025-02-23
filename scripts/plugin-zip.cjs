const AdmZip = require('adm-zip');
const fs = require('fs');
const path = require('path');
const ignoreArray = require('./plugin-zip-ignore-array.cjs');
const scriptGlobals = require('./script-globals.json');

function pluginZip() {
	const packageJsonPath = path.join(process.cwd(), 'package.json');
	const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));
	const pluginVersion = packageJson?.version;
	const pluginUrl = packageJson.config?.['plugin-url'];
	const pluginRoot = packageJson.config?.['plugin-root'];
	const pluginDir = path.basename(process.cwd());
	const newZipFileName = `${pluginDir}.${pluginVersion}.zip`;
	const zipFilePath = path.join(process.cwd(), newZipFileName);

	if (!pluginVersion || !pluginUrl || !pluginRoot) {
		/* eslint-disable no-console */
		console.error(
			`All plugin metadata ('version', 'config[plugin-url]', 'config[plugin-root]') is missing in package.json!`
		);
		/* eslint-enable */
		process.exit(1);
	}

	// If a previous ZIP exists, delete it
	if (fs.existsSync(zipFilePath)) {
		fs.unlinkSync(zipFilePath);
		console.log(`Deleted existing zipped file: ${newZipFileName}`); // eslint-disable-line no-console
	}


	const zip = new AdmZip();
	// Recursively add files and folders from the current directory into the ZIP
	function addFilesRecursively(srcDir, zipFolder) {
		const items = fs.readdirSync(srcDir);
		items.forEach((item) => {
			// Skip items in the ignore list or the ZIP file we're creating
			if (ignoreArray.includes(item) || item === newZipFileName) return;

			const fullPath = path.join(srcDir, item);
			const stat = fs.statSync(fullPath);

			if (stat.isDirectory()) {
				// Add an empty folder entry to preserve folder structure
				zip.addFile(path.join(zipFolder, item, '/'), Buffer.alloc(0));
				console.log(item);
				addFilesRecursively(fullPath, path.join(zipFolder, item));
			} else {
				// Add the file under the given ZIP folder
				console.log(item);
				zip.addLocalFile(fullPath, zipFolder);
			}
		});
	}
	addFilesRecursively(process.cwd(), pluginDir);

	// Write out the ZIP archive to disk
	zip.writeZip(zipFilePath);

	/* eslint-disable no-console */
	console.log(
		`Done. Created ${newZipFileName}. When unzipped you will see a folder named '${pluginDir}'! ${scriptGlobals.emojis['party-popper']}`
	);
	/* eslint-enable */
}

pluginZip();
