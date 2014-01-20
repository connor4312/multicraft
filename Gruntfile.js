module.exports = function(grunt) {

  grunt.initConfig({
    copy: {
      dev: {
        files: [{
          expand: true,
          cwd: 'static',
          src: ['**/*.{jpg,ico,gif,png,js}'],
          dest: 'panel/static'
        }]
      },
      dist: {
        files: [{
          expand: true,
          cwd: 'static',
          src: ['**/*.{jpg,ico}'],
          dest: 'panel/static'
        }]
      }
    },
    uglify: {
      dist: {
        options: {
          banner: '/* (c) 2014 xhost.ch */\n'
        },
        files: [{
          expand: true,
          cwd: 'static',
          src: ['js/*.js'],
          dest: 'panel/static',
          ext: '.js'
        }]
      }
    },
    less: {
      dev: {
        options: {
          yuicompress: false,
          concat: false
        },
        files: [{
          expand: true,
          cwd: 'static',
          src: ['css/*.less'],
          dest: 'panel/static',
          ext: '.css'
        }]
      },
      dist: {
        options: {
          yuicompress: true,
          concat: false
        },
        files: [{
          expand: true,
          cwd: 'static',
          src: ['css/*.less'],
          dest: 'panel/static',
          ext: '.css'
        }]
      }
    },
    imagemin: {
      dist: {
        files: [{
          expand: true,
          cwd: 'static',
          src: ['img/**/*.{png,gif}'],
          dest: 'panel/static',
        }]
      }
    },
    compress: {
      pack: {
        options: {
          archive: 'panel.zip'
        },
        files: [{
          expand: true,
          cwd: 'panel',
          src: ['**'],
          dest: '/',
        }]
      }
    },
    concurrent: {
      dev: ['copy:dev', 'less:dev'],
      dist: ['copy:dist', 'less:dist', 'imagemin:dist', 'uglify:dist'],
      watch: ['watch:copy', 'watch:less']
    },
    clean: {
      statics: ['panel/static'],
      pack: ['panel.zip'],
      state: [
        'panel/assets/*',
        'panel/protected/runtime/*',
        'panel/protected/data/data.db.dist',
        'panel/protected/config/config.php'
      ]
    },
    watch: {
      copy: {
        files: ['static/**/*.{jpg,ico,gif,png,js}'],
        tasks: ['copy:dev']
      },
      less: {
        files: ['static/css/*.less'],
        tasks: ['less:dev']
      },
    },
  });

  grunt.loadNpmTasks('grunt-concurrent');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-imagemin');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-compress');

  grunt.registerTask('default', ['clean:statics', 'concurrent:dev']);
  grunt.registerTask('spy', ['clean:statics', 'concurrent:dev', 'concurrent:watch']);

  grunt.registerTask('dist', ['clean:statics', 'concurrent:dist']);
  grunt.registerTask('pack', ['clean:statics', 'clean:pack', 'clean:state', 'concurrent:dist', 'compress:pack']);

};