<?php

namespace Azzarip\Domains;

use Azzarip\Client\CookieConsent\CookieConsent;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

class DUrl
{
	public function __construct(public string $uri, public string $domainKey, public array $data = [])
	{
		if (empty(config('domains.'.$this->domainKey))) {
			throw new \Exception('Wrong domain in durl.');
		}
	}

	public function url()
	{
		$url = config("domains.{$this->domainKey}.url");

		$uri = ltrim($this->uri, '/');

		if (! empty($uri)) {
			$url .= '/'.$uri;
		}

		if (! empty($this->data)) {
			$url .= '?'.Arr::query($this->data);
		}

		if (request()->isSecure()) {
			return 'https://'.$url;
		}

		return 'http://'.$url;
	}

	public function withAll()
	{
		$this->withCookieConsent();
		$this->withUtmToken();

		return $this;
	}

	public function withCookieConsent()
	{
		$cookieConsent = CookieConsent::get();
		if ($cookieConsent) {
			$this->data['cc'] = $cookieConsent->toUrl();
		}

		return $this;
	}

	public function withUtmToken()
	{
		$token = Session::get('utm');
		if ($token) {
			$this->data['utt'] = $token;
		}

		return $this;
	}

	public function __toString(): string
	{
		return $this->url();
	}
}
