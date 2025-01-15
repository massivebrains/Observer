<?php declare(strict_types=1);

namespace App\Console\Commands;

use Artisan;
use DB;
use Illuminate\Console\Command;
use Laravel\Prompts\Output\ConsoleOutput;

class TestSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup test database and run migrations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::statement('CREATE DATABASE IF NOT EXISTS `status_monitoring_test`');

        Artisan::call('migrate:fresh', ['--env' => 'testing', '--database' => 'mysql-test', '--seed' => 1], new ConsoleOutput());

        return 0;
    }
}
