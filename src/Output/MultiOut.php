<?php

namespace Addshore\Twitter\WikidataMeter\Output;

class MultiOut implements Output {

    private array $outputs;

    public function __construct( Output ...$outputs ) {
        $this->outputs = $outputs;
    }

    public function add( Output $output ) : void {
        $this->outputs[] = $output;
    }

    public function output( string $toOutput ) : void {
        foreach( $this->outputs as $output ) {
            $output->output( $toOutput );
        }
    }

}