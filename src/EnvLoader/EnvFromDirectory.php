<?php

namespace Addshore\Twitter\WikidataMeter\EnvLoader;

class EnvFromDirectory implements EnvLoader {
    
        /**
        * @var string
        */
        private $directory;
    
        /**
        * @param string $directory that holds a .env fole
        */
        public function __construct( string $directory ) {
            $this->directory = $directory;
        }

        public function load(): void {
            if( !file_exists( $this->file ) ) {
                throw new \RuntimeException( "File $this->file does not exist" );
            }
            $dotenv = \Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
            $dotenv->safeLoad();
        }

}