<?php

namespace Geekk\MultiCaptcha\KCaptcha;

use Geekk\MultiCaptcha\CaptchaRequestInterface;

/*
 * User's request with checking KCapctha.
 */
class KCaptchaRequest implements CaptchaRequestInterface
{

    protected $response;
    protected $key;

    public const RESPONSE_NAME = 'k-captcha-response';

    public const KEY_NAME = 'k-captcha-key';

    public function __construct($response, $key)
    {
        $this->response = $response;
        $this->key = $key;
    }

    public function getResponseValue(): ?string
    {
        return $this->response;
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
