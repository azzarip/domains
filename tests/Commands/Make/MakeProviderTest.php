<?php

namespace Azzarip\Domains\Tests\Commands\Make;

use Azzarip\Domains\Console\Commands\Make\MakeProvider;
use Azzarip\Domains\Tests\Concerns\TestsMakeCommands;
use Azzarip\Domains\Tests\Concerns\WritesToAppFilesystem;
use Azzarip\Domains\Tests\TestCase;

class MakeProviderTest extends TestCase
{
	use WritesToAppFilesystem;
	use TestsMakeCommands;
	
	public function test_it_overrides_the_default_command(): void
	{
		$this->requiresLaravelVersion('9.2.0');
		
		$this->artisan('make:provider', ['--help' => true])
			->expectsOutputToContain('--module')
			->assertExitCode(0);
	}
	
	public function test_it_scaffolds_a_provider_in_the_module_when_module_option_is_set(): void
	{
		$command = MakeProvider::class;
		$arguments = ['name' => 'TestProvider'];
		$expected_path = 'src/Providers/TestProvider.php';
		$expected_substrings = [
			'namespace Modules\TestModule\Providers',
			'class TestProvider',
		];
		
		$this->assertModuleCommandResults($command, $arguments, $expected_path, $expected_substrings);
	}
	
	public function test_it_scaffolds_a_provider_in_the_app_when_module_option_is_missing(): void
	{
		$command = MakeProvider::class;
		$arguments = ['name' => 'TestProvider'];
		$expected_path = 'app/Providers/TestProvider.php';
		$expected_substrings = [
			'namespace App\Providers',
			'class TestProvider',
		];
		
		$this->assertBaseCommandResults($command, $arguments, $expected_path, $expected_substrings);
	}
}
