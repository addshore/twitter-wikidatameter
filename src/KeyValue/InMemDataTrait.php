<?php

namespace Addshore\Twitter\WikidataMeter\KeyValue;

trait InMemDataTrait{

    private $data;
    private bool $changed = false;

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