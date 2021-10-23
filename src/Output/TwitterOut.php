<?php

namespace Addshore\Twitter\WikidataMeter\Output;

use Atymic\Twitter\ApiV1\Service\Twitter;
use Atymic\Twitter\Configuration as TwitterConf;
use Atymic\Twitter\Http\Factory\ClientCreator as TwitterClientCreator;
use Atymic\Twitter\Service\Querier as TwitterQuerier;

class TwitterOut implements Output {

    private $tw;

    /**
     * @param string $configPath consumable by TwitterConf::createFromConfig
     */
    public function __construct( string $configPath ){
        $this->tw = new Twitter(
            new TwitterQuerier(
                TwitterConf::createFromConfig(
                    require_once $configPath
                ),
                new TwitterClientCreator()
            )
        );
    }

    public function output( string $toOutput ) : void {
        $this->tw->postTweet([
            'status' => $toOutput
        ]);
    }

}