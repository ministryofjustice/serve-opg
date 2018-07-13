'use strict';

var gulp = require('gulp'),
    now = new Date().getTime(),
    clean = require('gulp-clean'),
    sass = require('gulp-sass'),
    concat = require('gulp-concat');

var config = {
    sass: {
        includePaths: [
            'node_modules/govuk-frontend'
        ]
    },
    sassSrc: 'src/AppBundle/Resources/assets/scss',
    webAssets: 'web/assets/' + now,
    jsSrc: 'src/AppBundle/Resources/assets/javascript'
}

// Clean out old assets
gulp.task('clean', function () {
    return gulp.src('web/assets/*', {read: false})
        .pipe(clean());
});

// Compile sass files
gulp.task('sass', function () {
    return gulp.src([
            config.sassSrc + '/application.scss'])
        .pipe(sass(config.sass).on('error', sass.logError))
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));
});

// Copy images and fonts from govuk frontend
gulp.task('imagesAndFonts', function () {
    return gulp.src('node_modules/govuk-frontend/assets/**/*')
        .pipe(gulp.dest(config.webAssets + '/'));
});

// Concats js into application.js
gulp.task('js', function () {
    return gulp.src([
            'node_modules/govuk-frontend/all.js',
            config.jsSrc + '/main.js'])
        .pipe(concat('application.js'))
        .pipe(gulp.dest(config.webAssets + '/js'));
});

// Default task
gulp.task('default', ['clean', 'sass', 'imagesAndFonts', 'js' ]);
