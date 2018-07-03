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

// Default task
gulp.task('default', ['clean', 'sass', 'imagesAndFonts', ]);
