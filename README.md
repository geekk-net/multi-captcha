# multi-captcha

Common php class interfaces for captchas. And various captchas implementation. 
You can use it for a switching of captcha's type in your project.

## Supported types of captcha

Now packages supports these types:

* Google ReCaptcha v2
* HCaptcha
* KCaptcha

You can add new one type. You need implement CaptchaInterface and CaptchaRequestInterface.

## Frameworks support

This package didn't tie with any framework. 
It doesn't work with specific framework's classes or interfaces, for example `Illuminate\Http\Request`
for http requests.

But you can ease create needed wrappers and factories for a working your framework and configuration files.

For Laravel, you can use the geekk/multi-captcha-laravel package.

## Using

Configuration array:

```php
$config = [
        'default' => 'hcaptcha',

        'connections' => [

            'recaptcha2' => [
                'driver' => 'recaptcha2',
                'site_key' => '...',
                'secret_key' => '...'
            ],

            'hcaptcha' => [
                'driver' => 'hcaptcha',
                'site_key' => '...',
                'secret_key' => '...'
            ],

            'kcaptcha' => [
                'driver' => 'kcaptcha',
                'show_credits' => false
            ]
        ]
]
```

If you plan to use KCaptcha, you need implement storage class:

```php
class CaptchaStore  implements CaptchaStoreInterface {

    protected $store;
    protected $prefix;
    protected $seconds;

    public function __construct(Repository $store, $prefix = 'kcaptcha:', int $seconds = 5*60)
    {
        $this->store = $store;
        $this->prefix = $prefix;
        $this->seconds = $seconds;
    }

    protected function getStoreKey($key)
    {
        return "$this->prefix:{$key}";
    }

    public function getValue(?string $key = null): ?string
    {
        $value = $this->store->get($this->getStoreKey($key));
        $this->store->forget($this->getStoreKey($key));
        return $value;
    }

    public function setValue(string $value, ?string $key = null)
    {
        $this->store->put($this->getStoreKey($key), $value);
    }
}
```

where Repository is some cache repository. Or you can use session instead of cache.

Implement the CaptchaManager - factory class:

```php
class CaptchaManager
{

    protected $config;

    protected $connectionName;

    protected $connectionConfig;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    private function loadDriverConfig()
    {
        $this->connectionName = $this->config['default'];
        $this->connectionConfig = $this->config['connections'][$this->connectionName];
    }

    public function getCaptcha(): CaptchaInterface
    {
        $this->loadDriverConfig();
        $driverName = $this->connectionConfig['driver'];
        switch ($driverName) {
            case 'recaptcha2':
                return new ReCaptcha2($this->connectionConfig);
            case 'hcaptcha':
                return new HCaptcha($this->connectionConfig);
            case 'kcaptcha':
                $store = new CaptchaStore();
                return new KCaptcha($this->connectionConfig, $store);
        }
        throw new \Exception(sprintf('Unknown captcha driver: %s', $driverName));
    }

    public function getRequest(Request $request): CaptchaRequestInterface
    {
        $driverName = $this->connectionConfig['driver'];
        switch ($driverName) {
            case 'recaptcha2':
                return new ReCaptcha2Request($request->post(ReCaptcha2Request::RESPONSE_NAME), $request->ip());
            case 'hcaptcha':
                return new HCaptchaRequest($request->post(HCaptchaRequest::RESPONSE_NAME), $request->ip());
            case 'kcaptcha':
                return new KCaptchaRequest($request->post(KCaptchaRequest::RESPONSE_NAME), $request->post(KCaptchaRequest::KEY_NAME));
        }
        throw new \Exception(sprintf('Unknown captcha driver: %s', $driverName));
    }
}
```

Then you can use it

```php

$captchaManager = new CaptchaManager($config);

$captcha = $captchaManager->getCaptcha();

// Render captcha in template
echo $captcha->render();

// Verify user's response
$result = $captcha->verify($captchaManager->getRequest($request));

```

## Customising captcha's view

Use css for a customizing.

For captcha's templates generated on frontend side you can get data from method `CaptchaInterface::getViewData()`.
