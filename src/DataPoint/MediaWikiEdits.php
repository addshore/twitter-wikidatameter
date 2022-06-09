<?php

namespace Addshore\Twitter\WikidataMeter\DataPoint;

use Addwiki\Mediawiki\Api\Client\Action\Request\ActionRequest;
use Addwiki\Mediawiki\Api\Client\MediaWiki;

class MediaWikiEdits implements DataPoint {

    private $mw;
    
    public function __construct( MediaWiki $mw ) {
        $this->mw = $mw;
    }

    public function get() : int {
        $wdStatistics = $this->mw->action()->request( ActionRequest::simpleGet( 'query', [ 'meta' => 'siteinfo', 'siprop' => 'statistics' ] ) )['query']['statistics'];
        return $wdStatistics['edits'];
    }
}