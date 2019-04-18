const gulp = require('gulp');
const plumber = require('gulp-plumber');
const notify = require('gulp-notify');
const debug = require('gulp-debug');
const imagemin = require('gulp-imagemin');
const jpegoptim = require('imagemin-jpegoptim');
const pngquant = require('imagemin-pngquant');
const imageminPngcrush = require('imagemin-pngcrush');
const config = require('../config.js');

module.exports = function() {
    return gulp.src(config.src.images, {since: gulp.lastRun('image')})
      .pipe(plumber({
            errorHandler: notify.onError(err => ({
              title: 'images',
              message: err.message
            }))
          }))
        .pipe(imagemin(
          [
            // imagemin.gifsicle({optimizationLevel: 1}),
            // imagemin.jpegtran(),
            // imagemin.optipng({optimizationLevel: 3}), 
            imagemin.svgo({
              plugins: [
                { optimizationLevel: 3 },
                { progessive: true },
                { interlaced: true },
                { removeViewBox: false },
                { removeUselessStrokeAndFill: false },
                { cleanupIDs: false }
              ]
            }),
            // jpegoptim({ max: 75 }), 
            // pngquant({ quality: '65-80', speed: 3 }),
            // imageminPngcrush()
          ],
          {
            progressive: true,
            interlaced: true
          })
        )
        .pipe(debug({title: 'images:'}))
        .pipe(gulp.dest(config.built.images));
};