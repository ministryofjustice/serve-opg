'use strict';

const gulp = require('gulp');
const del = require('del');
const plumber = require('gulp-plumber');
const sass = require('gulp-sass');
const concat = require('gulp-concat');
const replace = require('gulp-replace');

var config = {
    sassSrc: './assets/scss',
    webAssets: './public/assets',
    jsSrc: './assets/javascript',
    imgSrc: './assets/images'
}

function cleanAssets() {
    return del(['./public/assets/*']);
}

function watchFiles() {
    gulp.watch(config.sassSrc + '/**/*.scss');
    gulp.watch(config.sassSrc + '/*.scss');
    gulp.watch(config.jsSrc + '/**/*.js');
    gulp.watch(config.jsSrc + '/*.js');
}

function css() {
    return gulp
        .src(config.sassSrc + '/application.scss')
        .pipe(plumber())
        .pipe(sass({
            includePaths: ['node_modules/govuk-frontend'],
            outputStyle: 'expanded'
        }))
        .pipe(replace('url("../../images/', 'url("../images/'))
        .pipe(gulp.dest(config.webAssets + '/stylesheets'))
}

function govukImagesAndFonts() {
    return gulp
        .src('node_modules/govuk-frontend/assets/**/*')
        .pipe(gulp.dest(config.webAssets + '/'))
}

function images() {
    return gulp
        .src(config.imgSrc + '/**/*')
        .pipe(plumber())
        .pipe(gulp.dest(config.webAssets + '/images'))
}

function concatJs() {
    return gulp
        .src([
            'node_modules/jquery/dist/jquery.js',
            'node_modules/govuk-frontend/all.js',
            'node_modules/dropzone/dist/dropzone.js',
            config.jsSrc + '/modules/*.js',
            config.jsSrc + '/main.js'
        ])
        .pipe(plumber())
        .pipe(concat('application.js'))
        .pipe(gulp.dest(config.webAssets + '/js'))
}

const build = gulp.series(cleanAssets, govukImagesAndFonts, gulp.parallel(css, images, concatJs))
const watch = gulp.parallel(watchFiles);

exports.watch = watch;
exports.default = build;
