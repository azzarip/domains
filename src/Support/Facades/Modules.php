<?php

namespace Azzarip\Domains\Support\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Azzarip\Domains\Support\ModuleConfig;
use Azzarip\Domains\Support\DomainRegistry;

/**
 * @method static ModuleConfig|null module(string $name)
 * @method static ModuleConfig|null moduleForPath(string $path)
 * @method static ModuleConfig|null moduleForClass(string $fqcn)
 * @method static Collection modules()
 * @method static Collection reload()
 *
 * @see \Azzarip\Domains\Support\DomainRegistry
 */
class Modules extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return DomainRegistry::class;
	}
}
