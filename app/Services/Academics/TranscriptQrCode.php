<?php

namespace App\Services\Academics;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TranscriptQrCode
{
    public function svg(string $verificationUrl, int $size = 180): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($size, 2), new SvgImageBackEnd));
        return preg_replace('/<\?xml[^>]+\?>\s*/', '', $writer->writeString($verificationUrl)) ?? '';
    }
}
