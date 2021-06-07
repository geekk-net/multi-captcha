<?php

namespace Geekk\MultiCaptcha\HCaptcha;

use Geekk\MultiCaptcha\BaseCaptchaRequest;

/*
 * User's request with checking hCapctha.
 */
class HCaptchaRequest extends BaseCaptchaRequest
{

    protected $ip;

    public const RESPONSE_NAME = 'h-captcha-response';

    public function __construct(?bool $submitted, ?string $response, ?string $ip)
    {
        $this->submitted = $submitted;
        $this->response = $response;
        $this->ip = $ip;
    }

    public function getIP(): ?string
    {
        return $this->ip;
    }
}
