/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the NekloEULA that is bundled with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt
 *
 * Copyright (c)  Neklo (http://store.neklo.com/)
 */

'use strict';

const NODE_ENV = process.env.NODE_ENV || 'development';
const gulp = require('gulp');
const minify = require('gulp-minify');
const gulpif = require('gulp-if');
const concat = require('gulp-concat');
const stylus = require('gulp-stylus');
const path = require('path');
const autoprefixer = require('autoprefixer');
const postcss = require('gulp-postcss');
const imagemin = require('gulp-imagemin');
const jswrap = require('gulp-js-wrapper');

// config
const config = require('./config/config.json');

/**
 * Javascrpt
 */
gulp.task('js', () => {
    const vendor = config.main.src.js.vendor;
    const module = config.main.src.js.module;
    const minifyConfig = {
        ext: {
            min: '-min.js'
        }
    };

    // vendor
    gulp.src(vendor)
        .pipe(concat('vendor.js'))
        .pipe(gulpif(NODE_ENV == 'production', minify(minifyConfig)))
        .pipe(gulpif(!!config.main.src.amd.vendor.length, jswrap({
            opener: "require([" + config.main.src.amd.vendor + "], function(){",
            closer: "});"
        })))
        .pipe(gulp.dest(config.main.dest.js));


    // module
    gulp.src(module)
        .pipe(concat('module.js'))
        .pipe(gulpif(NODE_ENV == 'production', minify(minifyConfig)))
        .pipe(gulpif(!!config.main.src.amd.module.length, jswrap({
            opener: "require([" + config.main.src.amd.module + "], function(){",
            closer: "});"
        })))
        .pipe(gulp.dest(config.main.dest.js));

    // all
    gulp.src([].concat(vendor, module))
        .pipe(concat('all.js'))
        .pipe(gulpif(NODE_ENV == 'production', minify(minifyConfig)))
        .pipe(gulpif(!!config.main.src.amd.all.length, jswrap({
            opener: "require([" + config.main.src.amd.all + "], function(){",
            closer: "});"
        })))
        .pipe(gulp.dest(config.main.dest.js));
});

/**
 * images
 */
gulp.task('images', () => {
    return gulp.src(config.main.src.images)
        .pipe(imagemin([imagemin.gifsicle(), imagemin.jpegtran(), imagemin.optipng(), imagemin.svgo()], {
            progressive: true,
            arithmetic: true,
            optimizationLevel: 7
        }))
        .pipe(gulp.dest(config.main.dest.images));
});

/**
 * Css
 */
gulp.task('css', () => {
    const processors = [
        autoprefixer({ browsers: ['last 100 version'] })
    ];

    gulp.src(config.main.src.stylus)
        .pipe(stylus({
            compress: NODE_ENV == 'production'
        }))
        .pipe(postcss(processors))
        .pipe(gulp.dest(config.main.dest.stylus))
});

/**
 * fonts
 */
gulp.task('fonts', () => {
    gulp.src(config.main.src.fonts)
        .pipe(gulp.dest(config.main.dest.fonts))
});

/**
 * watch
 */
gulp.task('watch', function() {
    gulp.watch(config.main.watch.stylus, ['css']);
    gulp.watch(config.main.watch.js, function (event) {
        gulp.run('js');
    });
});

/**
 * default
 */
gulp.task('default', ['js', 'css', 'fonts', 'images', "watch"]);

/**
 * Build dev
 */
gulp.task('dev', ['js', 'css', 'fonts', 'images']);

/**
 * Build prod
 */
gulp.task('prod', ['js', 'css', 'fonts', 'images']);
