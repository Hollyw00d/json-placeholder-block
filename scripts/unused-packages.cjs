const check = require('unused-package');

check({ entries: ['src', 'scripts', 'webpack.config.cjs'] }).then(
	(res) => {
		console.log(res); // eslint-disable-line no-console
	}
);