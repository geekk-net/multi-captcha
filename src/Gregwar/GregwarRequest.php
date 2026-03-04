<?php

namespace Geekk\MultiCaptcha\Gregwar;

use Geekk\MultiCaptcha\CaptchaRequest;

/*
 * User's request with checking Gregwar captcha.
 */
class GregwarRequest extends CaptchaRequest
{
    public const RESPONSE_NAME = 'gregwar-response';

    public const KEY_NAME = 'gregwar-key';

    public function __construct(?bool $submitted, ?string $response, ?string $key)
    {
        parent::__construct($submitted, $response, $key);
    }
}

