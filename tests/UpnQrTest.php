<?php

namespace UpnQr\Tests;

use Endroid\QrCode\QrCode;
use PHPUnit\Framework\TestCase;
use UpnQr\Models\UpnQr;

final class UpnQrTest extends TestCase{

    public function testGenerateUpnQr(){
        $upn = new UpnQr();

        $upn->placnik_ime = "G. ROK LESJAK";
        $upn->placnik_ulica = "JANŠKOVO SELO 21";
        $upn->placnik_kraj = "3320 VELENJE";
        $upn->namen = "Storitve 7.9. do 6.10.2020";
        $upn->datum_placila = "22.10.2020";
        $upn->znesek = 122.30;
        $upn->prejemnik_iban = "SI56 2900 0015 9800 373";
        $upn->prejemnik_referenca = "SI12 2010075988875";
        $upn->prejemnik_ime = "A1 Slovenija, d. d.";
        $upn->prejemnik_ulica = "Šmartinska cesta 134b";
        $upn->prejemnik_kraj = "1000 Ljubljana";
        $upn->koda_namena = "OTHR";

        $upn->save('upn_example.png');
    }
}

