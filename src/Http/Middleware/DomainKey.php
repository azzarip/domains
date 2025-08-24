<?php

namespace Azzarip\Domains\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DomainKey
{
	protected ?string $domain;

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		$this->domain = request()->getHost();

		foreach (config('domains') as $key => $value) {
			if ($value['url'] == $this->domain) {
				$request->attributes->set('domainKey', $key);
				config()->set('seo.site_name', $value['name']);
				config()->set('app.url', durl('/', $key, []));
				config()->set('session.domain', '.'.$this->getBaseDomain());

				break;
			}
		}

		return $next($request);
	}

	protected function getBaseDomain(): string
	{
		$parts = explode('.', $this->domain);
		$count = count($parts);
	
		if ($count >= 2) {
			return $parts[$count - 2].'.'.$parts[$count - 1];
		}
		
		return $this->domain;
	}
}
