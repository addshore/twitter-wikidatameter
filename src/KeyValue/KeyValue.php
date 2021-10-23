<?php

namespace Addshore\Twitter\WikidataMeter\KeyValue;

interface KeyValue {
    public function syncFromSourceOfTruth();
    public function syncToSourceOfTruth();
    public function changed() : bool;
    public function initKeys( array $keys, int $value );
    public function hasValue( string $key ) : bool;
    public function getValue( string $key ) : int;
    public function setValue( string $key, int $value );
    public function dump() : array;
}
