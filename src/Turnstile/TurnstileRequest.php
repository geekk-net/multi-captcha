<?php

namespace Geekk\MultiCaptcha\Turnstile;

use Geekk\MultiCaptcha\CaptchaRequest;

/**
 * User's request with checking Cloudflare Turnstile.
 */
class TurnstileRequest extends CaptchaRequest
{

    public const RESPONSE_NAME = 'cf-turnstile-response';

    public function __construct(?bool $submitted, ?string $response, ?string $ip)
    {
        parent::__construct($submitted, $response, $ip);
    }
}
