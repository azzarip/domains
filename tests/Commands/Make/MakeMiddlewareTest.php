<?php

namespace Azzarip\Domains\Tests\Commands\Make;

use Azzarip\Domains\Console\Commands\Make\MakeMiddleware;
use Azzarip\Domains\Tests\Concerns\TestsMakeCommands;
use Azzarip\Domains\Tests\Concerns\WritesToAppFilesystem;
use Azzarip\Domains\Tests\TestCase;

class MakeMiddlewareTest extends TestCase
{
	use WritesToAppFilesystem;
	use TestsMakeCommands;
	
	public function test_it_overrides_the_default_command(): void
	{
		$this->requiresLaravelVersion('9.2.0');
		
		$this->artisan('make:middleware', ['--help' => true])
			->expectsOutputToContain('--module')
			->assertExitCode(0);
	}
	
	public function test_it_scaffolds_a_middleware_in_the_module_when_module_option_is_set(): void
	{
		$command = MakeMiddleware::class;
		$arguments = ['name' => 'TestMiddleware'];
		$expected_path = 'src/Http/Middleware/TestMiddleware.php';
		$expected_substrings = [
			'namespace Modules\TestModule\Http\Middleware',
			'class TestMiddleware',
		];
		
		$this->assertModuleCommandResults($command, $arguments, $expected_path, $expected_substrings);
	}
	
	public function test_it_scaffolds_a_middleware_in_the_app_when_module_option_is_missing(): void
	{
		$command = MakeMiddleware::class;
		$arguments = ['name' => 'TestMiddleware'];
		$expected_path = 'app/Http/Middleware/TestMiddleware.php';
		$expected_substrings = [
			'namespace App\Http\Middleware',
			'class TestMiddleware',
		];
		
		$this->assertBaseCommandResults($command, $arguments, $expected_path, $expected_substrings);
	}
}
