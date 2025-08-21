<?php

namespace Azzarip\Domains\Console\Commands\Make;

use Illuminate\Support\Str;

trait Modularize
{
	use \Azzarip\Domains\Console\Commands\Modularize;
	
	protected function getDefaultNamespace($rootNamespace)
	{
		$namespace = parent::getDefaultNamespace($rootNamespace);
		$domain = $this->domain();
		
		if ($domain && false === strpos($rootNamespace, $domain->namespaces->first())) {
			$find = rtrim($rootNamespace, '\\');
			$replace = rtrim($domain->namespaces->first(), '\\');
			$namespace = str_replace($find, $replace, $namespace);
		}
		
		return $namespace;
	}
	
	protected function qualifyClass($name)
	{
		$name = ltrim($name, '\\/');
		
		if ($domain = $this->domain()) {
			if (Str::startsWith($name, $domain->namespaces->first())) {
				return $name;
			}
		}
		
		return parent::qualifyClass($name);
	}
	
	protected function qualifyModel(string $model)
	{
		if ($domain = $this->domain()) {
			$model = str_replace('/', '\\', ltrim($model, '\\/'));
			
			if (Str::startsWith($model, $domain->namespace())) {
				return $model;
			}
			
			return $domain->qualify('Models\\'.$model);
		}
		
		return parent::qualifyModel($model);
	}
	
	protected function getPath($name)
	{
		if ($domain = $this->domain()) {
			$name = Str::replaceFirst($domain->namespaces->first(), '', $name);
		}
		
		$path = parent::getPath($name);
		
		if ($domain) {
			// Set up our replacements as a [find -> replace] array
			$replacements = [
				$this->laravel->path() => $domain->namespaces->keys()->first(),
				$this->laravel->basePath('tests/Tests') => $domain->path('tests'),
				$this->laravel->databasePath() => $domain->path('database'),
			];
			
			// Normalize all our paths for compatibility's sake
			$normalize = function($path) {
				return rtrim($path, '/').'/';
			};
			
			$find = array_map($normalize, array_keys($replacements));
			$replace = array_map($normalize, array_values($replacements));
			
			// And finally apply the replacements
			$path = str_replace($find, $replace, $path);
		}
		
		return $path;
	}
	
	public function call($command, array $arguments = [])
	{
		// Pass the --domain flag on to subsequent commands
		if ($domain = $this->option('domain')) {
			$arguments['--domain'] = $domain;
		}
		
		return $this->runCommand($command, $arguments, $this->output);
	}
}
