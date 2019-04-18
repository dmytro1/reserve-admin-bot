const gulp = require('gulp');
const plumber = require('gulp-plumber');
const notify = require('gulp-notify');
const debug = require('gulp-debug');
const config = require('../config.js');

module.exports = function() {
    return gulp.src(config.src.fonts, {since: gulp.lastRun('font')})
    	.pipe(plumber({
            errorHandler: notify.onError(err => ({
              title: 'fonts',
              message: err.message
            }))
          }))
        .pipe(debug({title: 'fonts:'}))
        .pipe(gulp.dest(config.built.fonts));
};