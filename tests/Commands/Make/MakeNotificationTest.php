<?php

namespace Azzarip\Domains\Tests\Commands\Make;

use Azzarip\Domains\Console\Commands\Make\MakeNotification;
use Azzarip\Domains\Tests\Concerns\TestsMakeCommands;
use Azzarip\Domains\Tests\Concerns\WritesToAppFilesystem;
use Azzarip\Domains\Tests\TestCase;

class MakeNotificationTest extends TestCase
{
	use WritesToAppFilesystem;
	use TestsMakeCommands;
	
	public function test_it_overrides_the_default_command(): void
	{
		$this->requiresLaravelVersion('9.2.0');
		
		$this->artisan('make:notification', ['--help' => true])
			->expectsOutputToContain('--module')
			->assertExitCode(0);
	}
	
	public function test_it_scaffolds_a_notification_in_the_module_when_module_option_is_set(): void
	{
		$command = MakeNotification::class;
		$arguments = ['name' => 'TestNotification'];
		$expected_path = 'src/Notifications/TestNotification.php';
		$expected_substrings = [
			'namespace Modules\TestModule\Notifications',
			'class TestNotification',
		];
		
		$this->assertModuleCommandResults($command, $arguments, $expected_path, $expected_substrings);
	}
	
	public function test_it_scaffolds_a_notification_in_the_app_when_module_option_is_missing(): void
	{
		$command = MakeNotification::class;
		$arguments = ['name' => 'TestNotification'];
		$expected_path = 'app/Notifications/TestNotification.php';
		$expected_substrings = [
			'namespace App\Notifications',
			'class TestNotification',
		];
		
		$this->assertBaseCommandResults($command, $arguments, $expected_path, $expected_substrings);
	}
}
