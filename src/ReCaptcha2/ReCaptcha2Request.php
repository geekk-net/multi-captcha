<?php

namespace Geekk\MultiCaptcha\ReCaptcha2;

use Geekk\MultiCaptcha\CaptchaRequestInterface;

/*
 * User's request with checking Google reCapctha v2.
 */
class ReCaptcha2Request implements CaptchaRequestInterface
{

    protected $response;

    protected $ip;

    public const RESPONSE_NAME = 'g-recaptcha-response';

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
