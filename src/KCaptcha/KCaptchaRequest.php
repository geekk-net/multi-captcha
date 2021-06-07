<?php

namespace Geekk\MultiCaptcha\KCaptcha;

use Geekk\MultiCaptcha\BaseCaptchaRequest;

/*
 * User's request with checking KCapctha.
 */
class KCaptchaRequest extends BaseCaptchaRequest
{

    protected $key;

    public const RESPONSE_NAME = 'k-captcha-response';

    public const KEY_NAME = 'k-captcha-key';

    public function __construct(?bool $submitted, ?string $response, ?string $key)
    {
        $this->submitted = $submitted;
        $this->response = $response;
        $this->key = $key;
    }

    /**
     * Getting key of store
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }
}
