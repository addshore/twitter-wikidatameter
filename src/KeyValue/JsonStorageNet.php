<?php

namespace Addshore\Twitter\WikidataMeter\KeyValue;

use GuzzleHttp\Client as Guzzle;

class JsonStorageNet implements KeyValue {

    private $data;
    private bool $changed = false;
    
    private function client() {
        return new Guzzle(['base_uri' => 'https://api.jsonstorage.net/v1/json/' . getenv('JSONSTORAGE_OBJECT') . '?apiKey=' . getenv('JSONSTORAGE_KEY')]);
    }

    function changed() : bool{
        return $this->changed;
    }

    public function initKeys( array $keys, int $value ) {
        foreach ($keys as $storeKey) {
            if(!$this->hasValue($storeKey)) {
                $this->setValue($storeKey, $value);
            }
        }
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
	

	function hasValue(string $key): bool {
        return array_key_exists($key, $this->data);
	}
	

	function getValue(string $key): int {
        return $this->data[$key];
	}
	

	function setValue(string $key, int $value) : void{
        $this->data[$key] = $value;
        $this->changed = true;
	}

    function dump() : array{
        return $this->data;
    }
}