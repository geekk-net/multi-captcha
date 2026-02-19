<?php

namespace Geekk\MultiCaptcha\ReCaptcha2;

use Geekk\MultiCaptcha\CaptchaRequest;

/*
 * User's request with checking Google reCapctha v2.
 */
class ReCaptcha2Request extends CaptchaRequest
{

    public const RESPONSE_NAME = 'g-recaptcha-response';

    public function __construct(?bool $submitted, ?string $response, ?string $ip)
    {
        parent::__construct($submitted, $response, $ip);
    }
}
