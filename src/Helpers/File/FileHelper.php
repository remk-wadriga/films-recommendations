<?php


namespace App\Helpers\File;


use App\Exception\ServiceException;

class FileHelper
{
    public static function getExt($file)
    {
        preg_match("/^.+\.(\w+)$/", $file, $matches);
        return is_array($matches) && isset($matches[1]) ? $matches[1] : null;
    }

    public static function getFastHash($file)
    {
        $blockSize = 4096;
        $fileSize = is_file($file) ? filesize($file) : strlen($file);
        if ($fileSize > 2 * $blockSize) {
            $hc = hash_init('md5');
            if (is_file($file)) {
                $fp = fopen($file, 'r');
                hash_update($hc, fread($fp, $blockSize));
                hash_update($hc, pack('V', $fileSize)); // uint32 LE
                fseek($fp, 0 - $blockSize, SEEK_END); // last 4096 bytes
                hash_update($hc, fread($fp, $blockSize));
            } else {
                hash_update($hc, substr($file, 0, $blockSize));
                hash_update($hc, substr($file, $fileSize - $blockSize));
            }
            return hash_final($hc);
        } else {
            return is_file($file) ? md5_file($file) : md5($file);
        }
    }

    public static function cropImage(FileEntity $file, $newWidth, $newHeight, $newImageFile = null, $currentWidth = null, $currentHeight = null)
    {
        if (!file_exists($file->path) && $file->data === null) {
            throw new ServiceException(sprintf('File "%s" does not exists and file data not given', $file->path), ServiceException::CODE_INVALID_CONFIG);
        }
        if (!file_exists($file->path)) {
            file_put_contents($file->path, $file->data);
        }

        $imageFile = $file->path;
        $fileExt = $file->ext;
        if ($newImageFile === null) {
            $fileName = basename($imageFile);
            $extWithSizePart = '_' . $newWidth . 'x' . $newHeight . '.' . $fileExt;
            if (!strpos($fileName, $extWithSizePart)) {
                $newImageFile = str_replace($fileName, str_replace('.' . $fileExt, $extWithSizePart, $fileName), $imageFile);
            } else {
                $newImageFile = $imageFile;
            }
        }
        if ($currentWidth === null || $currentHeight === null) {
            list($currentWidth, $currentHeight) = getimagesize($imageFile);
        }
        if ($currentWidth == $newWidth && $currentHeight == $newHeight) {
            if (!file_exists($newImageFile)) {
                copy($imageFile, $newImageFile);
            }
            $file->path = $newImageFile;
            return;
        }

        // Create default params for crop and resize
        $x1 = 0;
        $y1 = 0;
        $x2 = 0;
        $y2 = 0;
        $w1 = $newWidth;
        $h1 = $newHeight;
        $w2 = $currentWidth;
        $h2 = $currentHeight;
        $jpgExtensions = ['jpg', 'jpeg'];
        $pngExtensions = ['png'];

        // Calculate position and size of old image on new (empty) image
        if ($newWidth / $newHeight > $currentWidth / $currentHeight) {
            $w1 = ($newHeight * $currentWidth) / $currentHeight;
            $x1 = ($newWidth - $w1) / 2;
        } else {
            $h1 = ($newWidth * $currentHeight) / $currentWidth;
            $y1 = ($newHeight - $h1) / 2;
        }

        // Create new cropped and resized image
        try {
            $im1 = imagecreatetruecolor($newWidth, $newHeight);
            if (in_array($fileExt, $jpgExtensions)) {
                $im2 = imagecreatefromjpeg($imageFile);
            } elseif (in_array($fileExt, $pngExtensions)) {
                $im2 = imagecreatefrompng($imageFile);
            } else {
                throw new \Exception(sprintf('Invalid image extension: %s', $fileExt));
            }

            imagealphablending($im2, true);
            imagecopyresized($im1, $im2, $x1, $y1, $x2, $y2, $w1, $h1, $w2, $h2);

            if (in_array($fileExt, $jpgExtensions)) {
                imagejpeg($im1, $newImageFile);
            } elseif (in_array($fileExt, $pngExtensions)) {
                imagepng($im1, $newImageFile);
            }

            imagedestroy($im2);
            imagedestroy($im1);

            $file->path = $newImageFile;
        } catch (\Exception $e) {
            throw new ServiceException(sprintf('Can not crop the image "%s" to size "%s": %s', $imageFile, $newWidth . 'x' . $newHeight, $e->getMessage()), ServiceException::CODE_INVALID_CONFIG);
        }
    }
}