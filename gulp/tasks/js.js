const gulp = require('gulp');
const debug = require('gulp-debug');
const notify = require('gulp-notify');
const plumber = require('gulp-plumber');
const webpack = require('gulp-webpack');
const gulpif = require('gulp-if');
const rename = require('gulp-rename');
const config = require('../config.js');

const isDevelopment = !process.env.NODE_ENV || process.env.NODE_ENV == 'development';

module.exports = function(callback) {
    return gulp.src(config.src.js)
        .pipe(plumber({
            errorHandler: notify.onError(err => ({
              title: 'js',
              message: err.message
            }))
          }))
        .pipe(gulpif(isDevelopment, webpack(require('../webpack.config.js')), webpack(require('../webpack-prod.config.js'))))
        .pipe(debug({title: 'js:'}))
        .pipe(gulpif(!isDevelopment, rename(path => {
            path.extname = `.min${path.extname}`;
        })))
        .pipe(gulp.dest(config.built.js));
};

