<?php

namespace Geekk\MultiCaptcha;

/**
 * Implement it if captcha need extra js
 */
interface NeedExtraJs
{
    public function getJsUrl():?string;
}
