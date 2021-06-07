<?php

namespace Geekk\MultiCaptcha\ReCaptcha2;

use Geekk\MultiCaptcha\BaseCaptchaRequest;

/*
 * User's request with checking Google reCapctha v2.
 */
class ReCaptcha2Request extends BaseCaptchaRequest
{

    protected $ip;

    public const RESPONSE_NAME = 'g-recaptcha-response';

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
