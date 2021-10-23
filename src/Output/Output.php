<?php

namespace Addshore\Twitter\WikidataMeter\Output;

interface Output {
    public function output( string $toOutput ) : void;
}