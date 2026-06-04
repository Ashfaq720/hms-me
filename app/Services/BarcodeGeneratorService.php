<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeGeneratorService
{
    protected BarcodeGeneratorPNG $generator;

    public function __construct()
    {
        $this->generator = new BarcodeGeneratorPNG();
    }

    public function make(string $code, string $type = 'C128', int $scale = 2, int $height = 40): string
    {
        $map = [
            'C128' => \Picqer\Barcode\BarcodeGeneratorPNG::TYPE_CODE_128,
            'C39' => \Picqer\Barcode\BarcodeGeneratorPNG::TYPE_CODE_39,
            'EAN13' => \Picqer\Barcode\BarcodeGeneratorPNG::TYPE_EAN_13,
        ];
        $barType = $map[$type] ?? \Picqer\Barcode\BarcodeGeneratorPNG::TYPE_CODE_128;
        $png = $this->generator->getBarcode($code, $barType, $scale, $height);
        return 'data:image/png;base64,'.base64_encode($png);
    }
}
