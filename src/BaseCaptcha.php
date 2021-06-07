<?php

namespace Geekk\MultiCaptcha;

/**
 * Base class for captcha's driver
 */
abstract class BaseCaptcha
{

    /**
     * @var BaseCaptchaRequest $request
     */
    protected $request;

    /**
     * Form submission flag
     * @return bool|null
     */
    public function isSubmitted(): ?bool
    {
        if(empty($this->request)) {
            return null;
        }
        return $this->request->isSubmitted();
    }
}
