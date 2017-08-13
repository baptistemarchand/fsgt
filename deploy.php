<?php
namespace Deployer;

require 'recipe/symfony.php';

// Configuration

set('repository', 'git@gitlab.com:baptistemarchand42/fsgt.git');
set('git_tty', true); // [Optional] Allocate tty for git on first deployment
add('shared_files', []);
add('shared_dirs', [
    'var/logs',
    'var/sessions',
]);
add('writable_dirs', ['var/cache', 'var/logs', 'var/sessions']);
set('allow_anonymous_stats', false);
set('bin_dir', 'bin');
set('var_dir', 'var');
set('http_user', 'www-data');

// Hosts

localhost()
    ->stage('production')
    ->set('deploy_path', '/var/www/fsgt');

// Tasks

task('parameters.yml', function () {
    run('cp app/config/parameters.yml {{release_path}}/app/config/parameters.yml');
});
before('deploy:vendors', 'parameters.yml');

task('deploy:vendors', function () {

});
task('deploy:assets:install', function () {

});
task('deploy:cache:clear', function () {

});
task('deploy:cache:warmup', function () {

});



//desc('Restart PHP-FPM service');
//task('php-fpm:restart', function () {
//    // The user must have rights for restart service
//    // /etc/sudoers: username ALL=NOPASSWD:/bin/systemctl restart php-fpm.service
//    run('sudo service php7.0-fpm restart');
//});
//after('deploy:symlink', 'php-fpm:restart');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

//before('deploy:symlink', 'database:migrate');

desc('Creating symlinks for shared files and dirs');
task('deploy:shared', function () {
    $sharedPath = "{{deploy_path}}/shared";
    // Validate shared_dir, find duplicates
    foreach (get('shared_dirs') as $a) {
        foreach (get('shared_dirs') as $b) {
            if ($a !== $b && strpos(rtrim($a, '/') . '/', rtrim($b, '/') . '/') === 0) {
                throw new Exception("Can not share same dirs `$a` and `$b`.");
            }
        }
    }
    foreach (get('shared_dirs') as $dir) {
        // Check if shared dir does not exists.
        if (!test("[ -d $sharedPath/$dir ]")) {
            // Create shared dir if it does not exist.
            run("mkdir -p $sharedPath/$dir");
            // If release contains shared dir, copy that dir from release to shared.
            if (test("[ -d $(echo {{release_path}}/$dir) ]")) {
                run("cp -rv {{release_path}}/$dir $sharedPath/" . dirname($dir));
            }
        }
        // Remove from source.
        run("rm -rf {{release_path}}/$dir");
        // Create path to shared dir in release dir if it does not exist.
        // Symlink will not create the path and will fail otherwise.
        run("mkdir -p `dirname {{release_path}}/$dir`");
        // Symlink shared dir to release dir
        run("{{bin/symlink}} $sharedPath/$dir {{release_path}}/$dir");
    }
    foreach (get('shared_files') as $file) {
        $dirname = dirname($file);
        // Create dir of shared file
        run("mkdir -p $sharedPath/" . $dirname);
        // Check if shared file does not exists in shared.
        // and file exist in release
        if (!test("[ -f $sharedPath/$file ]") && test("[ -f {{release_path}}/$file ]")) {
            // Copy file in shared dir if not present
            run("cp -rv {{release_path}}/$file $sharedPath/$file");
        }
        // Remove from source.
        run("if [ -f $(echo {{release_path}}/$file) ]; then rm -rf {{release_path}}/$file; fi");
        // Ensure dir is available in release
        run("if [ ! -d $(echo {{release_path}}/$dirname) ]; then mkdir -p {{release_path}}/$dirname;fi");
        // Touch shared
        run("touch $sharedPath/$file");
        // Symlink shared dir to release dir
        run("{{bin/symlink}} $sharedPath/$file {{release_path}}/$file");
    }
});
