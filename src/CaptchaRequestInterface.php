<?php

namespace Geekk\MultiCaptcha;

/*
 * User's request with checking response.
 * For example, text for image based captcha or response value for recaptcha
 */
interface CaptchaRequestInterface
{

    /**
     * Response value
     * @return string|null
     */
    public function getResponseValue(): ?string;

    /**
     * Form submission flag
     * @return bool|null
     */
    public function isSubmitted(): ?bool;
}
