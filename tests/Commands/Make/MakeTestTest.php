<?php

namespace Azzarip\Domains\Tests\Commands\Make;

use Azzarip\Domains\Console\Commands\Make\MakeTest;
use Azzarip\Domains\Tests\Concerns\TestsMakeCommands;
use Azzarip\Domains\Tests\Concerns\WritesToAppFilesystem;
use Azzarip\Domains\Tests\TestCase;

class MakeTestTest extends TestCase
{
	use WritesToAppFilesystem;
	use TestsMakeCommands;
	
	public function test_it_overrides_the_default_command(): void
	{
		$this->requiresLaravelVersion('9.2.0');
		
		$this->artisan('make:test', ['--help' => true])
			->expectsOutputToContain('--module')
			->assertExitCode(0);
	}
	
	public function test_it_scaffolds_a_test_in_the_module_when_module_option_is_set(): void
	{
		$command = MakeTest::class;
		$arguments = ['name' => 'TestTest'];
		$expected_path = 'tests/Feature/TestTest.php';
		$expected_substrings = [
			'namespace Modules\TestModule\Tests',
			'use Tests\TestCase',
			'class TestTest extends TestCase',
		];
		
		$this->assertModuleCommandResults($command, $arguments, $expected_path, $expected_substrings);
	}
	
	public function test_it_scaffolds_a_test_in_the_app_when_module_option_is_missing(): void
	{
		$command = MakeTest::class;
		$arguments = ['name' => 'TestTest'];
		$expected_path = 'tests/Feature/TestTest.php';
		$expected_substrings = [
			'namespace Tests\Feature',
			'use Tests\TestCase',
			'class TestTest extends TestCase',
		];
		
		$this->assertBaseCommandResults($command, $arguments, $expected_path, $expected_substrings);
	}
}
