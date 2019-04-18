const gulp = require('gulp');
const debug = require('gulp-debug');
const plumber = require('gulp-plumber');
const notify = require('gulp-notify');
const svgSprite = require('gulp-svg-sprite');
const rsp = require('remove-svg-properties').stream;
const gulpif = require('gulp-if');
const config = require('../config.js');

module.exports = function() {
  return gulp.src(config.src.sprite)
    .pipe(plumber({
      errorHandler: notify.onError(err => ({
        title: 'sprite',
        message: err.message
      }))
    }))
    // .pipe(rsp.remove({
    //   properties: [rsp.PROPS_STROKE, rsp.PROPS_FILL, 'color'],
    //   log: false
    // }))
    .pipe(svgSprite({
      mode            : {
        sprite1     : {
            mode    : 'symbol',
            dest:       '.',
            bust:       false,
            sprite:     'sprite.php',
            layout:     'vertical',
            prefix:     '.',
            dimensions: '-icon',
            inline: true
        },
        sprite2     : {
            mode    : 'symbol',
            dest:       '.',
            bust:       false,
            sprite:     'sprite.svg',
            layout:     'vertical',
            prefix:     '.',
            dimensions: '-icon',
            render:     {
              scss: {
                dest: 'sprite.scss',
                template: config.src.dir + 'sass/sprite/sprite_template.scss'
              }
            }
        }
      }
    }))
    .pipe(debug({title: 'sprite:'}))
    .pipe(gulpif('*.scss', gulp.dest(config.src.dir + 'sass/sprite'), gulpif('*.svg', gulp.dest(config.src.dir + 'images'), gulp.dest('./'))));
};