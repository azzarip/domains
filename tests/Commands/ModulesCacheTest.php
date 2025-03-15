<?php

namespace Azzarip\Domains\Tests\Commands;

use Azzarip\Domains\Console\Commands\ModulesCache;
use Azzarip\Domains\Tests\Concerns\WritesToAppFilesystem;
use Azzarip\Domains\Tests\TestCase;

class ModulesCacheTest extends TestCase
{
	use WritesToAppFilesystem;
	
	public function test_it_writes_to_cache_file(): void
	{
		$this->makeModule('test-module');
		$this->makeModule('test-module-two');
		
		$this->artisan(ModulesCache::class);
		
		$expected_path = $this->getApplicationBasePath().$this->normalizeDirectorySeparators('bootstrap/cache/modules.php');
		
		$this->assertFileExists($expected_path);
		
		$cache = include $expected_path;
		
		$this->assertArrayHasKey('test-module', $cache);
		$this->assertArrayHasKey('test-module-two', $cache);
	}
}
