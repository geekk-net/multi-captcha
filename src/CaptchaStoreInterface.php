<?php

namespace Geekk\MultiCaptcha;

/**
 * Interface for storing captcha's values
 * Image based captcha need it for comparing with user's response
 */
interface CaptchaStoreInterface
{

    /**
     * Gets captcha's value from storage by key. Key can be empty for session storage
     * @param string|null $key
     * @return string
     */
    public function getValue(?string $key = null):?string;

    /**
     * Sets captcha's value to storage by key. Key can be empty for session storage
     * @param string $value
     * @param string|null $key
     * @return mixed
     */
    public function setValue(string $value, ?string $key = null);
}
