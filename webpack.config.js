const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		"admin": './src/common.js',
		"inline-edit-roles": './src/inline-edit-roles.js',
		"inline-edit-user-capabilities": './src/inline-edit-user-capabilities.js',
		"roles-admin": './src/roles-admin.js',
	},
	output: {
		filename: '[name].js',
		path: __dirname + '/build'
	}
};
