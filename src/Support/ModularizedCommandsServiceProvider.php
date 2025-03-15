<?php

namespace Azzarip\Domains\Support;

use Illuminate\Console\Application;
use Illuminate\Console\Application as Artisan;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand as OriginalMakeMigrationCommand;
use Illuminate\Support\ServiceProvider;
use Azzarip\Domains\Console\Commands\Database\SeedCommand;
use Azzarip\Domains\Console\Commands\Make\MakeCast;
use Azzarip\Domains\Console\Commands\Make\MakeChannel;
use Azzarip\Domains\Console\Commands\Make\MakeCommand;
use Azzarip\Domains\Console\Commands\Make\MakeComponent;
use Azzarip\Domains\Console\Commands\Make\MakeController;
use Azzarip\Domains\Console\Commands\Make\MakeEvent;
use Azzarip\Domains\Console\Commands\Make\MakeException;
use Azzarip\Domains\Console\Commands\Make\MakeFactory;
use Azzarip\Domains\Console\Commands\Make\MakeJob;
use Azzarip\Domains\Console\Commands\Make\MakeListener;
use Azzarip\Domains\Console\Commands\Make\MakeLivewire;
use Azzarip\Domains\Console\Commands\Make\MakeMail;
use Azzarip\Domains\Console\Commands\Make\MakeMiddleware;
use Azzarip\Domains\Console\Commands\Make\MakeMigration;
use Azzarip\Domains\Console\Commands\Make\MakeModel;
use Azzarip\Domains\Console\Commands\Make\MakeNotification;
use Azzarip\Domains\Console\Commands\Make\MakeObserver;
use Azzarip\Domains\Console\Commands\Make\MakePolicy;
use Azzarip\Domains\Console\Commands\Make\MakeProvider;
use Azzarip\Domains\Console\Commands\Make\MakeRequest;
use Azzarip\Domains\Console\Commands\Make\MakeResource;
use Azzarip\Domains\Console\Commands\Make\MakeRule;
use Azzarip\Domains\Console\Commands\Make\MakeSeeder;
use Azzarip\Domains\Console\Commands\Make\MakeTest;
use Livewire\Commands as Livewire;

class ModularizedCommandsServiceProvider extends ServiceProvider
{
	protected array $overrides = [
		'command.cast.make' => MakeCast::class,
		'command.controller.make' => MakeController::class,
		'command.console.make' => MakeCommand::class,
		'command.channel.make' => MakeChannel::class,
		'command.event.make' => MakeEvent::class,
		'command.exception.make' => MakeException::class,
		'command.factory.make' => MakeFactory::class,
		'command.job.make' => MakeJob::class,
		'command.listener.make' => MakeListener::class,
		'command.mail.make' => MakeMail::class,
		'command.middleware.make' => MakeMiddleware::class,
		'command.model.make' => MakeModel::class,
		'command.notification.make' => MakeNotification::class,
		'command.observer.make' => MakeObserver::class,
		'command.policy.make' => MakePolicy::class,
		'command.provider.make' => MakeProvider::class,
		'command.request.make' => MakeRequest::class,
		'command.resource.make' => MakeResource::class,
		'command.rule.make' => MakeRule::class,
		'command.seeder.make' => MakeSeeder::class,
		'command.test.make' => MakeTest::class,
		'command.component.make' => MakeComponent::class,
		'command.seed' => SeedCommand::class,
	];
	
	public function register(): void
	{
		// Register our overrides via the "booted" event to ensure that we override
		// the default behavior regardless of which service provider happens to be
		// bootstrapped first (this mostly matters for Livewire).
		$this->app->booted(function() {
			Artisan::starting(function(Application $artisan) {
				$this->registerMakeCommandOverrides();
				$this->registerMigrationCommandOverrides();
				$this->registerLivewireOverrides($artisan);
			});
		});
	}
	
	protected function registerMakeCommandOverrides()
	{
		foreach ($this->overrides as $alias => $class_name) {
			$this->app->singleton($alias, $class_name);
			$this->app->singleton(get_parent_class($class_name), $class_name);
		}
	}
	
	protected function registerMigrationCommandOverrides()
	{
		// Laravel 8
		$this->app->singleton('command.migrate.make', function($app) {
			return new MakeMigration($app['migration.creator'], $app['composer']);
		});
		
		// Laravel 9
		$this->app->singleton(OriginalMakeMigrationCommand::class, function($app) {
			return new MakeMigration($app['migration.creator'], $app['composer']);
		});
	}
	
	protected function registerLivewireOverrides(Artisan $artisan)
	{
		// Don't register commands if Livewire isn't installed
		if (! class_exists(Livewire\MakeCommand::class)) {
			return;
		}
		
		// Replace the resolved command with our subclass
		$artisan->resolveCommands([MakeLivewire::class]);
		
		// Ensure that if 'make:livewire' or 'livewire:make' is resolved from the container
		// in the future, our subclass is used instead
		$this->app->extend(Livewire\MakeCommand::class, function() {
			return new MakeLivewire();
		});
		$this->app->extend(Livewire\MakeLivewireCommand::class, function() {
			return new MakeLivewire();
		});
	}
}
