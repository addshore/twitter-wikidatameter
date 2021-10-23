<?php

namespace Addshore\Twitter\WikidataMeter\Output;

class EchoOut implements Output {
    public function output( string $toOutput ) : void {
        echo "-----------------------------------------------------------------" . PHP_EOL;
        echo $toOutput . PHP_EOL;
        echo "-----------------------------------------------------------------" . PHP_EOL;
    }

}