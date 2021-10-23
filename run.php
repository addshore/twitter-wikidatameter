<?php

use Addwiki\Mediawiki\Api\Client\MediaWiki;
use GuzzleHttp\Client as Guzzle;
use NumberToWords\NumberToWords;

require_once __DIR__ . '/vendor/autoload.php';

(new Addshore\Twitter\WikidataMeter\EnvLoader\EnvFromDirectory(__DIR__))->load();

$numberToWords = (new NumberToWords())->getNumberTransformer('en');
$wikidata = MediaWiki::newFromEndpoint( 'https://www.wikidata.org/w/api.php' );
$graphite = new Guzzle(['base_uri' => 'https://graphite.wikimedia.org/render']);

$out = new \Addshore\Twitter\WikidataMeter\Output\MultiOut(
    new \Addshore\Twitter\WikidataMeter\Output\EchoOut(),
);

// Some switches for some local testing..?
if(getenv('I_AM_BRING_TESTED') === false) {
    // Production only path
    $store = new \Addshore\Twitter\WikidataMeter\KeyValue\JsonStorageNet();
    $out->add( new \Addshore\Twitter\WikidataMeter\Output\TwitterOut(__DIR__ . '/vendor/atymic/twitter/config/twitter.php') );
} else {
    // Test only path
    $store = new \Addshore\Twitter\WikidataMeter\KeyValue\JsonStorageFile( __DIR__ . '/.tmp.data' );
}

const CONF_DATA_POINT = "datapoint";
const CONF_STEP = "step";
const CONF_OUTPUT = "output";

const STORE_WIKIDATA_EDITS = 'wdEdits';
const STORE_WIKIDATA_PAGES_NS_0 = 'wdNsPages0';
const STORE_WIKIDATA_PAGES_NS_120 = 'wdNsPages120';
const STORE_WIKIDATA_PAGES_NS_146 = 'wdNsPages146';
const STORE_WIKIDATA_LEXEME_FORMS = 'wdLexemeForms';
const STORE_WIKIDATA_LEXEME_SENSES = 'wdLexemeSenses';

$config = [
    STORE_WIKIDATA_EDITS => [ // Wikidata Edits
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\MediaWikiEdits( $wikidata ),
        CONF_STEP => 10000000, // 10 million
        CONF_OUTPUT => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikidata now has over ${formatted} edits!
            That's over ${words}...
            You can find the milestone edit here https://www.wikidata.org/w/index.php?diff=${round}
            OUT;
        },
    ],
    STORE_WIKIDATA_PAGES_NS_0 => [ // Wikidata Items
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'daily.wikidata.site_stats.pages_by_namespace.0.nonredirects' ),
        CONF_STEP => 1000000, // 1 million
        CONF_OUTPUT => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikidata now has over ${formatted} Items!
            That's over ${words}...
            You can find the latest creations here https://www.wikidata.org/wiki/Special:NewPages?namespace=0
            OUT;
        },
    ],
    STORE_WIKIDATA_PAGES_NS_120 => [ // Wikidata Properties
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'daily.wikidata.site_stats.pages_by_namespace.120.nonredirects' ),
        CONF_STEP => 100, // 100
        CONF_OUTPUT => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikidata now has over ${formatted} Properties!
            That's over ${words}...
            You can find the latest creations here https://www.wikidata.org/wiki/Special:NewPages?namespace=120
            OUT;
        },
    ],
    STORE_WIKIDATA_PAGES_NS_146 => [ // Wikidata Lexemes
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'daily.wikidata.site_stats.pages_by_namespace.146.nonredirects' ),
        CONF_STEP => 10000, // 10k
        CONF_OUTPUT => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikidata now has over ${formatted} Lexemes!
            That's over ${words}...
            You can find the latest creations here https://www.wikidata.org/wiki/Special:NewPages?namespace=146
            OUT;
        },
    ],
    STORE_WIKIDATA_LEXEME_FORMS => [ // Wikidata Lexeme Forms
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'sumSeries(daily.wikidata.datamodel.lexeme.languageItem.*.forms)' ),
        CONF_STEP => 10000, // 10k
        CONF_OUTPUT => function ( int $value, int $step, int $round, string $formatted, string $words ) {
            return <<<OUT
            Wikidata now has over ${formatted} Forms on Lexemes!
            That's over ${words}...
            OUT;
        },
    ],
    STORE_WIKIDATA_LEXEME_SENSES => [ // Wikidata Lexeme Senses
        CONF_DATA_POINT => new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'sumSeries(daily.wikidata.datamodel.lexeme.languageItem.*.senses)' ),
        CONF_STEP => 10000, // 10k
        CONF_OUTPUT => function ( int $value, int $step, int $round, string $formatted, string $words ) {
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
$toOutput = [];
foreach( $config as $key => $details ) {
    $value = $details[CONF_DATA_POINT]->get();
    $step = $details[CONF_STEP];
    if ( intdiv($value, $step) > intdiv($store->getValue($key), $step) ) {
        $roundNumber = floor($value/$step)*$step;
        $formatted = number_format($roundNumber);
        $words = $numberToWords->toWords($roundNumber);
        $toOutput[] = $details[CONF_OUTPUT]( $value, $step, $roundNumber, $formatted, $words );
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
if( count( $toOutput ) > 0 ) {
    echo "Stage 4: Sending new output." . PHP_EOL;
    foreach( $toOutput as $one ) {
        $out->output( $one );
    }
}


// All done!
echo "All done!" . PHP_EOL;
