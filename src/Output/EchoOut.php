<?php

namespace Addshore\Twitter\WikidataMeter\Output;

class EchoOut implements Output {
    public function output( string $toOutput ) : void {
        echo $toOutput . PHP_EOL;
    }

}