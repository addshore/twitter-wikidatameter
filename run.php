<?php

use Addwiki\Mediawiki\Api\Client\Action\Request\ActionRequest;
use Addwiki\Mediawiki\Api\Client\MediaWiki;
use Atymic\Twitter\Twitter;
use Atymic\Twitter\Configuration as TwitterConf;
use GuzzleHttp\Client as Guzzle;

require_once __DIR__ . '/vendor/autoload.php';

// Load Environment from a file (only if it exists)
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->safeLoad();

// Service Objects
$tw = new Twitter(
    TwitterConf::fromLaravelConfiguration(
        require_once __DIR__ . '/vendor/atymic/twitter/config/twitter.php'
        )
);
$store = new Guzzle(['base_uri' => 'https://api.jsonstorage.net/v1/json/' . getenv('JSONSTORAGE_OBJECT') . '?apiKey=' . getenv('JSONSTORAGE_KEY')]);
$wd = MediaWiki::newFromEndpoint( 'https://www.wikidata.org/w/api.php' )->action();

// Load our existing state
$storeGet = $store->request('GET');
$data = json_decode( $storeGet->getBody(), true );
$dataHash = md5(serialize($data));
// $recentPosts = $tw->getUserTimeline([
//     'screen_name' => getenv('TWITTER_USER'),
//     'count' => 200,
// ]);
// $posted = array_map( (function($a) { return $a->text; }), $posted );

// And state of wikidata
$wdStatistics = $wd->request( ActionRequest::simpleGet( 'query', [ 'meta' => 'siteinfo', 'siprop' => 'statistics' ] ) )['query']['statistics'];
$wdEdits = $wdStatistics['edits'];

// Initiate any un initiated state
$data['wdEdits'] = array_key_exists('wdEdits',$data) ? $data['wdEdits'] : $wdEdits;

// Figure out if we need to make a new tweet
$toPost = [];
// wdEdits
if ( intdiv($data['wdEdits'], 1000000) > intdiv($wdEdits, 1000000) ) {
    $roundNumber = floor($wdEdits/1000000)*1000000;
    $formatted = number_format($roundNumber);
    $toPost[] = "Wikidata just reached the next 1 million edit mark.\nNow with over ${$formatted} edits.\nhttps://www.wikidata.org/w/index.php?diff=${$roundNumber}";
    $data['wdEdits'] = $wdEdits;
}

// Possibly make a new tweet
foreach( $toPost as $tweetText ) {
    // if( in_array( $tweetText, $posted ) ) {
    //     echo "Skipping as recently tweeted: " . $tweetText . PHP_EOL;
    //     continue;
    // }

    echo "Tweeting: ${$tweetText}" . PHP_EOL;
    $a = $tw->postTweet([
        'status' => $tweetText
    ]);
    sleep(1);
    // break;
}

// Persist any changed state
if( $dataHash !== md5(serialize($data)) ){
    echo "Persisting changed state." . PHP_EOL;
    $store->request('PUT', '', [ 'body' => json_encode( $data ), 'headers' => [ 'content-type' => 'application/json; charset=utf-8' ] ]);
}

// All done!
echo "All done!" . PHP_EOL;
