<?php

namespace FLAIRUK\World\Console\Concerns;

trait RunsEveryDataset
{
    /**
     * Countries first, so a table that references countries can be seeded after it.
     *
     * @var list<string>
     */
    protected array $datasets = ['countries', 'cities', 'airports', 'airlines', 'aircrafts'];

    /**
     * @param  callable(string): int  $run
     */
    protected function eachDataset(callable $run): void
    {
        foreach ($this->datasets as $dataset) {
            $this->components->task(ucfirst($dataset), fn () => $run($dataset) === self::SUCCESS);
        }
    }
}
