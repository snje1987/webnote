var scripts = [
    'bower_components/jquery/dist/jquery.js',
    'bower_components/jquery-form/jquery.form.js',
    'bower_components/highlight/build/highlight.pack.js',
    'theme/def/script/common.js'
];

var styles = [
    'theme/style/common.css',
    'theme/def/style/common.css',
    'bower_components/highlight/build/demo/styles/github.css'
];

module.exports = function (grunt) {
    // Project configuration.
    grunt.initConfig({
        concat: {
            scripts: {
                files: {
                    'www/theme/def/script/common.js': scripts
                }
            },
            styles: {
                files: {
                    'www/theme/def/style/common.css': styles
                }
            }
        },
        uglify: {
            def: {
                files: {
                    'www/theme/def/script/common.js': 'www/theme/def/script/common.js'
                }
            }
        },
        copy: {
            def: {
                files: [
                    {expand: true, src: ['theme/def/image/**'], dest: 'www/'}
                ]
            }

        },
        cssmin: {
            options: {
                shorthandCompacting: false,
                roundingPrecision: -1
            },
            def: {
                files: {
                    'www/theme/def/style/common.css': 'www/theme/def/style/common.css'
                }
            }
        },
        watch: {
            manage: {
                files: scripts,
                tasks: ['concat:scripts'],
                options: {
                    debounceDelay: 250
                }
            },
            web: {
                files: styles,
                tasks: ['concat:styles'],
                options: {
                    debounceDelay: 250
                }
            },
            image: {
                files: ['theme/def/image/**'],
                tasks: ['copy:def'],
                options: {
                    debounceDelay: 250
                }
            }
        },
        clean: {
            def: {
                files: [
                    {expand: true, src: ['www/*', '!www/index.php']}
                ]
            }
        }
    });

    // 加载包含 "uglify" 任务的插件。
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // 默认被执行的任务列表。
    grunt.registerTask('default', ['concat', 'copy']);
    grunt.registerTask('release', ['concat', 'copy', 'uglify', 'cssmin']);

};