const path = require('path');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');

module.exports = {
    entry: {
        app: './app/App.js',
    },
    devtool: 'source-map',
    plugins: [
        new UglifyJSPlugin({
            sourceMap: true
        })
    ],
    output: {
        filename: '[name].min.js',
        path: path.resolve(__dirname, 'build')
    }
};