<?php

namespace MediaBoutique\Multisite\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class Site extends Command
{
    protected $signature = 'multisite:site {option} {name}';

    protected $description = 'Add or remove a site';

    public function handle()
    {
        return match ($this->argument('option')) {
            'add' => $this->add(),
            'remove' => $this->remove(),
        };
    }

    protected function add()
    {
        $name = $this->argument('name');
        $slug = Str::slug($name);

        $this->info("Adding site: {$name} (slug: {$slug})");

        $found = true;
        $directories = [
            resource_path("sites"),
            resource_path("sites/{$slug}"),
            resource_path("sites/{$slug}/css"),
            resource_path("sites/{$slug}/js"),
            resource_path("sites/{$slug}/views"),
            public_path("sites"),
            public_path("sites/{$slug}"),
            public_path("sites/{$slug}/css"),
            public_path("sites/{$slug}/js"),
            public_path("sites/{$slug}/img"),
            storage_path("app/sites"),
            storage_path("app/sites/{$slug}"),
            base_path("routes/sites"),
        ];

        foreach ($directories as $directory) {
            if (!file_exists($directory) && !is_link($directory)) {
                $found = false;
                File::makeDirectory($directory, 0755, true);
            }
        }

        if (!file_exists(base_path("routes/sites/{$slug}.php"))) {
            $found = false;
            $stub = File::get(dirname(__FILE__, 3) . '/stubs/routes.stub');

            $stub = str_replace(
                [
                    '{{slug}}',
                ],
                [
                    $slug
                ],
                $stub
            );
            File::put(base_path("routes/sites/{$slug}.php"), $stub);
        }

        if ($found) {
            $this->error('Site already added!');
        } else {
            $this->info('Done!');
        }
    }

    protected function remove()
    {
        $name = $this->argument('name');
        $slug = Str::slug($name);

        $this->info("Removing site: {$name} (slug: {$slug})");

        $found = false;
        $directories = [
            resource_path("sites/{$slug}/css"),
            resource_path("sites/{$slug}/js"),
            resource_path("sites/{$slug}/views"),
            resource_path("sites/{$slug}"),
            public_path("sites/{$slug}/css"),
            public_path("sites/{$slug}/js"),
            public_path("sites/{$slug}/img"),
            public_path("sites/{$slug}"),
            storage_path("app/sites/{$slug}"),
        ];

        foreach ($directories as $directory) {
            if (file_exists($directory) || is_link($directory)) {
                $found = true;
                File::deleteDirectory($directory);
            }
        }

        if (file_exists(base_path("routes/sites/{$slug}.php"))) {
            $found = true;
            File::delete(base_path("routes/sites/{$slug}.php"));
        }

        if ($found) {
            $this->info('Done!');
        } else {
            $this->error('Nothing to remove!');
        }
    }
}
