<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{

  public function newNameImage(){
    
  }

  public function upload(UploadedFile $file)
  {
    $file->move("upload/tricks", $nameImage);
  }
}
