<?php

namespace Addshore\Twitter\WikidataMeter\KeyValue;

class JsonStorageFile implements KeyValue {

    use InMemDataTrait;

    private string $file;

    public function __construct( string $file ) {
        $this->file = $file;
    }

	function syncFromSourceOfTruth() : void{
        if ( file_exists( $this->file ) ) {
            $this->data = json_decode( file_get_contents( $this->file ), true );
        } else {
            $this->data = [];
        }
	}

	function syncToSourceOfTruth() : void{
        file_put_contents( $this->file, json_encode( $this->data ) );
    }

}
