<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $path = $this->getPath($name);

        if ($this->files->exists($path)) {
            $this->error('Service already exists!');
            return false;
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->buildClass($name));

        $this->info('Service created successfully.');
    }

    /**
     * Get the full path to the service class.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        return app_path("Services/{$name}.php");
    }

    /**
     * Create the directory for the class if it doesn't exist.
     *
     * @param  string  $path
     * @return void
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true, true);
        }
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {

        $modelName = str_replace('Service', '', $name);

        $stub = file_get_contents(__DIR__.'/stubs/service.stub');

        return str_replace(['{{className}}', '{{modelName}}'], [$name, $modelName], $stub);
    }
}

// php artisan make:service CategoryService
// This will create a service class for the Category model in the Services directory.
