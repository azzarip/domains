<?php

namespace Azzarip\Domains\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Sitemap\Sitemap;

class GenerateSitemap extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'sitemap:generate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate the XML Sitemap for each domain';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		foreach (config('domains') as $key => $domain) {
			$directory = storage_path('app/sitemaps');
			if (! File::exists($directory)) {
				File::makeDirectory($directory, 0755, true);
			}

			$file = base_path("domains/$key/sitemap.php");
			if (! file_exists($file)) {
				continue;
			}

			$this->info('Sitemap for: '.$key);

			$sitemap = Sitemap::create();
			$entries = include $file;
			foreach ($entries as $entry) {
				$entry->setUrl(durl($entry->url, $key)->url());
				$sitemap->add($entry);
			}
			$sitemap->writeToFile($directory."/$key.xml");
		}

		return self::SUCCESS;
	}
}
