'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var merge = require('merge-stream');
var browserSync = require('browser-sync').create();

var env = process.argv.slice(2);
var debug = env === 'production' ? false : true;


gulp.task('sass', function() {
  return gulp.src('./scss/app.scss')
    .pipe(sourcemaps.init({
        loadMaps: false,
        debug: debug
    }))
    .pipe(sass({
      includePaths: [ './node_modules/bootstrap/scss/' ],
      outputStyle: debug ? 'compressed' : 'nested'
    }).on('error', sass.logError))
    .pipe(sourcemaps.write('.', {
        includeContent: false,
        sourceRoot: '/'
    }))
    .pipe(gulp.dest('../assets/css'))
    .pipe(browserSync.stream());
});

gulp.task('sass:watch', function() {
  gulp.watch('./scss/**/*.scss');
});

gulp.task('copy:scripts', function() {
  var bootstrap = gulp.src('node_modules/bootstrap/dist/js/*.js*')
    .pipe(gulp.dest('../assets/js/lib/bootstrap'));

  return merge(bootstrap);
});

gulp.task('copy:styles', function() {
  var bootstrap = gulp.src('node_modules/bootstrap/dist/css/*.css*')
    .pipe(gulp.dest('../assets/css/lib/bootstrap'));

  return merge(bootstrap);
});

gulp.task('serve', gulp.series('sass'), function() {
  browserSync.init({
    server: false,
    ui: false,
    online: false
  });

  gulp.watch('scss/**/*.scss', gulp.serires('sass'));
  gulp.watch('js/**/*.js').on('change', browserSync.reload);
});


gulp.task('default', gulp.parallel([ 'serve', 'copy:scripts', 'copy:styles' ]));
