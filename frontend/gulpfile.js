'use strict';

var gulp = require('gulp'),
    sass = require('gulp-sass'),
    clean = require('gulp-clean'),
    now = new Date().getTime();

var config = {
    sass: {
        includePaths: [
            'node_modules/govuk-frontend'
        ]
    },
    sassSrc: 'src/AppBundle/Resources/assets/scss',
    webAssets: 'web/assets/' + now,
}

// Clean out old assets
gulp.task('clean', function () {
    return gulp.src('web/assets/*', {read: false})
        .pipe(clean());
});

// Compile sass files
gulp.task('sass', ['clean'], function () {
    return gulp.src([
            config.sassSrc + '/application.scss'])
        .pipe(sass(config.sass).on('error', sass.logError))
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));
});

// Basics
gulp.task('default', ['sass'], function () {
    console.log('Building assets');
});
