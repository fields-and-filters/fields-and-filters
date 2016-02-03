module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        clean: {
            build: {
                src: ['dest', 'packages']
            }
        },
        joomla_packager: {
            options: {
                config: {
                    version: ['<%= pkg.version %>'],
                    creationDate: ['<%= grunt.template.today("yyyy-mm-dd") %>']
                }
            },
            pkg_fieldsandfilters: {
                options: {
                    name: 'fieldsandfilters',
                    type: 'package'
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-joomla-packager');
    grunt.loadNpmTasks('grunt-contrib-clean');

    grunt.registerTask('default', [
        'clean:build',
        'joomla_packager:pkg_fieldsandfilters'
    ]);

};