module.exports = {
    src: {
        dir:                    'assets/src/',
        fonts:                  'assets/src/fonts/**/*.{eot,ttf,woff,woff2,svg}',
        js:                     'assets/src/javascripts/common.js',
        sass:                   'assets/src/sass/*.sass',
        html:                   'assets/src/html/*.html',
        images:                 ['assets/src/images/**/*.{jpg,png,gif,svg}', '!assets/src/images/sprite/*.*'],
        sprite:                 'assets/src/images/sprite/*.svg'
    },

    watch: {
        fonts:                  'assets/src/fonts/**/*.{eot,ttf,woff,woff2,svg}',
        js:                     'assets/src/javascripts/**/*.js',
        sass:                   'assets/src/sass/**/*.{sass,scss}',
        html:                   'assets/src/html/**/*.html',
        images:                 ['assets/src/images/**/*.{jpg,png,gif,svg}', '!assets/src/images/sprite/*.*'],
        sprite:                 'assets/src/images/sprite/*.svg'
    },

    built: {
        dir:                    'assets/built/',
        fonts:                  'assets/built/fonts/',
        js:                     'assets/built/javascripts/',
        css:                    'assets/built/stylesheets/',
        html:                   'assets/built/html',
        images:                 'assets/built/images/',
        all:                    'assets/built/**/*.*',
        sprite:                 'assets/built/sprite/'
    }
};