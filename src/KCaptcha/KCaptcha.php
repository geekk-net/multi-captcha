<?php

namespace Geekk\MultiCaptcha\KCaptcha;

use Exception;
use Geekk\MultiCaptcha\CaptchaInterface;
use Geekk\MultiCaptcha\CaptchaRequestInterface;
use Geekk\MultiCaptcha\CaptchaStoreInterface;
use Composer\Autoload\ClassLoader;

/**
 * Captcha driver. KCapctha - http://www.captcha.ru/kcaptcha/
 */
class KCaptcha implements CaptchaInterface
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
     * Key for storing captcha's value
     * @var string
     */
    protected $storeKey;

    /**
     * Capctha's value
     * @var string
     */
    protected $value;

    public function __construct(array $config, CaptchaStoreInterface $store)
    {
        $this->store = $store;

        // Settings by default
        $default = [
            //alphabet without similar symbols (o=0, 1=l, i=j, t=f)
            'allowed_symbols' => "23456789abcdegikpqsvxyz",
            // CAPTCHA string length
            'length' => mt_rand(5, 7), // random 5 or 6 or 7
            // CAPTCHA image size (you do not need to change it, this parameters is optimal)
            'width' => 160,
            'height' => 80,
            // symbol's vertical fluctuation amplitude
            'fluctuation_amplitude' => 8,
            //noise
            'white_noise_density'=> 1/6,
            'black_noise_density' => 1/30,
            // increase safety by prevention of spaces between symbols
            'no_spaces' => true,
            // show credits
            'show_credits' => true, // set to false to remove credits line. Credits adds 12 pixels to image height
            'credits' => 'www.captcha.ru', // if empty, HTTP_HOST will be shown
            // CAPTCHA image colors (RGB, 0-255)
            'foreground_color' => array(mt_rand(0, 80), mt_rand(0, 80), mt_rand(0, 80)),
            'background_color' => array(mt_rand(220, 255), mt_rand(220, 255), mt_rand(220, 255)),
        ];
        // Apply settings from config
        $this->config = array_merge($default, $config);
        if(empty($this->config['fontsdir_absolute'])) {
            // Folder with fonts
            $this->config['fontsdir_absolute'] = $this->getVendorPath().'geekk/multi-captcha/resources/fonts/kcaptcha/';
        }
        if(empty($this->config['alphabet'])) {
            // do not change without changing font files!
            $this->config['alphabet'] = "0123456789abcdefghijklmnopqrstuvwxyz";
        }
    }

    /**
     * Get path to "vendor" folder
     * @return string
     */
    private function getVendorPath()
    {
        $reflector = new \ReflectionClass(ClassLoader::class);
        $vendorPath = preg_replace('/^(.*)\/composer\/ClassLoader\.php$/', '$1', $reflector->getFileName() );
        if($vendorPath && is_dir($vendorPath)) {
            return $vendorPath . '/';
        }
        throw new \RuntimeException('Unable to detect vendor path.');
    }

    protected function generate()
    {
        $this->value = $this->generateValue();
        $fonts = $this->buildFonts();
        $font = imagecreatefrompng($fonts[mt_rand(0, count($fonts)-1)]);
        $img = $this->buildImg($font, $x);
        $this->applyNoice($font, $img, $x);
        $img2 = $this->makeContainer();
        $this->applyWaveDistortion($img, $img2, $x);
        $this->storeKey = uniqid(rand(), true);
        $this->store->setValue($this->value, $this->storeKey);
        return $img2;
    }

    /**
     * Make separated symbols of font
     * @param $font
     * @return array
     */
    protected function makeFontFragments($font)
    {
        $alphabet = $this->config['alphabet'];
        $alphabetLength = strlen($alphabet);

        $fontMetrics=array();
        $symbol=0;
        $readingSymbol=false;

        $fontfile_width=imagesx($font);

        // loading font
        for ($i=0; $i<$fontfile_width && $symbol<$alphabetLength; $i++) {
            $transparent = (imagecolorat($font, $i, 0) >> 24) == 127;

            if (!$readingSymbol && !$transparent) {
                $fontMetrics[$alphabet{$symbol}]=array('start'=>$i);
                $readingSymbol=true;
                continue;
            }

            if ($readingSymbol && $transparent) {
                $fontMetrics[$alphabet{$symbol}]['end']=$i;
                $readingSymbol=false;
                $symbol++;
            }
        }
        return $fontMetrics;
    }

    /**
     * Build image with text
     * @param $font
     * @param $x
     * @return false|\GdImage|resource
     */
    protected function buildImg($font, &$x)
    {

        $amplitude = $this->config['fluctuation_amplitude'];
        do {
            imagealphablending($font, true);
            $fontfileHeight=imagesy($font)-1;
            $fontMetrics = $this->makeFontFragments($font);

            $img=imagecreatetruecolor($this->config['width'], $this->config['height']);
            imagealphablending($img, true);
            $white=imagecolorallocate($img, 255, 255, 255);
            $black=imagecolorallocate($img, 0, 0, 0);

            imagefilledrectangle($img, 0, 0, $this->config['width']-1, $this->config['height']-1, $white);

            // draw text
            $x=1;
            $odd=mt_rand(0, 1);
            if ($odd==0) {
                $odd=-1;
            }
            for ($i=0; $i<$this->config['length']; $i++) {
                $m=$fontMetrics[$this->value{$i}];

                $y=(($i%2)*$amplitude - $amplitude/2)*$odd
                    + mt_rand(-round($amplitude/3), round($amplitude/3))
                    + ($this->config['height']-$fontfileHeight)/2;

                if ($this->config['no_spaces']) {
                    $shift=0;
                    if ($i>0) {
                        $shift=10000;
                        for ($sy=3; $sy<$fontfileHeight-10; $sy+=1) {
                            for ($sx=$m['start']-1; $sx<$m['end']; $sx+=1) {
                                $rgb=imagecolorat($font, $sx, $sy);
                                $opacity=$rgb>>24;
                                if ($opacity<127) {
                                    $left=$sx-$m['start']+$x;
                                    $py=$sy+$y;
                                    if ($py>$this->config['height']) {
                                        break;
                                    }
                                    $width = $this->config['width'];
                                    for ($px=min($left, $width-1); $px>$left-200 && $px>=0; $px-=1) {
                                        $color=imagecolorat($img, $px, $py) & 0xff;
                                        if ($color+$opacity<170) { // 170 - threshold
                                            if ($shift>$left-$px) {
                                                $shift=$left-$px;
                                            }
                                            break;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                        if ($shift==10000) {
                            $shift=mt_rand(4, 6);
                        }
                    }
                } else {
                    $shift=1;
                }
                imagecopy($img, $font, $x-$shift, $y, $m['start'], 1, $m['end']-$m['start'], $fontfileHeight);
                $x+=$m['end']-$m['start']-$shift;
            }
        } while ($x>=$this->config['width']-10); // while not fit in canvas
        return $img;
    }

    /**
     * Fonts preparing
     * @return array
     */
    protected function buildFonts()
    {
        $fonts=array();
        if ($handle = opendir($this->config['fontsdir_absolute'])) {
            while (false !== ($file = readdir($handle))) {
                if (preg_match('/\.png$/i', $file)) {
                    $fonts[]=$this->config['fontsdir_absolute'].'/'.$file;
                }
            }
            closedir($handle);
        }
        return $fonts;
    }

    /**
     * Apply "noise"
     * @param $font
     * @param $img
     * @param $x
     */
    protected function applyNoice($font, $img, $x)
    {
        $white=imagecolorallocate($font, 255, 255, 255);
        $black=imagecolorallocate($font, 0, 0, 0);
        for ($i=0; $i<(($this->config['height']-30)*$x)*$this->config['white_noise_density']; $i++) {
            imagesetpixel($img, mt_rand(0, $x-1), mt_rand(10, $this->config['height']-15), $white);
        }
        for ($i=0; $i<(($this->config['height']-30)*$x)*$this->config['black_noise_density']; $i++) {
            imagesetpixel($img, mt_rand(0, $x-1), mt_rand(10, $this->config['height']-15), $black);
        }
    }

    /**
     * Make image-container
     * @return false|\GdImage|resource
     */
    protected function makeContainer()
    {
        $fgColor = $this->config['foreground_color'];
        $bgColor = $this->config['background_color'];
        $height = $this->config['height']+($this->config['show_credits']?12:0);
        $img2 = imagecreatetruecolor($this->config['width'], $height);
        $foreground = imagecolorallocate($img2, $fgColor[0], $fgColor[1], $fgColor[2]);
        $background = imagecolorallocate($img2, $bgColor[0], $bgColor[1], $bgColor[2]);
        imagefilledrectangle(
            $img2,
            0,
            0,
            $this->config['width']-1,
            $this->config['height']-1,
            $background
        );
        imagefilledrectangle(
            $img2,
            0,
            $this->config['height'],
            $this->config['width']-1,
            $this->config['height']+12,
            $foreground
        );
        imagestring(
            $img2,
            2,
            $this->config['width']/2-imagefontwidth(2)*strlen($this->config['credits'])/2,
            $this->config['height']-2,
            $this->config['credits'],
            $background
        );
        return $img2;
    }

    /**
     * Apply wave distortion
     * @param $img
     * @param $img2
     * @param $x
     */
    protected function applyWaveDistortion($img, $img2, $x)
    {

        // periods
        $rand1=mt_rand(750000, 1200000)/10000000;
        $rand2=mt_rand(750000, 1200000)/10000000;
        $rand3=mt_rand(750000, 1200000)/10000000;
        $rand4=mt_rand(750000, 1200000)/10000000;
        // phases
        $rand5=mt_rand(0, 31415926)/10000000;
        $rand6=mt_rand(0, 31415926)/10000000;
        $rand7=mt_rand(0, 31415926)/10000000;
        $rand8=mt_rand(0, 31415926)/10000000;
        // amplitudes
        $rand9=mt_rand(330, 420)/110;
        $rand10=mt_rand(330, 450)/100;

        $center=$x/2;

        $fbColor = $this->config['foreground_color'];
        $bGColor = $this->config['background_color'];
        for ($x=0; $x<$this->config['width']; $x++) {
            for ($y=0; $y<$this->config['height']; $y++) {
                $sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$this->config['width']/2+$center+1;
                $sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;

                if ($sx<0 || $sy<0 || $sx>=$this->config['width']-1 || $sy>=$this->config['height']-1) {
                    continue;
                } else {
                    $color=imagecolorat($img, $sx, $sy) & 0xFF;
                    $colorX=imagecolorat($img, $sx+1, $sy) & 0xFF;
                    $colorY=imagecolorat($img, $sx, $sy+1) & 0xFF;
                    $colorXY=imagecolorat($img, $sx+1, $sy+1) & 0xFF;
                }

                if ($color==255 && $colorX==255 && $colorY==255 && $colorXY==255) {
                    continue;
                } elseif ($color==0 && $colorX==0 && $colorY==0 && $colorXY==0) {
                    $newred=$fbColor[0];
                    $newgreen=$fbColor[1];
                    $newblue=$fbColor[2];
                } else {
                    $frsx=$sx-floor($sx);
                    $frsy=$sy-floor($sy);
                    $frsx1=1-$frsx;
                    $frsy1=1-$frsy;

                    $newcolor=(
                        $color*$frsx1*$frsy1+
                        $colorX*$frsx*$frsy1+
                        $colorY*$frsx1*$frsy+
                        $colorXY*$frsx*$frsy);

                    if ($newcolor>255) {
                        $newcolor=255;
                    }
                    $newcolor=$newcolor/255;
                    $newcolor0=1-$newcolor;

                    $newred=$newcolor0*$fbColor[0]+$newcolor*$bGColor[0];
                    $newgreen=$newcolor0*$fbColor[1]+$newcolor*$bGColor[1];
                    $newblue=$newcolor0*$fbColor[2]+$newcolor*$bGColor[2];
                }

                imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newred, $newgreen, $newblue));
            }
        }
    }

    /**
     * Generate value for captcha
     * @return string
     */
    protected function generateValue()
    {
        while (true) {
            $result='';
            for ($i=0; $i<$this->config['length']; $i++) {
                $result.=$this->config['allowed_symbols']{mt_rand(0, strlen($this->config['allowed_symbols'])-1)};
            }
            if (!preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $result)) {
                break;
            }
        }
        return $result;
    }

    /**
     * HTML code for captcha
     * @return string
     */
    public function render(): string
    {
        $viewData = $this->getViewData();
        return sprintf($this->getTemplate(), $viewData['src'], $viewData['key']);
    }

    /**
     * Template (without data) for captcha
     * @return string
     */
    public function getTemplate(): string
    {
        return
            '<img src="%s" alt="captcha"/><div/>'.
            '<input type="hidden" name="k-captcha-key" value="%s">'.
            '<input type="text" name="k-captcha-response" value="">';
    }

    /**
     * Convert image to base64
     * @param $img
     * @return string
     */
    protected function convertToBase64($img): string
    {
        ob_start();
        imagepng($img);
        $imageData = ob_get_contents();
        ob_end_clean();
        return base64_encode($imageData);
    }

    /**
     * Data for template (render)
     * @return array
     */
    public function getViewData(): array
    {
        $img = $this->generate();
        $imageDataBase64 = $this->convertToBase64($img);
        return [
            'src' => sprintf('data:image/png;base64,%s', $imageDataBase64),
            'key' => $this->storeKey
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
        if (($captchaRequest instanceof KCaptchaRequest) === false) {
            throw new Exception("argument should be instnase of ".KCaptchaRequest::class);
        }
        $correctValue = $this->store->getValue($captchaRequest->getKey());
        if (empty($correctValue)) {
            return false;
        }
        return ($captchaRequest->getResponseValue() === $correctValue);
    }
}
