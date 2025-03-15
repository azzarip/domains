<?php

namespace Azzarip\Domains\Tests\Commands;

use Azzarip\Domains\Console\Commands\ModulesCache;
use Azzarip\Domains\Console\Commands\ModulesClear;
use Azzarip\Domains\Tests\Concerns\WritesToAppFilesystem;
use Azzarip\Domains\Tests\TestCase;

class ModulesClearTest extends TestCase
{
	use WritesToAppFilesystem;
	
	public function test_it_writes_to_cache_file(): void
	{
		$this->artisan(ModulesCache::class);
		
		$expected_path = $this->getApplicationBasePath().$this->normalizeDirectorySeparators('bootstrap/cache/modules.php');
		
		$this->assertFileExists($expected_path);
		
		$this->artisan(ModulesClear::class);
		
		$this->assertFileDoesNotExist($expected_path);
	}
}
