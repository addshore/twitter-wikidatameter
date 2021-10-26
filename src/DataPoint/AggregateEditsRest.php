<?php

namespace Addshore\Twitter\WikidataMeter\DataPoint;

use GuzzleHttp\Client;

class AggregateEditsRest implements DataPoint {

    private Client $client;
    private string $project;
    
    /**
     * @param Client Should be a client pointing to https://wikimedia.org/api/rest_v1
     * @param string $project per the API spec, could be `all-wikipedia-projects`
     */
    public function __construct( Client $client, string $project ) {
        $this->client = $client;
        $this->project = $project;
    }

    public function get() : int {
        // 2001010100 is the start of time in this context?
        $currentDate = date( 'Ymd' ) . '00';
        $data = $this->client->request( 'GET', '/api/rest_v1/metrics/edits/aggregate/' . $this->project . '/all-editor-types/all-page-types/monthly/2001010100/' . $currentDate );
        $data = json_decode( $data->getBody(), true );
        $edits = 0;
        foreach( $data['items'] as $item ) {
            foreach( $item['results'] as $result ) {
                $edits = $edits + $result['edits'];
            }
        }
        return $edits;
    }
}