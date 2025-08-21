<?php

namespace Azzarip\Domains\Console\Commands\Make;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MakeController extends ControllerMakeCommand
{
	use Modularize;
	
	protected function parseModel($model)
	{
		if (! $domain = $this->domain()) {
			return parent::parseModel($model);
		}
		
		if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
			throw new InvalidArgumentException('Model name contains invalid characters.');
		}
		
		$model = trim(str_replace('/', '\\', $model), '\\');
		
		if (! Str::startsWith($model, $namespace = $domain->namespaces->first())) {
			$model = $namespace.$model;
		}
		
		return $model;
	}
}
