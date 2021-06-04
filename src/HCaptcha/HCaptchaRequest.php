<?php

namespace Geekk\MultiCaptcha\HCaptcha;

use Geekk\MultiCaptcha\CaptchaRequestInterface;

/*
 * User's request with checking hCapctha.
 */
class HCaptchaRequest implements CaptchaRequestInterface
{

    protected $response;

    protected $ip;

    public const RESPONSE_NAME = 'h-captcha-response';

    public function __construct($response, $ip)
    {
        $this->response = $response;
        $this->ip = $ip;
    }

    public function getResponseValue(): ?string
    {
        return $this->response;
    }

    public function getIP(): ?string
    {
        return $this->ip;
    }
}
