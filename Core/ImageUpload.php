<?php

namespace Core;

use Cloudinary\Cloudinary;
use App\Config;

abstract class ImageUpload
{

    /**
     * Create a Cloudinary instance
     */
    public static function upload()
    {
        $cloudinary = "cloudinary://" . Config::CLOUDINARY_API_KEY . ":" . Config::CLOUDINARY_API_SECRET ."@" . Config::CLOUDNAME;
        $cloudinary = new Cloudinary($cloudinary);
        return $cloudinary;
    }
}
