const path = require('path');

module.exports = {
  entry: './app/VueComponents',
  output: {
    path: path.resolve(__dirname, '.'),
    filename: 'webpacked_vue_components.js',
  }
}
