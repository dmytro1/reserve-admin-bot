const gulp = require('gulp');
const plumber = require('gulp-plumber');
const notify = require('gulp-notify');
const debug = require('gulp-debug');
const fileinclude = require('gulp-file-include');
const config = require('../config.js');

module.exports = function() {
    return gulp.src(config.src.html)
        .pipe(plumber({
            errorHandler: notify.onError(err => ({
              title: 'html',
              message: err.message
            }))
          }))
        .pipe(fileinclude({
          prefix: '@@',
          basepath: '@file'
        }))
        .pipe(debug({title: 'html:'}))
        .pipe(gulp.dest(config.built.html));
};