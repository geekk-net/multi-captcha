<?php

namespace Geekk\MultiCaptcha\Turnstile;

use Geekk\MultiCaptcha\BaseCaptcha;
use Geekk\MultiCaptcha\CaptchaInterface;
use Geekk\MultiCaptcha\CaptchaRequestInterface;
use Geekk\MultiCaptcha\NeedExtraJs;
use GuzzleHttp\Client;

/**
 * Captcha driver. Cloudflare Turnstile
 */
class Turnstile extends BaseCaptcha implements CaptchaInterface, NeedExtraJs
{

    protected $siteKey;

    protected $secretKey;

    public function __construct(array $config)
    {
        $this->siteKey = $config['site_key'];
        $this->secretKey = $config['secret_key'];
    }

    public function render(): string
    {
        $viewData = $this->getViewData();
        return sprintf($this->getTemplate(), $viewData['site_key']);
    }

    public function getTemplate(): string
    {
        return
            '<div class="multi-captcha-turnstile">'.
                '<div class="cf-turnstile" data-sitekey="%s"></div>'.
            '</div>';
    }

    public function getViewData(): array
    {
        return [
            'site_key' => $this->siteKey,
        ];
    }

    public function verify(CaptchaRequestInterface $captchaRequest): bool
    {
        $this->request = $captchaRequest;
        $response = $captchaRequest->getResponseValue();
        if (empty($response)) {
            return false;
        }

        $client = new Client([
            'base_uri' => 'https://challenges.cloudflare.com',
            'http_errors' => false,
        ]);
        $result = $client->request('POST', '/turnstile/v0/siteverify', [
            'form_params' => [
                'secret' => $this->secretKey,
                'response' => $response,
                'remoteip' => $captchaRequest->getContext(),
            ]
        ]);
        $body = $result->getBody();
        $data = json_decode($body);
        return !empty($data->success);
    }

    public function getJsUrl(): ?string
    {
        return 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    }
}
