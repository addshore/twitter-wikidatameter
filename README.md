# Twitter WikidataMeter Bot

I'm the code, and deployment of Twitter bot [WikidataMeter](https://twitter.com/WikidataMeter).
I tweet a few fun things about Wikidata as it grows.

## Tweets

Feel free to suggest more tweets by creating an Github issue.

Everytime another **1,000,000 edits** happens

![](https://i.imgur.com/Nr9MSVQ.png)

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

- [Github Actions](https://github.com/features/actions) - Building Docker images & running the cron for the bot
- [Wikidata](https://www.wikidata.org) - Getting the data
- [JSONStorage.net](https://www.jsonstorage.net/) - Some persistence between runs
- [Twitter](https://www.twitter.com) - For publishing the tweets

Libraries:

- [addwiki/mediawiki-api-base](https://github.com/addwiki/mediawiki-api-base) - Talking to the Wikidata API
- [atymic/twitter](https://github.com/atymic/twitter) - Talking to the Twitter API
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) - Reading env vars from a .env file
- [guzzlehttp/guzzle](https://github.com/guzzle/guzzle) - Talking to the JSONStorage.net API
