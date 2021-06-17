<?php

use Addwiki\Mediawiki\Api\Client\Action\Request\ActionRequest;
use Addwiki\Mediawiki\Api\Client\MediaWiki;
use Atymic\Twitter\ApiV1\Service\Twitter;
use Atymic\Twitter\Configuration as TwitterConf;
use Atymic\Twitter\Http\Factory\ClientCreator as TwitterClientCreator;
use Atymic\Twitter\Service\Querier as TwitterQuerier;
use GuzzleHttp\Client as Guzzle;
use NumberToWords\NumberToWords;

require_once __DIR__ . '/vendor/autoload.php';

// Load Environment from a file (only if it exists)
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->safeLoad();

// Service Objects
$tw = new Twitter(
    new TwitterQuerier(
        TwitterConf::createFromConfig(
            require_once __DIR__ . '/vendor/atymic/twitter/config/twitter.php'
        ),
        new TwitterClientCreator()
    )
);
$store = new Guzzle(['base_uri' => 'https://api.jsonstorage.net/v1/json/' . getenv('JSONSTORAGE_OBJECT') . '?apiKey=' . getenv('JSONSTORAGE_KEY')]);
$graphite = new Guzzle(['base_uri' => 'https://graphite.wikimedia.org/render']);
$wd = MediaWiki::newFromEndpoint( 'https://www.wikidata.org/w/api.php' )->action();
$numberToWords = (new NumberToWords())->getNumberTransformer('en');

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

// And state from graphite
$namespaceStatistics = $graphite->request( 'GET', '?format=json&from=-2d&until=now&target=daily.wikidata.site_stats.pages_by_namespace.{0,120,146}.nonredirects' );
$namespaceStatistics = json_decode( $namespaceStatistics->getBody(), true );
$wdNsPages = [];
foreach( $namespaceStatistics as $namespaceData ) {
    $target = $namespaceData['target'];
    $nsId = (int)str_replace('daily.wikidata.site_stats.pages_by_namespace.','',str_replace('.nonredirects','',$target));
    foreach( $namespaceData['datapoints'] as $datapoint ) {
        // The last value will be the latest one
        $wdNsPages[$nsId] = (int)$datapoint[0];
    }
}

// Initiate any un initiated state
$data['wdEdits'] = array_key_exists('wdEdits',$data) ? $data['wdEdits'] : 0;
$data['wdNsPages0'] = array_key_exists('wdNsPages0',$data) ? $data['wdNsPages0'] : 0;
$data['wdNsPages120'] = array_key_exists('wdNsPages120',$data) ? $data['wdNsPages120'] : 0;
$data['wdNsPages146'] = array_key_exists('wdNsPages146',$data) ? $data['wdNsPages146'] : 0;

// Figure out if we need to make a new tweet
$toPost = [];
// wdEdits
if ( intdiv($wdEdits, 1000000) > intdiv($data['wdEdits'], 1000000) ) {
    $roundNumber = floor($wdEdits/1000000)*1000000;
    $formatted = number_format($roundNumber);
    $words = $numberToWords->toWords($roundNumber);
    $toPost[] = <<<TWEET
    Wikidata now has over ${formatted} edits!
    That's over ${words}...
    You can find the milestone edit here https://www.wikidata.org/w/index.php?diff=${roundNumber}
    TWEET;
    $data['wdEdits'] = $wdEdits;
}
// wdNsPages0 (items)
if ( intdiv($wdNsPages[0], 1000000) > intdiv($data['wdNsPages0'], 1000000) ) {
    $roundNumber = floor($wdNsPages[0]/1000000)*1000000;
    $formatted = number_format($roundNumber);
    $words = $numberToWords->toWords($roundNumber);
    $toPost[] = <<<TWEET
    Wikidata now has over ${formatted} Items!
    That's over ${words}...
    You can find the latest creations here https://www.wikidata.org/wiki/Special:NewPages?namespace=0
    TWEET;
    $data['wdNsPages0'] = $wdNsPages[0];
}
// wdNsPages120 (properties)
if ( intdiv($wdNsPages[120], 100) > intdiv($data['wdNsPages120'], 100) ) {
    $roundNumber = floor($wdNsPages[120]/100)*100;
    $formatted = number_format($roundNumber);
    $words = $numberToWords->toWords($roundNumber);
    $toPost[] = <<<TWEET
    Wikidata now has over ${formatted} Properties!
    That's over ${words}...
    You can find the latest creations here https://www.wikidata.org/wiki/Special:NewPages?namespace=120
    TWEET;
    $data['wdNsPages120'] = $wdNsPages[120];
}
// wdNsPages146 (lexemes)
if ( intdiv($wdNsPages[146], 10000) > intdiv($data['wdNsPages146'], 10000) ) {
    $roundNumber = floor($wdNsPages[146]/10000)*10000;
    $formatted = number_format($roundNumber);
    $words = $numberToWords->toWords($roundNumber);
    $toPost[] = <<<TWEET
    Wikidata now has over ${formatted} Lexemes!
    That's over ${words}...
    You can find the latest creations here https://www.wikidata.org/wiki/Special:NewPages?namespace=146
    TWEET;
    $data['wdNsPages146'] = $wdNsPages[146];
}

// Possibly make a new tweet
foreach( $toPost as $tweetText ) {
    echo "Tweeting: ${tweetText}" . PHP_EOL;
    $a = $tw->postTweet([
        'status' => $tweetText
    ]);
    sleep(2);
}

// Persist any changed state
if( $dataHash !== md5(serialize($data)) ){
    echo "Persisting changed state." . PHP_EOL;
    var_dump($data);
    $store->request('PUT', '', [ 'body' => json_encode( $data ), 'headers' => [ 'content-type' => 'application/json; charset=utf-8' ] ]);
}

// All done!
echo PHP_EOL . "All done!" . PHP_EOL;
