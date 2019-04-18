const gulp = require('gulp');
const plumber = require('gulp-plumber');
const notify = require('gulp-notify');
const debug = require('gulp-debug');
const sass = require('gulp-sass');
const postcss = require('gulp-postcss');
const sourcemaps   = require('gulp-sourcemaps');
const autoprefixer = require('autoprefixer');
const cssmin = require('gulp-cssmin');
const gulpif = require('gulp-if');
const rename = require('gulp-rename');
const config = require('../config.js');
const path = require('path');

const isDevelopment = !process.env.NODE_ENV || process.env.NODE_ENV == 'development';

module.exports = function() {
  return gulp.src(config.src.sass)
    .pipe(plumber({
      errorHandler: notify.onError(err => ({
        title: 'sass',
        message: err.message
      }))
    }))
    .pipe(gulpif(isDevelopment, sourcemaps.init()))
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss([ 
      autoprefixer({ browsers: ['> 1%', 'last 5 versions', 'Firefox ESR'], remove: false })
    ]))
    .pipe(gulpif(!isDevelopment, cssmin()))
    .pipe(gulpif(isDevelopment, sourcemaps.write()))
    .pipe(debug({title: 'sass:'}))
    .pipe(gulpif(!isDevelopment, rename(path => {
        path.extname = `.min${path.extname}`;
    })))
    .pipe(gulp.dest(config.built.css));
};