<?php

namespace Azzarip\Domains\Console\Commands\Make;

use Azzarip\Domains\Support\Facades\Modules;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Console\ListenerMakeCommand;

class MakeListener extends ListenerMakeCommand
{
	use Modularize;
	
	protected function buildClass($name)
	{
		$event = $this->option('event');
		
		if (Modules::moduleForClass($name)) {
			$stub = str_replace(
				['DummyEvent', '{{ event }}'],
				class_basename($event),
				GeneratorCommand::buildClass($name)
			);
			
			return str_replace(
				['DummyFullEvent', '{{ eventNamespace }}'],
				trim($event, '\\'),
				$stub
			);
		}
		
		return parent::buildClass($name);
	}
}
