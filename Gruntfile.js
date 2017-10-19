module.exports = function(grunt) {

  var buildDir   = 'build';
  var releaseDir = 'release';

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    uglify: {
      options: {
        banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n',
        compress: {
          drop_console: true
        },
        sourceMap: true,
      },
      build: {
        files: [{
          expand: true,
          cwd: 'scripts',
          src: ['**/*.js','!**/*.min.js'],
          ext: '.min.js',
          extDot: 'last',
          dest: buildDir+'/scripts'
        }]
      },
      merge: {
        files:  [{
          src: ['scripts/sdb*.js', 'scripts/custom*.js', 'scripts/lang*.js', '!scripts/*.min.js'],
          dest: buildDir+'/merged/scripts/sdb-all.min.js'
          //'build/merged/scripts/sdb-all.js': ['scripts/sdb*.js', 'scripts/custom.ext.js', 'scripts/langprovider.js']
        }]
      }

    },
    jshint: {
      options: {
        reporterOutput: buildDir+'/jshint-output.html',
        reporter: require('jshint-html-reporter'),
        force: true
      },
      all: ['Gruntfile.js','scripts/*.js']
    },
    cssmin: {
      options: {
        sourceMap: true,
      },
      build: {
        files: [{
          expand: true,
          cwd: 'styles',
          src: ['**/*.css','!**/*.min.css'],
          ext: '.min.css',
          extDot: 'last',
          dest: 'build/styles'
        }]
      },
      merge: {
        files: [{
          src: ['styles/*.css','!styles/*.min.css'],
          dest: buildDir+'/merged/styles/shelfdb-all.min.css'
        }]
      }
    },
    watch: {
      css: {
        files: ['styles/**/*.css', '!styles/**/*.min.css'],
        tasks: ['cssmin']
      },
      js: {
        files: ['scripts/**/*.js','!scripts/**/*.min.js'],
        tasks: ['uglify']
      }
    },
    clean: {
      all: [buildDir+'/*', releaseDir+'/*'],
      release: [releaseDir+'/*'],
      build: [buildDir+'/*']
    },
    copy: {
      release: {
        files: [
          {
            expand: true,
            src: ['img/**', 'lib/**', 'config/*.json', 'classes/**', 'pages/**', 'scripts/**', 'sql/**', 'styles/**', 'templates/**', '*.php', '!**/*.js', '!**/*.css'],
            dest: releaseDir+'/'
          }, {
            expand: true,
            cwd: buildDir+'/',
            src: '**',
            dest: releaseDir+'/'
          }
        ]
      },
      dev: {
        files: [
          {
            expand: true,
            src: ['lib/**', 'config/*.json', 'classes/**', 'pages/**', 'scripts/**', 'sql/**', 'templates/**', '*.php'],
            dest: releaseDir+'/'
          }
        ]
      }
    }
  });

  // Load the plugin that provides the "uglify" task.
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-newer');

  // Default task(s).
  grunt.registerTask('all', ['newer:cssmin:build', 'newer:uglify:build', 'newer:jshint']);
  grunt.registerTask('rebuild_all', ['clean:build', 'all'])
  grunt.registerTask('merged', ['newer:cssmin:merge', 'newer:uglify:merge']);
  grunt.registerTask('release', ['all', 'newer:copy:release']);

};
