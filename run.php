<?php

use Addwiki\Mediawiki\Api\Client\MediaWiki;
use GuzzleHttp\Client as Guzzle;
use NumberToWords\NumberToWords;
use Addshore\Twitter\WikidataMeter\Output\MultiOut;

require_once __DIR__ . '/vendor/autoload.php';

(new Addshore\Twitter\WikidataMeter\EnvLoader\EnvFromDirectory(__DIR__))->load();

$numberToWords = (new NumberToWords())->getNumberTransformer('en');
$enwiki = MediaWiki::newFromEndpoint( 'https://en.wikipedia.org/w/api.php' );
$wikidata = MediaWiki::newFromEndpoint( 'https://www.wikidata.org/w/api.php' );
$graphite = new Guzzle(['base_uri' => 'https://graphite.wikimedia.org/render']);
$wmRest = new Guzzle(['base_uri' => 'https://wikimedia.org/api/rest_v1']);

$outTwitterWikidataMeter = new \Addshore\Twitter\WikidataMeter\Output\TwitterOut(__DIR__ . '/config/twitter/wikidatameter.php');
$outTwitterWikipediaMeter = new \Addshore\Twitter\WikidataMeter\Output\TwitterOut(__DIR__ . '/config/twitter/wikipediameter.php');
$outTwitterWikimediaMeter = new \Addshore\Twitter\WikidataMeter\Output\TwitterOut(__DIR__ . '/config/twitter/wikimediameter.php');
$outEcho = new \Addshore\Twitter\WikidataMeter\Output\EchoOut();

// Switch to local storage if we are locally testing
if(getenv('I_AM_BRING_TESTED') === false) {
    $store = new \Addshore\Twitter\WikidataMeter\KeyValue\JsonStorageNet();
} else {
    $store = new \Addshore\Twitter\WikidataMeter\KeyValue\JsonStorageFile( __DIR__ . '/.tmp.data' );
}

const CONF_DATA_POINT = "datapoint";
const CONF_STEP = "step";
const CONF_MESSAGE = "message";
const CONF_OUTPUTS = "outputs";

const STORE_WIKIMEDIA_EDITS = "wikimedia_edits";
const STORE_WIKIPEDIA_EDITS = "wikipedia_edits";
const STORE_ENWIKI_EDITS = "enwiki_edits";
const STORE_WIKIDATA_EDITS = 'wdEdits';
const STORE_WIKIDATA_PAGES_NS_0 = 'wdNsPages0';
const STORE_WIKIDATA_PAGES_NS_120 = 'wdNsPages120';
const STORE_WIKIDATA_PAGES_NS_146 = 'wdNsPages146';
const STORE_WIKIDATA_LEXEME_FORMS = 'wdLexemeForms';
const STORE_WIKIDATA_LEXEME_SENSES = 'wdLexemeSenses';

const ONE_HUNDRED = 100;
const TEN_THOUSAND = 10000;
const ONE_MILLION = 1000000;
const TEN_MILLION = 10000000;

$config = [
    STORE_WIKIMEDIA_EDITS => [ // All Wikimedia project edits
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\AggregateEditsRest( $wmRest, 'all-projects' ),
        CONF_STEP => TEN_MILLION,
        CONF_OUTPUTS => new MultiOut( $outTwitterWikimediaMeter ),
        CONF_MESSAGE => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikimedia, across all projects, now has over ${formatted} edits!
            That's over ${words}...
            See the history https://stats.wikimedia.org/#/all-projects/contributing/edits/normal|bar|all|~total|monthly
            OUT;
        },
    ],
    STORE_WIKIPEDIA_EDITS => [ // All language Wikipedia edits
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\AggregateEditsRest( $wmRest, 'all-wikipedia-projects' ),
        CONF_STEP => TEN_MILLION,
        CONF_OUTPUTS => new MultiOut( $outTwitterWikipediaMeter ),
        CONF_MESSAGE => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikipedia, across all languages, now has over ${formatted} edits!
            That's over ${words}...
            See the history https://stats.wikimedia.org/#/all-wikipedia-projects/contributing/edits/normal|bar|all|~total|monthly
            OUT;
        },
    ],
    STORE_ENWIKI_EDITS => [ // en.wikipedia Edits
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\MediaWikiEdits( $enwiki ),
        CONF_STEP => TEN_MILLION,
        CONF_OUTPUTS => new MultiOut( $outTwitterWikipediaMeter ),
        CONF_MESSAGE => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            English Wikipedia now has over ${formatted} edits!
            That's over ${words}...
            You can find the milestone edit here https://en.wikipedia.org/w/index.php?oldid=${round}
            OUT;
        },
    ],
    STORE_WIKIDATA_EDITS => [ // Wikidata Edits
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\MediaWikiEdits( $wikidata ),
        CONF_STEP => TEN_MILLION,
        CONF_OUTPUTS => new MultiOut( $outTwitterWikidataMeter ),
        CONF_MESSAGE => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikidata now has over ${formatted} edits!
            That's over ${words}...
            You can find the milestone edit here https://www.wikidata.org/w/index.php?diff=${round}
            OUT;
        },
    ],
    STORE_WIKIDATA_PAGES_NS_0 => [ // Wikidata Items
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'daily.wikidata.site_stats.pages_by_namespace.0.nonredirects' ),
        CONF_STEP => ONE_MILLION,
        CONF_OUTPUTS => new MultiOut( $outTwitterWikidataMeter ),
        CONF_MESSAGE => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikidata now has over ${formatted} Items!
            That's over ${words}...
            You can find the latest creations here https://www.wikidata.org/wiki/Special:NewPages?namespace=0
            OUT;
        },
    ],
    STORE_WIKIDATA_PAGES_NS_120 => [ // Wikidata Properties
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'daily.wikidata.site_stats.pages_by_namespace.120.nonredirects' ),
        CONF_STEP => ONE_HUNDRED,
        CONF_OUTPUTS => new MultiOut( $outTwitterWikidataMeter ),
        CONF_MESSAGE => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikidata now has over ${formatted} Properties!
            That's over ${words}...
            You can find the latest creations here https://www.wikidata.org/wiki/Special:NewPages?namespace=120
            OUT;
        },
    ],
    STORE_WIKIDATA_PAGES_NS_146 => [ // Wikidata Lexemes
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'daily.wikidata.site_stats.pages_by_namespace.146.nonredirects' ),
        CONF_STEP => TEN_THOUSAND,
        CONF_OUTPUTS => new MultiOut( $outTwitterWikidataMeter ),
        CONF_MESSAGE => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikidata now has over ${formatted} Lexemes!
            That's over ${words}...
            You can find the latest creations here https://www.wikidata.org/wiki/Special:NewPages?namespace=146
            OUT;
        },
    ],
    STORE_WIKIDATA_LEXEME_FORMS => [ // Wikidata Lexeme Forms
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'sumSeries(daily.wikidata.datamodel.lexeme.languageItem.*.forms)' ),
        CONF_STEP => TEN_THOUSAND,
        CONF_OUTPUTS => new MultiOut( $outTwitterWikidataMeter ),
        CONF_MESSAGE => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikidata now has over ${formatted} Forms on Lexemes!
            That's over ${words}...
            OUT;
        },
    ],
    STORE_WIKIDATA_LEXEME_SENSES => [ // Wikidata Lexeme Senses
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'sumSeries(daily.wikidata.datamodel.lexeme.languageItem.*.senses)' ),
        CONF_STEP => TEN_THOUSAND,
        CONF_OUTPUTS => new MultiOut( $outTwitterWikidataMeter ),
        CONF_MESSAGE => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikidata now has over ${formatted} Senses on Lexemes!
            That's over ${words}...
            OUT;
        },
    ],
];

/**
 * Logic starts below here! =]
 */

// Load current source of truth
echo "Stage 1: Loading current state." . PHP_EOL;
$store->syncFromSourceOfTruth();
$store->initKeys(array_keys($config), 0);

// Use the config to check for new data, and collect new output
echo "Stage 2: Collecting new output." . PHP_EOL;
$outputCollection = [];
foreach( $config as $key => $details ) {
    $value = $details[CONF_DATA_POINT]->get();
    $step = $details[CONF_STEP];
    if ( intdiv($value, $step) > intdiv($store->getValue($key), $step) ) {
        $roundNumber = floor($value/$step)*$step;
        $formatted = number_format($roundNumber);
        $words = $numberToWords->toWords($roundNumber);
        $outputCollection[$key] = $details[CONF_MESSAGE]( $value, $step, $roundNumber, $formatted, $words );
        $store->setValue($key, $value);
    }
}

// Store new knowledge
if( $store->changed() ) {
    echo "Stage 3: Persisting changed state." . PHP_EOL;
    var_dump($store->dump());
    $store->syncToSourceOfTruth();
}

// Output what we need
if( count( $outputCollection ) > 0 ) {
    echo "Stage 4: Sending new output." . PHP_EOL;
    foreach( $outputCollection as $key => $toOutput ) {
        $outEcho->output( $toOutput );
        if(getenv('I_AM_BRING_TESTED') === false) {
            $config[$key][CONF_OUTPUTS]->output( $toOutput );
        }
    }
}

// All done!
echo "All done!" . PHP_EOL;
