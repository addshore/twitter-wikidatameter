<?php

namespace Addshore\Twitter\WikidataMeter\EnvLoader;

interface EnvLoader {

    /**
     * Load the environment variables into the current process
     */
    public function load() : void;

}