<?php

namespace FLAIRUK\World\Console;

use FLAIRUK\World\Console\Concerns\RunsEveryDataset;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'world:install')]
class InstallCommand extends Command
{
    use RunsEveryDataset;

    protected $signature = 'world:install
                            {--migrate : Run the migrations and seed the tables without prompting}';

    protected $description = 'Publish every dataset\'s config and migration, then optionally migrate and seed';

    public function handle(Filesystem $files): int
    {
        $this->eachDataset(function (string $dataset) use ($files) {
            $this->callSilently('vendor:publish', ['--tag' => "{$dataset}-config"]);
            $this->publishMigration($files, $dataset);

            return self::SUCCESS;
        });

        if ($this->option('migrate') || $this->confirm('Run the migrations and seed all five tables now?', true)) {
            $this->call('migrate');
            $this->call('world:seed');
        }

        $this->components->info('Laravel World installed.');

        return self::SUCCESS;
    }

    /**
     * Copy the dataset's migration in with a timestamp, unless an earlier install already did.
     */
    protected function publishMigration(Filesystem $files, string $dataset): void
    {
        $directory = $this->laravel->databasePath('migrations');

        if ($files->glob("{$directory}/*_create_{$dataset}_table.php")) {
            return;
        }

        $source = collect(ServiceProvider::pathsToPublish(null, "{$dataset}-migrations"))->keys()->first();

        $files->ensureDirectoryExists($directory);
        $files->copy("{$source}/create_{$dataset}_table.php", $directory.'/'.date('Y_m_d_His')."_create_{$dataset}_table.php");
    }
}
