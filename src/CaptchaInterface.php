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
     * Template (without data) for captcha
     * @return string
     */
    public function getTemplate(): string;

    /**
     * Data for template (render)
     * @return array
     */
    public function getViewData(): array;

    /**
     * Verification of captcha's responce
     * @param CaptchaRequestInterface $captchaRequest
     * @return bool
     */
    public function verify(CaptchaRequestInterface $captchaRequest): bool;

    /**
     * Form submission flag
     * @return bool|null
     */
    public function isSubmitted(): ?bool;
}
