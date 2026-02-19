<?php

namespace Geekk\MultiCaptcha\HCaptcha;

use Geekk\MultiCaptcha\BaseCaptcha;
use Geekk\MultiCaptcha\CaptchaInterface;
use Geekk\MultiCaptcha\CaptchaRequestInterface;
use Geekk\MultiCaptcha\NeedExtraJs;
use GuzzleHttp\Client;

/**
 * Captcha driver. Google hCapctha
 */
class HCaptcha extends BaseCaptcha implements CaptchaInterface, NeedExtraJs
{

    protected $siteKey;

    protected $secretKey;

    public function __construct(array $config)
    {
        $this->siteKey = $config['site_key'];
        $this->secretKey = $config['secret_key'];
    }

    /**
     * HTML code for captcha
     * @return string
     */
    public function render(): string
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
        return
            '<div class="multi-captcha-hcaptcha">'.
                '<div class="h-captcha" data-sitekey="%s"></div>'.
            '</div>';
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
        $this->request = $captchaRequest;
        $responce = $captchaRequest->getResponseValue();
        if (empty($responce)) {
            return false;
        }
        $client = new Client(['base_uri' => 'https://hcaptcha.com']);
        $response = $client->request('POST', '/siteverify', [
            'form_params' => [
                'secret' => $this->secretKey,
                'response' => $responce,
                'remoteip' => $captchaRequest->getContext()
            ]
        ]);
        $body = $response->getBody();
        $data = json_decode($body);
        return !empty($data->success);
    }

    /**
     * JS file for captcha
     * @return string|null
     */
    public function getJsUrl(): ?string
    {
        return "https://hcaptcha.com/1/api.js";
    }
}
