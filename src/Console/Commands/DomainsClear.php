<?php

namespace Azzarip\Domains\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Azzarip\Domains\Support\ModuleRegistry;

class DomainsClear extends Command
{
	protected $signature = 'domains:clear';
	
	protected $description = 'Remove the domains cache file';
	
	public function handle(Filesystem $filesystem, ModuleRegistry $registry)
	{
		$filesystem->delete($registry->getCachePath());
		$this->info('Module cache cleared!');
	}
}
