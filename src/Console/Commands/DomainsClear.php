<?php

namespace Azzarip\Domains\Console\Commands;

use Azzarip\Domains\Support\DomainRegistry;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class DomainsClear extends Command
{
	protected $signature = 'domains:clear';
	
	protected $description = 'Remove the domains cache file';
	
	public function handle(Filesystem $filesystem, DomainRegistry $registry)
	{
		$filesystem->delete($registry->getCachePath());
		$this->info('Domain cache cleared!');
	}
}
