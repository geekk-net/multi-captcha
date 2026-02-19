<?php

namespace Geekk\MultiCaptcha\HCaptcha;

use Geekk\MultiCaptcha\CaptchaRequest;

/*
 * User's request with checking hCapctha.
 */
class HCaptchaRequest extends CaptchaRequest
{

    public const RESPONSE_NAME = 'h-captcha-response';

    public function __construct(?bool $submitted, ?string $response, ?string $ip)
    {
        parent::__construct($submitted, $response, $ip);
    }
}
