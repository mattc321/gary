const path = require('path');
const gulp = require('gulp');
const rename = require('gulp-rename');

const browserSync = require('browser-sync').create();

const cache = require('gulp-cached');
const sourcemaps = require('gulp-sourcemaps');
const sass = require('gulp-sass');
const autoprefixer = require('gulp-autoprefixer');
const babel = require('gulp-babel');

// Setup files
const modulePath = (files) => {
  return './drupal/web/modules/gary/' + files;
};
const themePath = (files) => {
  return './drupal/web/themes/gary/' + files;
};

const cssFiles = [
  {
    input: modulePath('gary_layout/css/*.scss'),
    output: modulePath('gary_layout/dist'),
  },
  {
    input: modulePath('gary_custom/css/*.scss'),
    output: modulePath('gary_custom/dist'),
  },
  {
    input: themePath('css/**/*.scss'),
    output: themePath('dist'),
  },
];

const jsFiles = [
  {
    input: modulePath('gary_layout/js/**/*.js'),
    output: modulePath('gary_layout/dist'),
  },
  {
    input: modulePath('gary_custom/js/**/*.js'),
    output: modulePath('gary_custom/dist'),
  },
  {
    input: themePath('js/**/*.js'),
    output: themePath('dist'),
  }
];

// Same as below but just for compiling
gulp.task('css', () => {
  return cssFiles.map((data) => {
    return gulp.src(data.input)
      .pipe(cache('css'))
      .pipe(sourcemaps.init())
      .pipe(sass({
        // outputStyle: 'compressed'
      }))
      .on('error', function (e) {
        console.error(e);
        this.emit('end');
      })
      .pipe(autoprefixer({
        browsers: ['last 2 versions'],
      }))
      .pipe(sourcemaps.write('.'))
      .pipe(gulp.dest(data.output));
  })
});

// Same as above but also streams the output to browsersync
gulp.task('bs:css', () => {
  return cssFiles.map((data) => {
    return gulp.src(data.input)
      .pipe(cache('css'))
      .pipe(sourcemaps.init())
      .pipe(sass({
        // outputStyle: 'compressed'
      }))
      .on('error', function (e) {
        console.error(e);
        this.emit('end');
      })
      .pipe(autoprefixer({
        browsers: ['last 2 versions'],
      }))
      .pipe(sourcemaps.write('.'))
      .pipe(gulp.dest(data.output))
      .pipe(browserSync.stream({match: '**/*.css'}));
  })
});

gulp.task('js', () => {
  return jsFiles.map((data) => {
    return gulp.src(data.input)
      .pipe(cache('js'))
      .pipe(sourcemaps.init())
      .pipe(babel({
        presets: ['es2015']
      }))
      .on('error', function (e) {
        console.error(e);
        this.emit('end');
      })
      .pipe(sourcemaps.write('.'))
      .pipe(gulp.dest(data.output));
  });
});

const svgsymbols = require('gulp-svg-symbols');
gulp.task('svg', () => {
  return gulp.src([themePath('symbols/*.svg')])
    .pipe(svgsymbols())
    .pipe(gulp.dest((file, b, c) => {
      if(file.extname === '.svg') {
        file.extname = '.svg.twig';
        return themePath('templates/misc')
      } else {
        return themePath('dist/svg');
      }
    }))
});

gulp.task('serve', () => {
  browserSync.init(require('./bs-config.js'));

  gulp.watch(cssFiles.map((data) => data.input), ['bs:css']);
  gulp.watch(jsFiles.map((data) => data.input), ['js']);
});

const symbolTemplate = `<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="0" height="0" style="position:absolute">
	<% _.forEach(svg, function(svgItem) { %>
		<symbol id="<%= svgItem.name %>" viewBox="<%= svgItem.viewBox %>" preserveAspectRatio="none">
			<%= svgItem.data.replace(/<svg.*?>(.*?)<\\/svg>/, "$1") %>
		</symbol>
	<% }); %>
</svg>`;
