<?php

namespace Azzarip\Domains\Support;

use Azzarip\Domains\Console\Commands\DomainsClear;
use Azzarip\Domains\Console\Commands\Make\MakeDomain;
use Azzarip\Domains\Console\Commands\ModulesCache;
use Azzarip\Domains\Console\Commands\ModulesList;
use Azzarip\Domains\Console\Commands\ModulesSync;
use Closure;
use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory as ViewFactory;
use Livewire\Livewire;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;

class DomainsServiceProvider extends ServiceProvider
{
	protected ?DomainRegistry $registry = null;
	
	protected ?AutoDiscoveryHelper $auto_discovery_helper = null;
	
	protected string $base_dir;
	
	protected ?string $domains_path = null;
	
	public function __construct($app)
	{
		parent::__construct($app);
		
		$this->base_dir = str_replace('\\', '/', dirname(__DIR__, 2));
	}
	
	public function register(): void
	{
		$this->app->singleton(DomainRegistry::class, function() {
			return new DomainRegistry(
				$this->getDomainsBasePath(),
				$this->app->bootstrapPath('cache/modules.php')
			);
		});
		
		$this->app->singleton(AutoDiscoveryHelper::class);
		
		// Look for and register all our commands in the CLI context
		Artisan::starting(Closure::fromCallable([$this, 'onArtisanStart']));
	}
	
	public function boot(): void
	{
		$this->publishVendorFiles();
		$this->bootPackageCommands();
		
		$this->bootRoutes();
		$this->bootBreadcrumbs();
		$this->bootViews();
		$this->bootBladeComponents();
		$this->bootTranslations();
		$this->bootLivewireComponents();
	}
	
	protected function registry(): DomainRegistry
	{
		return $this->registry ??= $this->app->make(DomainRegistry::class);
	}
	
	protected function autoDiscoveryHelper(): AutoDiscoveryHelper
	{
		return $this->auto_discovery_helper ??= $this->app->make(AutoDiscoveryHelper::class);
	}
	
	protected function publishVendorFiles(): void
	{
		$this->publishes([
			"{$this->base_dir}/config.php" => $this->app->configPath('app-modules.php'),
		], 'modular-config');
	}
	
	protected function bootPackageCommands(): void
	{
		if (! $this->app->runningInConsole()) {
			return;
		}
		
		$this->commands([
			MakeDomain::class,
			ModulesCache::class,
			DomainsClear::class,
			ModulesSync::class,
			ModulesList::class,
		]);
	}
	
	protected function bootRoutes(): void
	{
		if ($this->app->routesAreCached()) {
			return;
		}
		
		$this->autoDiscoveryHelper()
			->routeFileFinder()
			->each(function(SplFileInfo $file) {
				require $file->getRealPath();
			});
	}
	
	protected function bootViews(): void
	{
		$this->callAfterResolving('view', function(ViewFactory $view_factory) {
			$this->autoDiscoveryHelper()
				->viewDirectoryFinder()
				->each(function(SplFileInfo $directory) use ($view_factory) {
					$module = $this->registry()->moduleForPathOrFail($directory->getPath());
					$view_factory->addNamespace($module->name, $directory->getRealPath());
				});
		});
	}
	
	protected function bootBladeComponents(): void
	{
		$this->callAfterResolving(BladeCompiler::class, function(BladeCompiler $blade) {
			// Boot individual Blade components (old syntax: `<x-module-* />`)
			$this->autoDiscoveryHelper()
				->bladeComponentFileFinder()
				->each(function(SplFileInfo $component) use ($blade) {
					$module = $this->registry()->moduleForPathOrFail($component->getPath());
					$fully_qualified_component = $module->pathToFullyQualifiedClassName($component->getPathname());
					$blade->component($fully_qualified_component, null, $module->name);
				});
			
			// Boot Blade component namespaces (new syntax: `<x-module::* />`)
			$this->autoDiscoveryHelper()
				->bladeComponentDirectoryFinder()
				->each(function(SplFileInfo $component) use ($blade) {
					$module = $this->registry()->moduleForPathOrFail($component->getPath());
					$blade->componentNamespace($module->qualify('View\\Components'), $module->name);
				});
		});
	}
	
	protected function bootTranslations(): void
	{
		$this->callAfterResolving('translator', function(TranslatorContract $translator) {
			if (! $translator instanceof Translator) {
				return;
			}
			
			$this->autoDiscoveryHelper()
				->langDirectoryFinder()
				->each(function(SplFileInfo $directory) use ($translator) {
					$module = $this->registry()->moduleForPathOrFail($directory->getPath());
					$path = $directory->getRealPath();
					
					$translator->addNamespace($module->name, $path);
					$translator->addJsonPath($path);
				});
		});
	}
	
	/**
	 * This functionality is likely to go away at some point so don't rely
	 * on it too much. The package has been abandoned.
	 */
	protected function bootBreadcrumbs(): void
	{
		$class_name = 'Diglactic\\Breadcrumbs\\Manager';
		
		if (! class_exists($class_name)) {
			return;
		}
		
		// The breadcrumbs package makes $breadcrumbs available in the scope of breadcrumb
		// files, so we'll do the same for consistency-sake
		$breadcrumbs = $this->app->make($class_name);
		
		$files = glob($this->getModulesBasePath().'/*/routes/breadcrumbs/*.php');
		
		foreach ($files as $file) {
			require_once $file;
		}
	}
	
	protected function bootLivewireComponents(): void
	{
		if (! class_exists(Livewire::class)) {
			return;
		}
		
		$this->autoDiscoveryHelper()
			->livewireComponentFileFinder()
			->each(function(SplFileInfo $component) {
				$module = $this->registry()->moduleForPathOrFail($component->getPath());
				
				$component_name = Str::of($component->getRelativePath())
					->explode('/')
					->filter()
					->push($component->getBasename('.php'))
					->map([Str::class, 'kebab'])
					->implode('.');
				
				$fully_qualified_component = $module->pathToFullyQualifiedClassName($component->getPathname());
				
				Livewire::component("{$module->name}::{$component_name}", $fully_qualified_component);
			});
	}
	
	protected function onArtisanStart(Artisan $artisan): void
	{
		$this->registerCommands($artisan);
		$this->registerNamespacesInTinker();
	}
	
	protected function registerCommands(Artisan $artisan): void
	{
		$this->autoDiscoveryHelper()
			->commandFileFinder()
			->each(function(SplFileInfo $file) use ($artisan) {
				$module = $this->registry()->moduleForPathOrFail($file->getPath());
				$class_name = $module->pathToFullyQualifiedClassName($file->getPathname());
				if ($this->isInstantiableCommand($class_name)) {
					$artisan->resolve($class_name);
				}
			});
	}
	
	protected function registerNamespacesInTinker()
	{
		if (! class_exists('Laravel\\Tinker\\TinkerServiceProvider')) {
			return;
		}
		
		$namespaces = app(DomainRegistry::class)
			->domains()
			->flatMap(fn(ModuleConfig $config) => $config->namespaces)
			->reject(fn($ns) => Str::endsWith($ns, ['Tests\\', 'Database\\Factories\\', 'Database\\Seeders\\']))
			->values()
			->all();
		
		Config::set('tinker.alias', array_merge($namespaces, Config::get('tinker.alias', [])));
	}
	
	protected function registerLazily(string $class_name, callable $callback): self
	{
		$this->app->resolving($class_name, Closure::fromCallable($callback));
		
		return $this;
	}
	
	protected function getDomainsBasePath(): string
	{
		if (null === $this->domains_path) {
			$this->domains_path = str_replace('\\', '/', $this->app->basePath('domains'));
		}
		
		return $this->domains_path;
	}
	
	protected function isInstantiableCommand($command): bool
	{
		return is_subclass_of($command, Command::class)
			&& ! (new ReflectionClass($command))->isAbstract();
	}
}
