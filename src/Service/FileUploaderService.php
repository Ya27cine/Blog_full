<?php 
    namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Constraints\File;

class FileUploaderService{

        protected $targetDirectory;

        public function __construct($targetDirectory)
        {
            $this->targetDirectory = $targetDirectory;
        }

        public function upload($imageFile){

            $newFilename = uniqid().'.'.$imageFile->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $imageFile->move(
                    $this->targetDirectory,
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            return $newFilename;
        }

        public function getFileImage($imageName){
            try {
                $fileImage =  new File($this->targetDirectory.'/'.$imageName);
    
                 return $fileImage;

            } catch (\Throwable $th) {
               // $post->setImage(  );
            }
        }

    }



?>