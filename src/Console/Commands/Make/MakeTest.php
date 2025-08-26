<?php

namespace Azzarip\Domains\Console\Commands\Make;

use Illuminate\Foundation\Console\TestMakeCommand;
use Illuminate\Support\Str;

class MakeTest extends TestMakeCommand
{
	use Modularize {
		getPath as getModularPath;
	}
	
	protected function getPath($name)
	{
		if ($module = $this->domain()) {
			$name = '\\'.Str::replaceFirst($module->namespaces->first(), '', $name);
			return $this->getModularPath($name);
		}
		
		return parent::getPath($name);
	}
	
	protected function rootNamespace()
	{
		if ($module = $this->domain()) {
			return $module->namespaces->first().'Tests';
		}
		
		return 'Tests';
	}
}
