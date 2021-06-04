<?php

namespace Geekk\MultiCaptcha;

/**
 * Interface for driver of captcha
 */
interface CaptchaInterface
{

    /**
     * HTML code for captcha
     * @return string
     */
    public function render():string;

    /**
     * Verification of captcha's responce
     * @param CaptchaRequestInterface $captchaRequest
     * @return bool
     */
    public function verify(CaptchaRequestInterface $captchaRequest): bool;
}
