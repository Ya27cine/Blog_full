<?php 
    namespace App\Service;

    class FileUploaderService{

        protected $targetDirectory;

        public function __construct($targetDirectory)
        {
            $this->targetDirectory = $targetDirectory;
        }
    }



?>