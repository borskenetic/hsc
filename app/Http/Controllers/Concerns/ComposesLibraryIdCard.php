<?php

namespace App\Http\Controllers\Concerns;

use App\Support\PublicAssetPath;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

trait ComposesLibraryIdCard
{
    protected function idCardTemplate(string $side)
    {
        $path = PublicAssetPath::resolve("images/id_templates/{$side}.png")
            ?? base_path("images/id_templates/{$side}.png");

        return Image::make($path);
    }

    protected function drawIdCardText($img, $text, $x, $y, $size, $color = '#000', $align = 'center', $valign = 'top'): void
    {
        $fontPathBold = public_path('fonts/arialbd.ttf');
        $fontPathRegular = public_path('fonts/arial.ttf');

        if (file_exists($fontPathBold)) {
            $img->text($text, $x, $y, function ($font) use ($fontPathBold, $size, $color, $align, $valign) {
                $font->file($fontPathBold);
                $font->size($size);
                $font->color($color);
                $font->align($align);
                $font->valign($valign);
            });

            return;
        }

        foreach ([[-1, 0], [1, 0], [0, -1], [0, 1]] as [$ox, $oy]) {
            $img->text($text, $x + $ox, $y + $oy, function ($font) use ($fontPathRegular, $size, $color, $align, $valign) {
                $font->file($fontPathRegular);
                $font->size($size);
                $font->color($color);
                $font->align($align);
                $font->valign($valign);
            });
        }

        $img->text($text, $x, $y, function ($font) use ($fontPathRegular, $size, $color, $align, $valign) {
            $font->file($fontPathRegular);
            $font->size($size);
            $font->color($color);
            $font->align($align);
            $font->valign($valign);
        });
    }

    /**
     * @param  array{photo:?string,full_name:string,subtitle:?string,id_number:?string,qrcode:?string}  $data
     */
   protected function composeIdCardFront($img, array $data)
    {
        
        $photoPath = PublicAssetPath::resolve($data['photo'] ?? null);
        if ($photoPath) {
            $profile = Image::make($photoPath)->resize(298, 315);
            $img->insert($profile, 'center', -337,-15);
        }
        
        $fontPath = public_path('fonts/arial.ttf');

        $img->text($data['full_name'], 650, 340, function ($font) use ($fontPath) {
            $font->file($fontPath);
            $font->size(70);
            $font->color('#fffff');
            $font->align('center');
            $font->valign('top');
        });

        if (! empty($data['subtitle'])) {
            $img->text(trim($data['subtitle']), 660, 415, function ($font) use ($fontPath) {
                $font->file($fontPath);
                $font->size(18);
                $font->color('#fffff');
                $font->align('center');
                $font->valign('top');
            });
        }

        if (! empty($data['id_number'])) {
            $idNumber = trim($data['id_number']);
            $idFontSize = 40;
            foreach ([[-2, 0], [2, 0], [0, -2], [0, 2], [-2, -2], [-2, 2], [2, -2], [2, 2]] as [$ox, $oy]) {
                $img->text($idNumber, 170 + $ox, 480 + $oy, function ($font) use ($fontPath, $idFontSize) {
                    $font->file($fontPath);
                    $font->size($idFontSize);
                    $font->color('fffff');
                    $font->align('center');
                    $font->valign('top');
                });
            }
        }

        if (! empty($data['qrcode'])) {
            $qrPng = QrCode::format('png')
                ->size(145)
                ->margin(0)
                ->generate($data['qrcode']);
            $qrImage = Image::make((string) $qrPng);
            $img->insert($qrImage, 'top-left',  825, 152);
            
        
         $signaturePath = PublicAssetPath::resolve($data['signature'] ?? null);

        if ($signaturePath) {
            $signature = Image::make($signaturePath)
                ->resize(100, 110)
                ->greyscale()          // Convert to grayscale
                ->invert()             // Invert colors (if needed)
                ->colorize(100, 100, 100); // Make it white
        
            $img->insert($signature, 'center', -320, 215);
        }
        
        }
        

        return $img;
    }

    /**
     * @param  array{
     *     signature:?string,
     *     emergency_person:?string,
     *     emergency_relationship:?string,
     *     emergency_number:?string,
     *     birth_date:?string
     * }  $data
     */
    protected function composeIdCardBack($img, array $data)
    {

        if (! empty($data['emergency_person'])) {
            $this->drawIdCardText($img, $data['emergency_person'], 260, 110, 60, '#000');
        }
        if (! empty($data['emergency_relationship'])) {
            $this->drawIdCardText($img, $data['emergency_relationship'], 260, 170, 50, '#000');
        }
        if (! empty($data['emergency_number'])) {
            $this->drawIdCardText($img, $data['emergency_number'], 260, 220, 60, '#000');
        }

        if (! empty($data['birth_date'])) {
            $formattedDate = Carbon::parse($data['birth_date'])->format('F d, Y');
            $this->drawIdCardText($img, $formattedDate, 260, 380, 50, '#000');
        }

        return $img;
    }
}
