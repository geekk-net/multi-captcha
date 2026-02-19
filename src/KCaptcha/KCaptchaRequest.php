<?php

namespace Geekk\MultiCaptcha\KCaptcha;

use Geekk\MultiCaptcha\CaptchaRequest;

/*
 * User's request with checking KCapctha.
 */
class KCaptchaRequest extends CaptchaRequest
{

    public const RESPONSE_NAME = 'k-captcha-response';

    public const KEY_NAME = 'k-captcha-key';

    public function __construct(?bool $submitted, ?string $response, ?string $key)
    {
        parent::__construct($submitted, $response, $key);
    }
}
