<?php

namespace Azzarip\Domains\Console\Commands;

use Azzarip\Domains\Support\ModuleConfig;
use Azzarip\Domains\Support\DomainRegistry;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputOption;

trait Modularize
{
	protected function domain(): ?ModuleConfig
	{
		if ($name = $this->option('domain')) {
			$registry = $this->getLaravel()->make(DomainRegistry::class);
			
			if ($module = $registry->module($name)) {
				return $module;
			}
			
			throw new InvalidOptionException(sprintf('The "%s" domain does not exist.', $name));
		}
		
		return null;
	}
	
	protected function configure()
	{
		parent::configure();
		
		$this->getDefinition()->addOption(
			new InputOption(
				'--domain',
				null,
				InputOption::VALUE_REQUIRED,
				'Run inside an application module'
			)
		);
	}
}
