const gulp = require('gulp');
const config = require('../config.js');

module.exports = function() {

    gulp.watch(config.watch.fonts, gulp.series('font'));

    gulp.watch(config.watch.html, gulp.series('html'));

    gulp.watch([config.watch.sprite, config.src.dir + 'sass/sprite/sprite_template.scss'], gulp.series('sprite'));

    gulp.watch(config.watch.images, gulp.series('image'));

    gulp.watch(config.watch.sass, gulp.series('sass'));

    gulp.watch(config.watch.js, gulp.series('js'));

};