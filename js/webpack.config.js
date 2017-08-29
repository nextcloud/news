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
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        // presets: ['env']
                        "plugins": [
                            ["angularjs-annotate", { "explicitOnly" : true}]
                        ]
                    }
                }
            }
        ]
    },
    output: {
        filename: '[name].min.js',
        path: path.resolve(__dirname, 'build')
    }
};