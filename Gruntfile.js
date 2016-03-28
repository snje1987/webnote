module.exports = function (grunt) {
    // Project configuration.
    grunt.initConfig({
        uglify: {
            def: {
                src: [
                    'bower_components/jquery/dist/jquery.min.js',
                    'bower_components/jquery-form/jquery.form.js',
                    'bower_components/highlight/build/browser/highlight.pack.js',
                    'theme/def/script/common.js'
                ],
                dest: 'www/theme/def/script/common.js'
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
                    'www/theme/def/style/common.css': [ 'theme/style/common.css','theme/def/style/common.css', 'bower_components/highlight/build/browser/demo/styles/github.css']
                }
            }
        }
    });

    // 加载包含 "uglify" 任务的插件。
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    // 默认被执行的任务列表。
    grunt.registerTask('default', ['uglify', 'copy', 'cssmin']);

};