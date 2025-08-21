<?php

namespace Azzarip\Domains\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Azzarip\Domains\Exceptions\CannotFindModuleForPathException;
use Symfony\Component\Finder\SplFileInfo;

class DomainRegistry
{
	protected ?Collection $domains = null;
	
	public function __construct(
		protected string $domains_path,
		protected string $cache_path
	) {
	}
	
	public function getModulesPath(): string
	{
		return $this->domains_path;
	}

	public function getDomainsPath(): string
	{
		return $this->domains_path;
	}
	
	public function getCachePath(): string
	{
		return $this->cache_path;
	}
	
	public function domain(?string $name = null): ?ModuleConfig
	{
		// We want to allow for gracefully handling empty/null names
		return $name
			? $this->domains()->get($name)
			: null;
	}
	
	public function moduleForPath(string $path): ?ModuleConfig
	{
		return $this->domain($this->extractModuleNameFromPath($path));
	}
	
	public function moduleForPathOrFail(string $path): ModuleConfig
	{
		if ($module = $this->moduleForPath($path)) {
			return $module;
		}
		
		throw new CannotFindModuleForPathException($path);
	}
	
	public function moduleForClass(string $fqcn): ?ModuleConfig
	{
		return $this->domains()->first(function(ModuleConfig $domain) use ($fqcn) {
			foreach ($domain->namespaces as $namespace) {
				if (Str::startsWith($fqcn, $namespace)) {
					return true;
				}
			}
			
			return false;
		});
	}
	
	public function domains(): Collection
	{
		return $this->domains ??= $this->loadDomains();
	}
	
	public function reload(): Collection
	{
		$this->domains = null;
		
		return $this->loadDomains();
	}
	
	protected function loadDomains(): Collection
	{
		if (file_exists($this->cache_path)) {
			return Collection::make(require $this->cache_path)
				->mapWithKeys(function(array $cached) {
					$config = new ModuleConfig($cached['name'], $cached['base_path'], new Collection($cached['namespaces']));
					return [$config->name => $config];
				});
		}
		
		if (! is_dir($this->domains_path)) {
			return new Collection();
		}
		
		return FinderCollection::forFiles()
			->depth('== 1')
			->name('composer.json')
			->in($this->domains_path)
			->collect()
			->mapWithKeys(function(SplFileInfo $path) {
				$config = ModuleConfig::fromComposerFile($path);
				return [$config->name => $config];
			});
	}
	
	protected function extractModuleNameFromPath(string $path): string
	{
		// Handle Windows-style paths
		$path = str_replace('\\', '/', $path);
		
		// If the modules directory is symlinked, we may get two paths that are actually
		// in the same directory, but have different prefixes. This helps resolve that.
		if (Str::startsWith($path, $this->domains_path)) {
			$path = trim(Str::after($path, $this->domains_path), '/');
		} elseif (Str::startsWith($path, $modules_real_path = str_replace('\\', '/', realpath($this->domains_path)))) {
			$path = trim(Str::after($path, $modules_real_path), '/');
		}
		
		return explode('/', $path)[0];
	}
}
