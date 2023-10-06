'use strict';

var gulp = require('gulp'),
    watch = require('gulp-watch'),
    prefixer = require('gulp-autoprefixer'),
    sass = require('gulp-sass')(require('sass')),
    uglify = require('gulp-uglify'),
    minify = require('gulp-minify'),
    sourcemaps = require('gulp-sourcemaps'),
    plumber = require('gulp-plumber');

var path = {
    build: {
        css: 'assets/css/'
    },
    src: {
        style: 'assets/sass/*.scss'
    },
    watch: {
        style: 'assets/sass/*.scss'
    },
    clean: './css'
};

var config = {
    server: {
        baseDir: "./css"
    },
    tunnel: true,
    host: 'localhost',
    port: 9000,
    logPrefix: "Frontend"
};

gulp.task('style:build', async function () {
    gulp.src(path.src.style)
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(sass({
            includePaths: ['sass/'],
            sourcemaps: true,
            errLogToConsole: true,
            outputStyle: 'compressed'
        }))
        .pipe(prefixer({
            overrideBrowserslist: ['last 4 versions', 'ie 9'],
            cascade: false
        }))
        //.pipe(gcmq())
        .pipe(sourcemaps.write())
        .pipe(minify())
        .pipe(gulp.dest(path.build.css));
});

gulp.task('build', gulp.series(
    'style:build'
));

gulp.task('watch', async function(){
    gulp.watch(path.watch.style, gulp.series('style:build'));
});

gulp.task('default', gulp.series('build', 'watch'));
