//////////////////////////////
// Gulp tasks
//////////////////////////////

var gulp         = require('gulp');
var sourcemaps   = require('gulp-sourcemaps');
var sass         = require('gulp-sass');
var cssnano      = require('gulp-cssnano');
var uglify       = require('gulp-uglify');
var concat       = require('gulp-concat');
var plumber      = require('gulp-plumber');

// Root folder
var rootFolder = '../resources/';

// Styles
gulp.task('styles', function () {
    return gulp.src([
        'sass/endpoints/*.scss',
        'sass/endpoints/**/*.scss'
    ])
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(cssnano({zindex: false}))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(rootFolder + 'css/'));
});

// Minimum requirements for the frontend
gulp.task('scriptsFrontend', function () {
    return gulp.src([
    ])
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(concat('frontend.js'))
        .pipe(uglify())
        .pipe(gulp.dest(rootFolder + 'js/'));
});

// Scripts required for the backend
gulp.task('scriptsCms', function () {
    return gulp.src([
        'js/utils.js',
        'js/kikcms.js',

        'js/datatable/datatable.js',
        'js/datatable/sortControl.js',
        'js/datatable/treeSortControl.js',

        'js/finder/finder.js',
        'js/finder/uploader.js',

        'js/webform/webform.js',

        'js/datatables/pagesDataTable.js',
        'js/datatables/selectDataTable.js',
        'js/datatables/translationsDataTable.js',

        'js/modules/statistics.js',
        'js/modules/users.js'
    ])
        .pipe(plumber())
        .pipe(concat('cms.js'))
        .pipe(uglify())
        .pipe(gulp.dest(rootFolder + 'js/'));
});

// scripts useful, but not required for Frontend
gulp.task('scriptsFrontend', function () {
    return gulp.src([
        'js/utils.js',
        'js/kikcms.js',
        'js/webform/webform.js'
    ])
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(concat('frontend/base.js'))
        .pipe(uglify())
        .pipe(gulp.dest(rootFolder + 'js/'));
});

// Vendors scripts Frontend
gulp.task('vendorsScriptsFrontend', function () {
    return gulp.src([
        'bower_components/jquery/dist/jquery.min.js',
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/tooltip.js',
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/popover.js'
    ])
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(concat('frontend/vendor.js'))
        .pipe(uglify())
        .pipe(gulp.dest(rootFolder + 'js/'));
});

// Vendors scripts CMS
gulp.task('vendorsScriptsCms', function () {
    return gulp.src([
        'bower_components/jquery/dist/jquery.min.js',

        'bower_components/moment/min/moment.min.js',
        'bower_components/moment/locale/en-gb.js',
        'bower_components/moment/locale/nl.js',

        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/collapse.js',
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/dropdown.js',
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/transition.js',
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/tab.js',
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/tooltip.js',
        'bower_components/bootstrap-sass/assets/javascripts/bootstrap/popover.js',
        'bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
        'bower_components/bootstrap3-typeahead/bootstrap3-typeahead.min.js'
    ])
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(concat('vendor.js'))
        .pipe(uglify())
        .pipe(gulp.dest(rootFolder + 'js/'));
});

// Vendors styles
gulp.task('vendorsStyles', function () {
    return gulp.src([
        'sass/bootstrap.scss'
    ])
        .pipe(plumber())
        .pipe(sass())
        .pipe(cssnano({
            zindex: false,
            discardComments: {removeAll: true}
        }))
        .pipe(concat('vendor.css'))
        .pipe(gulp.dest(rootFolder + 'css/vendor/'));
});

// Vendors combined task
gulp.task('vendors', ['vendorsScripts', 'vendorsStyles']);

// Watch task with browserSync
gulp.task('watch', ['styles', 'scriptsCms'], function () {
    gulp.watch('sass/**/*.scss', ['styles']);
    gulp.watch('sass/*.scss', ['styles']);
    gulp.watch('js/**/*.js', ['scriptsCms', 'scriptsFrontend']);
});

// Default task
gulp.task('default', [
    'styles',
    'scripts',
    'vendors'
]);
