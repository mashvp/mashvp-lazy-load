/* eslint-disable no-undef */

const path = require('path');

const TerserPlugin = require('terser-webpack-plugin');

const config = {
  devtool: 'eval-source-map',

  plugins: [
    new TerserPlugin({
      extractComments: false,
    }),
  ],

  module: {
    rules: [
      {
        test: /\.js$/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
          },
        },
        exclude: [/core-js/],
      },
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
        },
      },
    ],
  },

  resolve: {
    extensions: ['.js', '.jsx'],
  },
};

const adminConfig = Object.assign({}, config, {
  entry: path.resolve(__dirname, 'js/admin/index.js'),

  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'admin.min.js',
  },
});

const frontConfig = Object.assign({}, config, {
  entry: path.resolve(__dirname, 'js/front/index.js'),

  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'front.min.js',
  },
});

module.exports = [adminConfig, frontConfig];
