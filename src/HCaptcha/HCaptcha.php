<?php

namespace Geekk\MultiCaptcha\HCaptcha;

use Geekk\MultiCaptcha\CaptchaInterface;
use Geekk\MultiCaptcha\CaptchaRequestInterface;
use Geekk\MultiCaptcha\NeedExtraJs;
use GuzzleHttp\Client;

/**
 * Captcha driver. Google hCapctha
 */
class HCaptcha implements CaptchaInterface, NeedExtraJs
{

    protected $siteKey;

    protected $secretKey;

    public function __construct(string $siteKey, string $secretKey)
    {
        $this->siteKey = $siteKey;
        $this->secretKey = $secretKey;
    }

    /**
     * HTML code for captcha
     * @return string
     */
    public function render(): string
    {
        return sprintf('<div class="h-captcha" data-sitekey="%s"></div>', $this->siteKey);
    }

    /**
     * Verification of captcha's responce
     * @param CaptchaRequestInterface $captchaRequest
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verify(CaptchaRequestInterface $captchaRequest): bool
    {
        if (($captchaRequest instanceof HCaptchaRequest) === false) {
            throw new \Exception("argument should be instnase of ".HCaptchaRequest::class);
        }

        $responce = $captchaRequest->getResponseValue();
        if (empty($responce)) {
            return false;
        }
        $client = new Client(['base_uri' => 'https://hcaptcha.com']);
        $response = $client->request('POST', '/siteverify', [
            'form_params' => [
                'secret' => $this->secretKey,
                'response' => $responce,
                'remoteip' => $captchaRequest->getIP()
            ]
        ]);
        $body = $response->getBody();
        $data = json_decode($body);
        return !empty($data->success);
    }

    /**
     * JS file for captcha
     * @return string
     */
    public function getJsUrl(): string
    {
        return "https://hcaptcha.com/1/api.js";
    }
}
