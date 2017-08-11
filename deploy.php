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

desc('Restart PHP-FPM service');
task('php-fpm:restart', function () {
    // The user must have rights for restart service
    // /etc/sudoers: username ALL=NOPASSWD:/bin/systemctl restart php-fpm.service
    run('sudo service php7.0-fpm restart');
});
after('deploy:symlink', 'php-fpm:restart');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');
