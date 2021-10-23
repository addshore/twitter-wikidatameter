<?php

namespace Addshore\Twitter\WikidataMeter\EnvLoader;

class EnvFromDirectory implements EnvLoader {
    
        private string $directory;
    
        /**
        * @param string $directory that holds a .env fole
        */
        public function __construct( string $directory ) {
            $this->directory = $directory;
        }

        public function load(): void {
            if( !file_exists( $this->directory ) ) {
                throw new \RuntimeException( "File $this->directory does not exist" );
            }
            $dotenv = \Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
            $dotenv->safeLoad();
        }

}