module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    uglify: {
      options: {
        banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
      },
      build: {
        src: 'scripts/sdb-core.js',
        dest: 'build/sdb-core.min.js'
      }
    },
    jshint: {
      options: {
        reporterOutput: 'build/jshint-output.html',
        reporter: require('jshint-html-reporter')
      },
      all: ['Gruntfile.js','scripts/sdb*.js']
    }
  });

  // Load the plugin that provides the "uglify" task.
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-jshint');

  // Default task(s).
  grunt.registerTask('default', ['uglify','jshint']);

};
