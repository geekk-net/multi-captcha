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

This package does not tied with any framework. 
It doesn't work with specific framework's classes or interfaces, for example `Illuminate\Http\Request`
for http requests.

But you can ease create needed wrappers and factories for a working your framework and configuration files.



## Example for Laravel

For example, for Laravel you have configuration file captcha.php:

```php
return [
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

You can create abstraction for a working with `Illuminate\Http\Request` 

```php
namespace App\Captcha;

use Geekk\MultiCaptcha\ReCaptcha2\ReCaptcha2Request as Base;
use Illuminate\Http\Request;

/**
 * ReCaptcha2Request for Laravel
 */
class ReCaptcha2Request extends Base
{

    public static function instanceByRequest(Request $request):self
    {
        return new static($request->post(self::RESPONSE_NAME), $request->ip());
    }

}
```

And then you can create CaptchaManager

```php
namespace App\Captcha;

use Illuminate\Http\Request;
use Geekk\MultiCaptcha\ReCaptcha2\ReCaptcha2;
use Geekk\MultiCaptcha\HCaptcha\HCaptcha;
use Geekk\MultiCaptcha\KCaptcha\KCaptcha;
use Geekk\MultiCaptcha\CaptchaInterface;
use Geekk\MultiCaptcha\CaptchaRequestInterface;


/**
 * Creates object of captcha by configuration file's settings
 */
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
                return new ReCaptcha2($this->connectionConfig['site_key'], $this->connectionConfig['secret_key']);
            case 'hcaptcha':
                return new HCaptcha($this->connectionConfig['site_key'], $this->connectionConfig['secret_key']);
            case 'kcaptcha':
                $store = new CaptchaStore(app('cache')->store());
                return new KCaptcha($this->connectionConfig, $store);
        }
        throw new \Exception(sprintf('Unknown captcha driver: %s', $driverName));
    }

    public function getRequest(Request $request): CaptchaRequestInterface
    {
        $driverName = $this->connectionConfig['driver'];
        switch ($driverName) {
            case 'recaptcha2':
                return ReCaptcha2Request::instanceByRequest($request);
            case 'hcaptcha':
                return HCaptchaRequest::instanceByRequest($request);
            case 'kcaptcha':
                return KCaptchaRequest::instanceByRequest($request);
        }
        throw new \Exception(sprintf('Unknown captcha driver: %s', $driverName));
    }
```

For kcaptcha you need implement CaptchaStoreInterface:

```php
namespace App\Captcha;

use Geekk\MultiCaptcha\CaptchaStoreInterface;
use Illuminate\Contracts\Cache\Repository;

class CaptchaStore implements CaptchaStoreInterface
{

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

Create service provider:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Captcha\CaptchaManager;

class CaptchaServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(CaptchaManager::class, function ($app) {
            return new CaptchaManager(config('captcha'));
        });
    }
}
```

Add `App\Providers\CaptchaServiceProvider::class` to config/app.php

Then you can use it

```php
$captcha = $captchaManager->getCaptcha();

// Render captcha in template
echo $captcha->render();

// Verify user's response
$result = $captcha->verify($captchaManager->getRequest($request));

```
