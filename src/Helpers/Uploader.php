<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{

  public function newNameImage(UploadedFile $file)
  {
    $newName = md5(uniqid()) . '.' . $file->guessExtension();

    return $newName;
  }

  public function upload(UploadedFile $file, string $path, string $fileName)
  {
    $file->move($path, $fileName);
  }

  public function removeImage(string $path)
  {
    if (\file_exists($path)) {
      \unlink($path);
    }
  }
}
