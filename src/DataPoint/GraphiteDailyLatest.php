<?php

namespace Addshore\Twitter\WikidataMeter\DataPoint;

class GraphiteDailyLatest implements DataPoint {

    private $client;
    private $metricName;
    
    public function __construct( \GuzzleHttp\Client $client, string $metricName ) {
        $this->client = $client;
        $this->metricName = $metricName;
    }

    public function get() : int {
        $senseCount = $this->client->request( 'GET', '?format=json&from=-2d&until=now&target=' . $this->metricName );
        $senseCount = json_decode( $senseCount->getBody(), true );
        $value = 0;
        foreach( $senseCount as $senseData ) {
            foreach( $senseData['datapoints'] as $datapoint ) {
                // The last value will be the latest one
                $value = (int)$datapoint[0];
            }
        }
        return $value;
    }
}