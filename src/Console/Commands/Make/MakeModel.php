<?php

namespace Azzarip\Domains\Console\Commands\Make;

use Illuminate\Foundation\Console\ModelMakeCommand;

class MakeModel extends ModelMakeCommand
{
	use Modularize;
	
	protected function getDefaultNamespace($rootNamespace)
	{
		if ($domain = $this->domain()) {
			$rootNamespace = rtrim($domain->namespaces->first(), '\\');
		}
		
		return $rootNamespace.'\Models';
	}
}
