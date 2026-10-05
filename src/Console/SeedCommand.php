<?php

namespace FLAIRUK\World\Console;

use FLAIRUK\World\Console\Concerns\RunsEveryDataset;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'world:seed')]
class SeedCommand extends Command
{
    use RunsEveryDataset;

    protected $signature = 'world:seed
                            {--prune : Delete rows that are no longer in the datasets}';

    protected $description = 'Insert or update every dataset table from the bundled data';

    public function handle(): int
    {
        $this->eachDataset(fn (string $dataset) => $this->call("{$dataset}:seed", array_filter([
            '--prune' => $this->option('prune'),
        ])));

        return self::SUCCESS;
    }
}
