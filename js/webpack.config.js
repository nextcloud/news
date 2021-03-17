const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');

module.exports = {
  entry: './app/VueComponents',
  output: {
    path: path.resolve(__dirname, '.'),
    filename: 'webpacked_vue_components.js',
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },
    ]
  },
  plugins: [
    new VueLoaderPlugin()
  ]
}
