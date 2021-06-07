<?php

namespace Geekk\MultiCaptcha;

/*
 * Base class for user's request with checking a captcha
 */
abstract class BaseCaptchaRequest implements CaptchaRequestInterface
{

    protected $submitted;

    protected $response;

    public function getResponseValue(): ?string
    {
        return $this->response;
    }

    public function isSubmitted(): ?bool
    {
        return $this->submitted;
    }

}
