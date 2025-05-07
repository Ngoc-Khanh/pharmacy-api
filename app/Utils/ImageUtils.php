<?php

namespace App\Utils;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Exception;

class ImageUtils
{
  protected $cloudinary;
  public function __construct()
  {
    $this->cloudinary = new Cloudinary();
  }
  public function uploadImage(UploadedFile $file, string $folder = 'default', array $options = [])
  {
    try {
      $defaultOptions = [
        'folder' => $folder,
        'resource_type' => 'image',
        'quality' => 'auto',
        'fetch_format' => 'auto',
        'width' => 500,
        'crop' => 'scale',
      ];
      $uploadOptions = array_merge($defaultOptions, $options);
      $response = $this->cloudinary->uploadApi()->upload($file->getRealPath(), $uploadOptions);
      return [
        'success' => true,
        'public_id' => $response['public_id'],
        'url' => $response['secure_url'],
      ];
    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => $e->getMessage(),
      ];
    }
  }
}
