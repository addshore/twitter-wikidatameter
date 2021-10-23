<?php

namespace Addshore\Twitter\WikidataMeter\DataPoint;

interface DataPoint {
    public function get() : int;
}