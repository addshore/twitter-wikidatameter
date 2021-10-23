<?php

namespace Addshore\Twitter\WikidataMeter\KeyValue;

use GuzzleHttp\Client as Guzzle;

class JsonStorageNet implements KeyValue {

    use InMemDataTrait;

    private function client() {
        return new Guzzle(['base_uri' => 'https://api.jsonstorage.net/v1/json/' . getenv('JSONSTORAGE_OBJECT') . '?apiKey=' . getenv('JSONSTORAGE_KEY')]);
    }

	function syncFromSourceOfTruth() : void{
        $storeGet = $this->client()->request('GET');
        $data = json_decode( $storeGet->getBody(), true );
        $this->data = $data;
        $this->changed = false;
	}

	function syncToSourceOfTruth() : void{
        $this->client()->request('PUT', '', [ 'body' => json_encode( $this->data ), 'headers' => [ 'content-type' => 'application/json; charset=utf-8' ] ]);
	}

}