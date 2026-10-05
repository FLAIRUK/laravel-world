<?php

namespace FLAIRUK\World\Tests;

use FLAIRUK\Airports\Models\Airport;
use FLAIRUK\Countries\Models\Country;
use FLAIRUK\World\Facades\World;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

class CommandsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();

        parent::tearDown();
    }

    #[Test]
    public function install_publishes_every_migration_once_and_can_skip_migrating(): void
    {
        $this->artisan('world:install')
            ->expectsConfirmation('Run the migrations and seed all five tables now?', 'no')
            ->assertSuccessful();

        $this->artisan('world:install')
            ->expectsConfirmation('Run the migrations and seed all five tables now?', 'no')
            ->assertSuccessful();

        foreach (['countries', 'cities', 'airports', 'airlines', 'aircrafts'] as $dataset) {
            $this->assertCount(1, glob(database_path("migrations/*_create_{$dataset}_table.php")), $dataset);
            $this->assertFileExists(config_path("{$dataset}.php"));
        }

        $this->assertFalse(DB::getSchemaBuilder()->hasTable('airports'));
    }

    #[Test]
    public function install_with_migrate_seeds_every_table(): void
    {
        $this->artisan('world:install', ['--migrate' => true])->assertSuccessful();

        $this->assertSame(World::countries()->all()->count(), Country::query()->count());
        $this->assertSame(World::airports()->all()->count(), Airport::query()->count());

        foreach (['cities', 'airlines', 'aircrafts'] as $table) {
            $this->assertGreaterThan(0, DB::table($table)->count(), $table);
        }
    }

    #[Test]
    public function seed_prunes_stale_rows_in_every_table(): void
    {
        $this->artisan('world:install', ['--migrate' => true])->assertSuccessful();

        DB::table('airports')->insert(['id' => 999999, 'code' => 'ZZZ', 'name' => 'Stale', 'country_code' => 'GB']);

        $this->artisan('world:seed', ['--prune' => true])->assertSuccessful();

        $this->assertFalse(Airport::query()->whereKey(999999)->exists());
    }

    protected function cleanUp(): void
    {
        $files = new Filesystem;

        foreach (['countries', 'cities', 'airports', 'airlines', 'aircrafts'] as $dataset) {
            $files->delete($files->glob(database_path("migrations/*_create_{$dataset}_table.php")));
            $files->delete(config_path("{$dataset}.php"));
        }
    }
}
