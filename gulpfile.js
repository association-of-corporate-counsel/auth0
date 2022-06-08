const gulp = require('gulp'),
    { series, parallel } = require('gulp'),
    plumber = require('gulp-plumber'),
    terser = require('gulp-terser'),
    babel = require('gulp-babel');

function build() {
    return gulp
    .src([
        'src/js/**.js'
    ])
    .pipe(plumber())
    .pipe(babel({
        presets: ["@babel/preset-env"]
    }))
    .pipe(terser())
    .pipe(gulp.dest('js/'));
}
gulp.task(build);

function watch() {
    gulp.watch('src/js/**.js', build);
}
gulp.task(watch);

exports.default = build;
exports.build = build;
exports.dev = series(build, watch);