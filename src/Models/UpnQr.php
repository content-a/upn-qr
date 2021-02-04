<?php

namespace UpnQr\Models;

use Endroid\QrCode\QrCode;
use Imagine;
use Imagine\Gd\Font;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\Point;
use Imagine\Image\PointInterface;

class UpnQr {

    private $imagine;
    private $image;

    private $font;

    // Data
    public $placnik_ime;
    public $placnik_ulica;
    public $placnik_kraj;
    public $namen;
    public $datum_placila;
    public $znesek;
    public $prejemnik_iban;
    public $prejemnik_referenca;
    public $prejemnik_ime;
    public $prejemnik_ulica;
    public $prejemnik_kraj;
    public $koda_namena;

    public function __construct()
    {
        $this->imagine = new Imagine\Gd\Imagine();

        // Set upn template image.
        $base_directory = dirname(__DIR__, 2);
        $this->image = $this->imagine->open($base_directory . '/assets/upn-qr-min.png');

        // Set font family, color and size.
        $font_path = $base_directory . '/assets/fonts/arial.ttf';
        $font_color = new Color('000');
        $this->font = new Font($font_path, 20, $font_color);
    }

    /**
     * Sets texts on upn.
     *
     */
    public function setTexts(){
        // Placnik
        $this->image->draw()->text($this->placnik_ime, $this->font, new Point(50,80));
        $this->image->draw()->text($this->placnik_ime, $this->font, new Point(980,225));
        $this->image->draw()->text($this->placnik_ulica, $this->font, new Point(50,110));
        $this->image->draw()->text($this->placnik_ulica, $this->font, new Point(980,270));
        $this->image->draw()->text($this->placnik_kraj, $this->font, new Point(50,140));
        $this->image->draw()->text($this->placnik_kraj, $this->font, new Point(980,315));

        // Namen
        $this->image->draw()->text($this->namen . ",", $this->font, new Point(50,230));
        $this->image->draw()->text($this->namen, $this->font, new Point(790,480));

        // Datum placila
        $this->image->draw()->text("rok: " . $this->datum_placila, $this->font, new Point(50,260));
        $this->image->draw()->text($this->datum_placila, $this->font, new Point(1610,395));

        // Znesek
        $znesek = number_format((float) $this->znesek, 2, ',', '');
        $this->image->draw()->text($znesek, $this->font, new Point(160,345));
        $this->image->draw()->text($znesek, $this->font, new Point(1060,395));

        // Prejemnik IBAN
        $this->image->draw()->text($this->prejemnik_iban, $this->font, new Point(50,425));
        $this->image->draw()->text($this->prejemnik_iban, $this->font, new Point(590,560));

        // Prejemnik referenca
        $this->image->draw()->text($this->prejemnik_referenca, $this->font, new Point(50,535));
        $this->image->draw()->text(substr($this->prejemnik_referenca, 0, 4), $this->font, new Point(585,630));
        $this->image->draw()->text(substr($this->prejemnik_referenca, 4), $this->font, new Point(730,630));

        // Prejemnik
        $this->image->draw()->text($this->prejemnik_ime, $this->font, new Point(50,620));
        $this->image->draw()->text($this->prejemnik_ime, $this->font, new Point(590,705));
        $this->image->draw()->text($this->prejemnik_ulica, $this->font, new Point(50,650));
        $this->image->draw()->text($this->prejemnik_ulica, $this->font, new Point(590,755));
        $this->image->draw()->text($this->prejemnik_kraj, $this->font, new Point(50,680));
        $this->image->draw()->text($this->prejemnik_kraj, $this->font, new Point(590,795));

        // Koda namena
        $this->image->draw()->text($this->koda_namena, $this->font, new Point(635,480));
    }

    /**
     * Generate qr and add it to upn.
     *
     */
    public function generate_qr(){
        // Format price.
        $qr_price = number_format((float) $this->znesek, 2, ',', '');
        $qr_price = str_replace(",", "", $qr_price);
        for ($i = strlen($qr_price); $i < 11; $i++)
            $qr_price = "0" . $qr_price;

        // Remove spaces.
        $iban_qr =  str_replace(' ', '', $this->prejemnik_iban);
        $referenca_qr = str_replace(' ', '', $this->prejemnik_referenca);

        // Set format for qr string.
        $qr_string = "UPNQR\n\n\n\n\n{$this->placnik_ime}\n\n\n"
            . "$qr_price\n\n\n{$this->koda_namena}\n{$this->namen}\n{$this->datum_placila}\n$iban_qr\n$referenca_qr\n"
            . "{$this->prejemnik_ime}\n{$this->prejemnik_ulica}\n{$this->prejemnik_kraj}\n";

        // Qr string needs be of size 412 chars. So we fill empty chars with spaces.
        $kontrolna_vsota = strlen($qr_string);
        $qr_string .= $kontrolna_vsota . "\n";
        for ($i = strlen($qr_string); $i < 411; $i++)
            $qr_string .= " ";

        // Create a basic QR code
        $qrCode = new QrCode($qr_string);
        $qrCode->setSize(320);
        $qrCode->setRoundBlockSize(false, QrCode::ROUND_BLOCK_SIZE_MODE_ENLARGE);
        $qrCode->setMargin(0);

        // Add qr to upn image.
        $image_qr = $this->imagine->load($qrCode->writeString());
        $this->image->paste($image_qr, new Point(598,88));
    }

    /**
     * Save image to selected path.
     *
     * @param string $path to save file
     */
    public function save($path, $width = 1200, $height = 566){
        // Sets texts.
        $this->setTexts();

        // Add qr code.
        $this->generate_qr();

        // Resize image
        $this->image->resize(new Box($width, $height));

        // Save to path.
        $this->image->save($path);
    }
}
