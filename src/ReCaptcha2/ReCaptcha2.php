<?php

namespace Geekk\MultiCaptcha\ReCaptcha2;

use GuzzleHttp\Client;
use Geekk\MultiCaptcha\CaptchaInterface;
use Geekk\MultiCaptcha\CaptchaRequestInterface;
use Geekk\MultiCaptcha\NeedExtraJs;

/**
 * Captcha driver. Google reCapctha v2
 */
class ReCaptcha2 implements CaptchaInterface, NeedExtraJs
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
    public function render():string
    {
        $viewData = $this->getViewData();
        return sprintf($this->getTemplate(), $viewData['site_key']);
    }

    /**
     * Template (without data) for captcha
     * @return string
     */
    public function getTemplate(): string
    {
        return '<div class="g-recaptcha" data-sitekey="%s"></div>';
    }

    /**
     * Data for template (render)
     * @return array
     */
    public function getViewData(): array
    {
        return [
            'site_key' => $this->siteKey,
        ];
    }

    /**
     * Verification of captcha's responce
     * @param CaptchaRequestInterface $captchaRequest
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verify(CaptchaRequestInterface $captchaRequest): bool
    {
        if (($captchaRequest instanceof ReCaptcha2Request) === false) {
            throw new \Exception("argument should be instnase of ".ReCaptcha2Request::class);
        }
        $responce = $captchaRequest->getResponseValue();
        if (empty($responce)) {
            return false;
        }
        $client = new Client(['base_uri' => 'https://www.google.com/recaptcha/api/']);
        $response = $client->request('POST', 'siteverify', [
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
        return "https://www.google.com/recaptcha/api.js";
    }
}
