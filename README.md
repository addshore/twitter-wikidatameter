# Twitter WikidataMeter Bot

I'm the code, and deployment of Twitter bot [WikidataMeter](https://twitter.com/WikidataMeter).
I tweet a few fun things about Wikidata as it grows.

- Everytime another 1,000,000 edits happens.

## Development

You'll need to install my dependencies using composer.

```sh
composer install
```

And in order to fully integrate with the services you'll need to populate a `.env` file.
Checkout `.env.example` for what is needed.

Then just run the main script.

```sh
php run.php
```

## Deployment

I run on Github Actions on a cron using a docker container.

You can build the docker container using the following.

```sh
docker build . -t twitter-wikidatameter
```

## Makes use of

Services:

- [Github Actions](https://github.com/features/actions)
- [Wikidata](https://www.wikidata.org)
- [JSONStorage.net](https://www.jsonstorage.net/)
- [Twitter](https://www.twitter.com)

Libraries:

- [addwiki/mediawiki-api-base](https://github.com/addwiki/mediawiki-api-base)
- [atymic/twitter](https://github.com/atymic/twitter)
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)
- [guzzlehttp/guzzle](https://github.com/guzzle/guzzle)
