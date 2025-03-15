<?php

namespace Azzarip\Domains\Tests\Commands\Make;

use Azzarip\Domains\Console\Commands\Make\MakeObserver;
use Azzarip\Domains\Tests\Concerns\TestsMakeCommands;
use Azzarip\Domains\Tests\Concerns\WritesToAppFilesystem;
use Azzarip\Domains\Tests\TestCase;

class MakeObserverTest extends TestCase
{
	use WritesToAppFilesystem;
	use TestsMakeCommands;
	
	public function test_it_overrides_the_default_command(): void
	{
		$this->requiresLaravelVersion('9.2.0');
		
		$this->artisan('make:observer', ['--help' => true])
			->expectsOutputToContain('--module')
			->assertExitCode(0);
	}
	
	public function test_it_scaffolds_a_observer_in_the_module_when_module_option_is_set(): void
	{
		$command = MakeObserver::class;
		$arguments = ['name' => 'TestObserver'];
		$expected_path = 'src/Observers/TestObserver.php';
		$expected_substrings = [
			'namespace Modules\TestModule\Observers',
			'class TestObserver',
		];
		
		$this->assertModuleCommandResults($command, $arguments, $expected_path, $expected_substrings);
	}
	
	public function test_it_scaffolds_a_observer_in_the_app_when_module_option_is_missing(): void
	{
		$command = MakeObserver::class;
		$arguments = ['name' => 'TestObserver'];
		$expected_path = 'app/Observers/TestObserver.php';
		$expected_substrings = [
			'namespace App\Observers',
			'class TestObserver',
		];
		
		$this->assertBaseCommandResults($command, $arguments, $expected_path, $expected_substrings);
	}
}
