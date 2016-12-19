<?php
namespace Deployer;
require 'vendor/deployer/deployer/recipe/common.php';

// Configuration
set('repository', 'https://github.com/famoser/SyncApi.git');
set('shared_files', ["app/data.sqlite"]);
set('shared_dirs', ["app/logs"]);
set('writable_dirs', ['app/logs', 'app/cache']);
set('clear_paths', ["app/cache"]);
set('http_user', 'floria74');

// import servers
serverList('servers.yml');

desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

//stages: dev, testing, production
set('default_stage', 'dev');

after('deploy', 'success');