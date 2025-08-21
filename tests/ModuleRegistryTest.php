<?php

namespace Azzarip\Domains\Tests;

use Azzarip\Domains\Support\ModuleConfig;
use Azzarip\Domains\Support\DomainRegistry;
use Azzarip\Domains\Tests\Concerns\WritesToAppFilesystem;

class DomainRegistryTest extends TestCase
{
	use WritesToAppFilesystem;
	
	public function test_it_resolves_modules(): void
	{
		$this->makeModule('test-module');
		$this->makeModule('test-module-two');
		
		$registry = $this->app->make(DomainRegistry::class);
		
		$this->assertInstanceOf(ModuleConfig::class, $registry->module('test-module'));
		$this->assertInstanceOf(ModuleConfig::class, $registry->module('test-module-two'));
		$this->assertNull($registry->module('non-existant-module'));
		
		$this->assertCount(2, $registry->modules());
		
		$module = $registry->moduleForPath($this->getModulePath('test-module', 'foo/bar'));
		$this->assertInstanceOf(ModuleConfig::class, $module);
		$this->assertEquals('test-module', $module->name);
		
		$module = $registry->moduleForPath($this->getModulePath('test-module-two', 'foo/bar'));
		$this->assertInstanceOf(ModuleConfig::class, $module);
		$this->assertEquals('test-module-two', $module->name);
		
		$module = $registry->moduleForClass('Modules\\TestModule\\Foo');
		$this->assertInstanceOf(ModuleConfig::class, $module);
		$this->assertEquals('test-module', $module->name);
		
		$module = $registry->moduleForClass('Modules\\TestModuleTwo\\Foo');
		$this->assertInstanceOf(ModuleConfig::class, $module);
		$this->assertEquals('test-module-two', $module->name);
	}
}
