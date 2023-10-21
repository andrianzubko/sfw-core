<?php

namespace SFW\Lazy\Sys;

/**
 * Image functions.
 */
class Image extends \SFW\Lazy\Sys
{
    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Reading image from string.
     */
    public function fromString(string|false|null $string): \GdImage|false
    {
        if (empty($string)) {
            return false;
        }

        $image = imagecreatefromstring($string);

        if ($image === false) {
            return false;
        }

        return $image;
    }

    /**
     * Reading image from file.
     */
    public function fromFile(string $file): \GdImage|false
    {
        return $this->fromString(self::sys('File')->get($file));
    }

    /**
     * Saving as PNG to file or returning as string.
     */
    public function savePng(\GdImage $image, ?string $file = null, int $quality = 0): string|bool
    {
        imagesavealpha($image, true);

        if ($file !== null) {
            $success = imagepng($image, $file, $quality);

            if ($success) {
                @chmod($file, self::$config['sys']['file_mode']);
            }

            return $success;
        }

        ob_start(fn() => null);

        imagepng($image, null, $quality);

        return ob_get_clean();
    }

    /**
     * Saving as JPEG to file or returning as string.
     */
    public function saveJpeg(\GdImage $image, ?string $file = null, int $quality = 75): string|bool
    {
        $w = imagesx($image);

        $h = imagesy($image);

        $fixed = imagecreatetruecolor($w, $h);

        imagefill($fixed, 0, 0, imagecolorallocate($fixed, 255, 255, 255));

        imagecopy($fixed, $image, 0, 0, 0, 0, $w, $h);

        imageinterlace($fixed, true);

        if ($file !== null) {
            $success = imagejpeg($fixed, $file, $quality);

            if ($success) {
                @chmod($file, self::$config['sys']['file_mode']);
            }

            return $success;
        }

        ob_start(fn() => null);

        imagejpeg($fixed, null, $quality);

        return ob_get_clean();
    }

    /**
     * Resizing image.
     */
    public function resize(\GdImage $image, int $nW, int $nH, bool $crop = false, bool $fit = false): \GdImage {
        $oW = imagesx($image);

        $oH = imagesy($image);

        if ($oW == $nW && $oH == $nH || !$fit && $oW <= $nW && $oH <= $nH) {
            return $image;
        }

        if ($crop) {
            $resized = $this->resizeWithCrop($image, $nW, $nH, $oW, $oH);
        } else {
            $resized = $this->resizeNoCrop($image, $nW, $nH, $oW, $oH);
        }

        return $resized;
    }

    /**
     * Resizing image with cropping.
     */
    protected function resizeWithCrop(\GdImage $image, int $nW, int $nH, int $oW, int $oH): \GdImage
    {
        $ratio = $oW / $oH;

        $cW = $nW;

        $cH = $nH;

        if ($cW / $cH > $ratio) {
            $cH = (int) round($cW / $ratio);
        } else {
            $cW = (int) round($cH * $ratio);
        }

        $cX = (int) round(($nW - $cW) / 2);

        $cY = (int) round(($nH - $cH) / 2);

        $resized = imagecreatetruecolor($nW, $nH);

        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));

        imagecopyresampled($resized, $image, $cX, $cY, 0, 0, $cW, $cH, $oW, $oH);

        return $resized;
    }

    /**
     * Resizing image with no cropping.
     */
    protected function resizeNoCrop(\GdImage $image, int $nW, int $nH, int $oW, int $oH): \GdImage
    {
        $ratio = $oW / $oH;

        if ($nW / $nH > $ratio) {
            $nW = (int) round($nH * $ratio);
        } else {
            $nH = (int) round($nW / $ratio);
        }

        $resized = imagecreatetruecolor($nW, $nH);

        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $nW, $nH, $oW, $oH);

        return $resized;
    }
}
