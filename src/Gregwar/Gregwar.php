<?php

namespace Geekk\MultiCaptcha\Gregwar;

use Geekk\MultiCaptcha\BaseCaptcha;
use Geekk\MultiCaptcha\CaptchaInterface;
use Geekk\MultiCaptcha\CaptchaRequestInterface;
use Geekk\MultiCaptcha\CaptchaStoreInterface;
use Gregwar\Captcha\CaptchaBuilder;

/**
 * Captcha driver based on gregwar/captcha (image captcha).
 */
class Gregwar extends BaseCaptcha implements CaptchaInterface
{
    /**
     * @var CaptchaStoreInterface
     */
    protected $store;

    /**
     * @var array
     */
    protected $config;

    /**
     * Key for storing captcha's value.
     *
     * @var string
     */
    protected $storeKey;

    /**
     * Captcha value.
     *
     * @var string
     */
    protected $value;

    public function __construct(array $config, CaptchaStoreInterface $store)
    {
        $this->store = $store;

        $default = [
            'width' => 160,
            'height' => 80,
            'length' => 5,
            'quality' => 90,
            // alphabet without similar symbols (o=0, 1=l, i=j, t=f)
            'allowed_symbols' => '23456789abcdegikpqsvxyz',
        ];

        $this->config = array_merge($default, $config);
    }

    /**
     * Generates captcha image builder and stores expected value.
     */
    protected function generate(): CaptchaBuilder
    {
        $builder = new CaptchaBuilder();

        $this->value = $this->generateValue();
        $builder->setPhrase($this->value);

        $builder->build(
            (int) $this->config['width'],
            (int) $this->config['height']
        );

        $this->storeKey = uniqid('', true);
        $this->store->setValue($this->value, $this->storeKey);

        return $builder;
    }

    /**
     * Generates random phrase for captcha.
     */
    protected function generateValue(): string
    {
        $alphabet = (string) ($this->config['allowed_symbols'] ?? '23456789abcdegikpqsvxyz');
        $length = (int) $this->config['length'];
        $maxIndex = strlen($alphabet) - 1;

        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $alphabet[random_int(0, $maxIndex)];
        }

        return $result;
    }

    protected function convertToBase64(CaptchaBuilder $builder): string
    {
        ob_start();
        // Use PNG as image type and configured quality
        $builder->setImageType('png');
        $builder->output((int) $this->config['quality']);
        $imageData = ob_get_contents();
        ob_end_clean();

        return base64_encode($imageData);
    }

    /**
     * HTML code for captcha.
     */
    public function render(): string
    {
        $builder = $this->generate();
        $imageBase64 = $this->convertToBase64($builder);
        $src = sprintf('data:image/png;base64,%s', $imageBase64);

        return sprintf($this->getTemplate(), $src, $this->storeKey);
    }

    /**
     * Template (without data) for captcha.
     */
    public function getTemplate(): string
    {
        return
            '<div class="multi-captcha-gregwar">' .
                '<div class="multi-captcha-gregwar__container">' .
                    '<div class="multi-captcha-gregwar__img">' .
                        '<img src="%s" alt="captcha"/>' .
                    '</div>' .
                    '<div class="multi-captcha-gregwar__input">' .
                        '<input type="hidden" name="gregwar-key" value="%s">' .
                        '<input type="text" name="gregwar-response" value="">' .
                    '</div>' .
                '</div>' .
            '</div>';
    }

    /**
     * Delimiter for site_key compact format: key + delimiter + base64(image).
     */
    public const SITE_KEY_DELIMITER = "\n";

    /**
     * Data for template / API. Single field site_key: compact format "{key}\n{base64Image}".
     *
     * @return array{site_key:string}
     */
    public function getViewData(): array
    {
        $builder = $this->generate();
        $imageDataBase64 = $this->convertToBase64($builder);

        return [
            'site_key' => $this->storeKey . self::SITE_KEY_DELIMITER . $imageDataBase64,
        ];
    }

    /**
     * Verification of captcha's response.
     */
    public function verify(CaptchaRequestInterface $captchaRequest): bool
    {
        $this->request = $captchaRequest;

        $correctValue = $this->store->getValue($captchaRequest->getContext());
        if (empty($correctValue)) {
            return false;
        }

        return $captchaRequest->getResponseValue() === $correctValue;
    }
}

