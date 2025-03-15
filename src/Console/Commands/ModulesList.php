<?php

namespace Azzarip\Domains\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Azzarip\Domains\Support\ModuleConfig;
use Azzarip\Domains\Support\ModuleRegistry;

class ModulesList extends Command
{
	protected $signature = 'modules:list';
	
	protected $description = 'List all modules';
	
	public function handle(ModuleRegistry $registry)
	{
		$namespace_title = 'Namespace';
		
		$table = $registry->modules()
			->map(function(ModuleConfig $config) use (&$namespace_title) {
				$namespaces = $config->namespaces->map(function($namespace) {
					return rtrim($namespace, '\\');
				});
				
				if ($config->namespaces->count() > 1) {
					$namespace_title = 'Namespaces';
				}
				
				return [
					$config->name,
					Str::after(str_replace('\\', '/', $config->base_path), str_replace('\\', '/', $this->laravel->basePath()).'/'),
					$namespaces->implode(', '),
				];
			})
			->toArray();
		
		$count = $registry->modules()->count();
		$this->line('You have '.$count.' '.Str::plural('module', $count).' installed.');
		$this->line('');
		
		$this->table(['Module', 'Path', $namespace_title], $table);
	}
}
