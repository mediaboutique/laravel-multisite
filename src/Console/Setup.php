<?php

namespace MediaBoutique\Multisite\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Setup extends Command
{
    protected $signature = 'multisite:setup';

    protected $description = 'Multisite setup';

    public function handle()
    {
        $this->info('Setting up multisite...');

        if (!file_exists(config_path('multisite.php'))) {
            File::copy(dirname(__FILE__, 3) . '/config/multisite.php', config_path('multisite.php'));
        }

        $directories = [
            resource_path("sites"),
            public_path("sites"),
            storage_path("app/sites"),
            base_path("routes/sites"),
        ];

        foreach ($directories as $directory) {
            if (!file_exists($directory) && !is_link($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
        }

        $this->info('Done!');
        $this->info('You can now add your first site with: php artisan multisite:site add "My site name"');
    }
}
