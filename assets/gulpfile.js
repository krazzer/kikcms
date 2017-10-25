//////////////////////////////
// Gulp tasks
//////////////////////////////

var gulp         = require('gulp');
var sourcemaps   = require('gulp-sourcemaps');
var sass         = require('gulp-sass');
var cssnano      = require('gulp-cssnano');
var uglify       = require('gulp-uglify');
var concat       = require('gulp-concat');

// Root folder
var rootFolder = '../resources/';

// Styles
gulp.task('styles', function () {
    return gulp.src([
        'sass/endpoints/*.scss',
        'sass/endpoints/**/*.scss'
    ])
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(cssnano({
            zindex: false,
            discardComments: {
                removeAll: true
            }
        }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(rootFolder + 'css/'));
});

// Scripts
gulp.task('scripts');

// Vendors scripts
gulp.task('vendorsScripts', function () {
    return gulp.src([
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/collapse.js',
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/dropdown.js',
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/transition.js',
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/tab.js',
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/tooltip.js',
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/popover.js',
        'bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
        'bower_components/bootstrap3-typeahead/bootstrap3-typeahead.min.js'
    ])
        .pipe(sourcemaps.init())
        .pipe(concat('bootstrap.js'))
        .pipe(uglify())
        .pipe(gulp.dest(rootFolder + 'js/vendor/'));
});

// Vendors styles
gulp.task('vendorsStyles', function () {
    return gulp.src([
        'sass/bootstrap.scss'
    ])
        .pipe(sass())
        .pipe(cssnano({
            zindex: false,
            discardComments: {removeAll: true}
        }))
        .pipe(concat('bootstrap.css'))
        .pipe(gulp.dest(rootFolder + 'css/vendor/'));
});

// Vendors combined task
gulp.task('vendors', ['vendorsScripts', 'vendorsStyles']);

// Watch task with browserSync
gulp.task('watch', ['styles', 'scripts'], function () {
    gulp.watch('sass/**/**/*.scss', ['styles']);
    gulp.watch('sass/**/*.scss', ['styles']);
    gulp.watch('sass/*.scss', ['styles']);
    gulp.watch('scripts/*.js', ['scripts']);
});

// Default task
gulp.task('default', [
    'styles',
    'scripts',
    'vendors'
]);
