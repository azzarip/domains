<?php

namespace Azzarip\Domains\Console\Commands\Make;

use Composer\Factory;
use Composer\Json\JsonFile;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Azzarip\Domains\Console\Commands\DomainsClear;
use Azzarip\Domains\Support\ModuleRegistry;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Terminal;

class MakeDomain extends Command
{
	protected $signature = 'make:domain
		{key : The key of the domain}
		{--accept-default-namespace : Skip default namespace confirmation}';
	
	protected $description = 'Create a new Laravel domain';
	
	/**
	 * This is the base path of the module
	 *
	 * @var string
	 */
	protected $base_path;
	
	/**
	 * This is the PHP namespace for all modules
	 *
	 * @var string
	 */
	protected $domain_namespace = "Domains";
		
	/**
	 * This is the name of the module
	 *
	 * @var string
	 */
	protected $domain_key;
	
	/**
	 * This is the module name as a StudlyCase'd name
	 *
	 * @var string
	 */
	protected $class_name_prefix;
	
	/**
	 * This is the name of the module as a composer package
	 * i.e. modules/my-module
	 *
	 * @var string
	 */
	protected $composer_name;
	
	/**
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $filesystem;
	
	/**
	 * @var \Azzarip\Domains\Support\ModuleRegistry
	 */
	protected $domain_registry;
	
	public function __construct(Filesystem $filesystem, ModuleRegistry $domain_registry)
	{
		parent::__construct();
		
		$this->filesystem = $filesystem;
		$this->domain_registry = $domain_registry;
	}
	
	public function handle()
	{
		$this->domain_key = Str::kebab($this->argument('key'));
		$this->class_name_prefix = Str::studly($this->argument('key'));
		$this->composer_name = "domains/{$this->domain_key}";
		$this->base_path = $this->domain_registry->getDomainsPath().'/'.$this->domain_key;
		
		$this->setUpStyles();
		
		$this->newLine();

		$this->ensureDomainsDirectoryExists();

		$this->writeStubs();
		$this->updateCoreComposerConfig();
		
		$this->call(DomainsClear::class);
		
		$this->newLine();
		$this->line("Please run <kbd>composer update {$this->composer_name}</kbd>");
		$this->newLine();
		
		$this->domain_registry->reload();
		
		return 0;
	}
	
	
	protected function ensureDomainsDirectoryExists()
	{
		if (! $this->filesystem->isDirectory($this->base_path)) {
			$this->filesystem->makeDirectory($this->base_path, 0777, true);
			$this->line(" - Created <info>{$this->base_path}</info>");
		}
	}
	
	protected function writeStubs()
	{
		$this->title('Creating initial domains files');
		
		$tests_base = 'Tests\TestCase';
		
		$placeholders = [
			'StubBasePath' => $this->base_path,
			'StubDomainKey' => $this->domain_key,
			'StubDomainNamespace' => $this->domain_namespace,
			'StubModuleNameSingular' => Str::singular($this->domain_namespace),
			'StubModuleNamePlural' => Str::plural($this->domain_namespace),
			'StubClassNamePrefix' => $this->class_name_prefix,
			'StubComposerName' => $this->composer_name,
			'StubMigrationPrefix' => date('Y_m_d_His'),
			'StubFullyQualifiedTestCaseBase' => $tests_base,
			'StubTestCaseBase' => class_basename($tests_base),
		];
		
		$search = array_keys($placeholders);
		$replace = array_values($placeholders);
		
		foreach ($this->getStubs() as $destination => $stub_file) {
			$contents = file_get_contents($stub_file);
			$destination = str_replace($search, $replace, $destination);
			$filename = "{$this->base_path}/{$destination}";
			
			$output = str_replace($search, $replace, $contents);
			
			if ($this->filesystem->exists($filename)) {
				$this->line(" - Skipping <info>{$destination}</info> (already exists)");
				continue;
			}
			
			$this->filesystem->ensureDirectoryExists($this->filesystem->dirname($filename));
			$this->filesystem->put($filename, $output);
			
			$this->line(" - Wrote to <info>{$destination}</info>");
		}
		
		$this->newLine();
	}
	
	protected function updateCoreComposerConfig()
	{
		$this->title('Updating application composer.json file');
		
		// We're going to move into the Laravel base directory while
		// we're updating the composer file so that we're sure we update
		// the correct composer.json file (we'll restore CWD at the end)
		$original_working_dir = getcwd();
		chdir($this->laravel->basePath());
		
		$json_file = new JsonFile(Factory::getComposerFile());
		$definition = $json_file->read();
		
		if (! isset($definition['repositories'])) {
			$definition['repositories'] = [];
		}
		
		if (! isset($definition['require'])) {
			$definition['require'] = [];
		}
		
		$domain_config = [
			'type' => 'path',
			'url' => str_replace('\\', '/', 'domains').'/*',
			'options' => [
				'symlink' => true,
			],
		];
		
		$has_changes = false;
		
		$repository_already_exists = collect($definition['repositories'])
			->contains(function($repository) use ($domain_config) {
				return $repository['url'] === $domain_config['url'];
			});
		
		if (false === $repository_already_exists) {
			$this->line(" - Adding path repository for <info>{$domain_config['url']}</info>");
			$has_changes = true;
			
			$definition['repositories'][] = $domain_config;

		}
		

		if ($has_changes) {
			$json_file->write($definition);
			$this->line(" - Wrote to <info>{$json_file->getPath()}</info>");
		} else {
			$this->line(' - Nothing to update (repository & require entry already exist)');
		}
		
		chdir($original_working_dir);
		
		$this->newLine();
	}
	
	protected function sortComposerPackages(array $packages): array
	{
		$prefix = function($requirement) {
			return preg_replace(
				[
					'/^php$/',
					'/^hhvm-/',
					'/^ext-/',
					'/^lib-/',
					'/^\D/',
					'/^(?!php$|hhvm-|ext-|lib-)/',
				],
				[
					'0-$0',
					'1-$0',
					'2-$0',
					'3-$0',
					'4-$0',
					'5-$0',
				],
				$requirement
			);
		};
		
		uksort($packages, function($a, $b) use ($prefix) {
			return strnatcmp($prefix($a), $prefix($b));
		});
		
		return $packages;
	}
	
	protected function setUpStyles()
	{
		$formatter = $this->getOutput()->getFormatter();
		
		if (! $formatter->hasStyle('kbd')) {
			$formatter->setStyle('kbd', new OutputFormatterStyle('cyan'));
		}
	}
	
	protected function title($title)
	{
		$this->getOutput()->title($title);
	}
	
	public function newLine($count = 1)
	{
		$this->getOutput()->newLine($count);
	}
	
	protected function getStubs(): array
	{
		$composer_stub = 'composer-stub-latest.json';
		
		return [
			'composer.json' => $this->pathToStub($composer_stub),
			'src/Providers/StubClassNamePrefixServiceProvider.php' => $this->pathToStub('ServiceProvider.php'),
			'tests/Feature/Providers/StubClassNamePrefixServiceProviderTest.php' => $this->pathToStub('ServiceProviderTest.php'),
			'routes/StubDomainKey-routes.php' => $this->pathToStub('web-routes.php'),
			'resources/views/homepage.blade.php' => $this->pathToStub('view.blade.php'),
		];
	}
	
	protected function pathToStub($filename): string
	{
		return str_replace('\\', '/', dirname(__DIR__, 4))."/stubs/{$filename}";
	}
}
