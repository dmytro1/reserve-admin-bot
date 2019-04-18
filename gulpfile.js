'use strict';

const gulp = require('gulp');

function lazyRequireTask(taskName, path) {
  gulp.task(taskName, function() {
  	let task = require(path);
    return task();
  });
}

lazyRequireTask('clean', './gulp/tasks/clean');

lazyRequireTask('font', './gulp/tasks/font');

lazyRequireTask('html', './gulp/tasks/html');

lazyRequireTask('sprite', './gulp/tasks/sprite');

lazyRequireTask('image', './gulp/tasks/image');

lazyRequireTask('sass', './gulp/tasks/sass');

lazyRequireTask('js', './gulp/tasks/js');

lazyRequireTask('watch', './gulp/tasks/watch');

lazyRequireTask('serve', './gulp/tasks/serve');

gulp.task('ws', gulp.parallel('watch', 'serve'));

gulp.task('minimal', gulp.series('sass', 'js', 'watch'));

gulp.task('build', gulp.series('font', 'sprite', 'image', 'sass', 'js'));

gulp.task('rebuild', gulp.series('clean', 'build'));

gulp.task('default', gulp.series('rebuild', gulp.parallel('watch', 'serve', 'html')));


gulp.task('set-prod', function(){
	process.env.NODE_ENV = "production";
	return gulp.src('./');
});
gulp.task('build:prod', gulp.series('set-prod', 'sass', 'js'));

//in console:   gulp build:prod    or    NODE_ENV=production gulp build
