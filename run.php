<?php

use Addwiki\Mediawiki\Api\Client\MediaWiki;
use GuzzleHttp\Client as Guzzle;
use NumberToWords\NumberToWords;

require_once __DIR__ . '/vendor/autoload.php';

(new Addshore\Twitter\WikidataMeter\EnvLoader\EnvFromDirectory(__DIR__))->load();

const STORE_WIKIDATA_EDITS = 'wdEdits';
const STORE_WIKIDATA_PAGES_NS_0 = 'wdNsPages0';
const STORE_WIKIDATA_PAGES_NS_120 = 'wdNsPages120';
const STORE_WIKIDATA_PAGES_NS_146 = 'wdNsPages146';
const STORE_WIKIDATA_LEXEME_FORMS = 'wdLexemeForms';
const STORE_WIKIDATA_LEXEME_SENSES = 'wdLexemeSenses';

$storeExpectedKeys = [
    STORE_WIKIDATA_EDITS,
    STORE_WIKIDATA_PAGES_NS_0,
    STORE_WIKIDATA_PAGES_NS_120,
    STORE_WIKIDATA_PAGES_NS_146,
    STORE_WIKIDATA_LEXEME_FORMS,
    STORE_WIKIDATA_LEXEME_SENSES,
];

/**
 * Load our truth
 */

$store = new \Addshore\Twitter\WikidataMeter\KeyValue\JsonStorageNet();
$store->syncFromSourceOfTruth();
$store->initKeys($storeExpectedKeys, 0);

/**
 * GET DATA
 */

$wikidata = MediaWiki::newFromEndpoint( 'https://www.wikidata.org/w/api.php' );
$graphite = new Guzzle(['base_uri' => 'https://graphite.wikimedia.org/render']);

$wdEdits = ( new Addshore\Twitter\WikidataMeter\DataPoint\MediaWikiEdits( $wikidata ) )->get();
$wdNsPages = [
    0 => ( new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'daily.wikidata.site_stats.pages_by_namespace.0.nonredirects' ) )->get(),
    120 => ( new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'daily.wikidata.site_stats.pages_by_namespace.120.nonredirects' ) )->get(),
    146 => ( new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'daily.wikidata.site_stats.pages_by_namespace.146.nonredirects' ) )->get(),
];
$wdLexemeForms = ( new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'sumSeries(daily.wikidata.datamodel.lexeme.languageItem.*.forms)' ) )->get();
$wdLexemeSenses = ( new Addshore\Twitter\WikidataMeter\DataPoint\GraphiteDailyLatest( $graphite, 'sumSeries(daily.wikidata.datamodel.lexeme.languageItem.*.senses)' ) )->get();

/**
 * FIGURE OUT OUTPUT
 */

$numberToWords = (new NumberToWords())->getNumberTransformer('en');

$toPost = [];
// wdEdits
if ( intdiv($wdEdits, 10000000) > intdiv($store->getValue(STORE_WIKIDATA_EDITS), 10000000) ) {
    $roundNumber = floor($wdEdits/10000000)*10000000;
    $formatted = number_format($roundNumber);
    $words = $numberToWords->toWords($roundNumber);
    $toPost[] = <<<TWEET
    Wikidata now has over ${formatted} edits!
    That's over ${words}...
    You can find the milestone edit here https://www.wikidata.org/w/index.php?diff=${roundNumber}
    TWEET;
    $store->setValue(STORE_WIKIDATA_EDITS, $wdEdits);
}
// wdNsPages0 (items)
if ( intdiv($wdNsPages[0], 1000000) > intdiv($store->getValue(STORE_WIKIDATA_PAGES_NS_0), 1000000) ) {
    $roundNumber = floor($wdNsPages[0]/1000000)*1000000;
    $formatted = number_format($roundNumber);
    $words = $numberToWords->toWords($roundNumber);
    $toPost[] = <<<TWEET
    Wikidata now has over ${formatted} Items!
    That's over ${words}...
    You can find the latest creations here https://www.wikidata.org/wiki/Special:NewPages?namespace=0
    TWEET;
    $store->setValue(STORE_WIKIDATA_PAGES_NS_0, $wdNsPages[0]);
}
// wdNsPages120 (properties)
if ( intdiv($wdNsPages[120], 100) > intdiv($store->getValue(STORE_WIKIDATA_PAGES_NS_120), 100) ) {
    $roundNumber = floor($wdNsPages[120]/100)*100;
    $formatted = number_format($roundNumber);
    $words = $numberToWords->toWords($roundNumber);
    $toPost[] = <<<TWEET
    Wikidata now has over ${formatted} Properties!
    That's over ${words}...
    You can find the latest creations here https://www.wikidata.org/wiki/Special:NewPages?namespace=120
    TWEET;
    $store->setValue(STORE_WIKIDATA_PAGES_NS_120, $wdNsPages[120]);
}
// wdNsPages146 (lexemes)
if ( intdiv($wdNsPages[146], 10000) > intdiv($data[STORE_WIKIDATA_PAGES_NS_146], 10000) ) {
    $roundNumber = floor($wdNsPages[146]/10000)*10000;
    $formatted = number_format($roundNumber);
    $words = $numberToWords->toWords($roundNumber);
    $toPost[] = <<<TWEET
    Wikidata now has over ${formatted} Lexemes!
    That's over ${words}...
    You can find the latest creations here https://www.wikidata.org/wiki/Special:NewPages?namespace=146
    TWEET;
    $store->setValue(STORE_WIKIDATA_PAGES_NS_146, $wdNsPages[146]);
}
// wdLexemeForms (starting at 9,815,747)
if ( intdiv($wdNsPages[146], 10000) > intdiv($store->getValue(STORE_WIKIDATA_PAGES_NS_146), 100000) ) {
    $roundNumber = floor($wdLexemeForms/100000)*100000;
    $formatted = number_format($roundNumber);
    $words = $numberToWords->toWords($roundNumber);
    $toPost[] = <<<TWEET
    Wikidata now has over ${formatted} Forms on Lexemes!
    That's over ${words}...
    TWEET;
    $store->setValue(STORE_WIKIDATA_LEXEME_FORMS, $wdLexemeForms);
}
// wdLexemeSenses (starting at 153,036)
if ( intdiv($wdLexemeSenses, 10000) > intdiv($store->getValue(STORE_WIKIDATA_LEXEME_SENSES), 10000) ) {
    $roundNumber = floor($wdLexemeSenses/10000)*10000;
    $formatted = number_format($roundNumber);
    $words = $numberToWords->toWords($roundNumber);
    $toPost[] = <<<TWEET
    Wikidata now has over ${formatted} Senses on Lexemes!
    That's over ${words}...
    TWEET;
    $store->setValue(STORE_WIKIDATA_LEXEME_SENSES, $wdLexemeSenses);
}

/**
 * Output the desired things & store new truth
 */

$out = new \Addshore\Twitter\WikidataMeter\Output\MultiOut(
    new \Addshore\Twitter\WikidataMeter\Output\EchoOut(),
    new \Addshore\Twitter\WikidataMeter\Output\TwitterOut(__DIR__ . '/vendor/atymic/twitter/config/twitter.php'),
);

if( $store->changed() ) {
    echo "Persisting changed state." . PHP_EOL;
    var_dump($store->dump());
    $store->syncToSourceOfTruth();
}

foreach( $toPost as $toOutput ) {
    $out->output( $toOutput );
    sleep(2);
}

echo PHP_EOL . "All done!" . PHP_EOL;
