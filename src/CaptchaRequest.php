<?php

namespace Geekk\MultiCaptcha;

/*
 * User's request with checking a captcha.
 * Can be used directly when form field names are unified (e.g. Vue captcha component).
 */
class CaptchaRequest implements CaptchaRequestInterface
{

    protected $submitted;

    protected $response;

    protected $context;

    public function __construct(?bool $submitted, ?string $response, ?string $context = null)
    {
        $this->submitted = $submitted;
        $this->response = $response;
        $this->context = $context;
    }

    public function getResponseValue(): ?string
    {
        return $this->response;
    }

    public function isSubmitted(): ?bool
    {
        return $this->submitted;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

}
