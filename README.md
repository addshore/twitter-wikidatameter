# Twitter WikidataMeter Bot

I'm the code, and deployment of Twitter bot [WikidataMeter](https://twitter.com/WikidataMeter).
I tweet a few fun things about Wikidata as it grows.

## Tweets

Feel free to suggest more tweets by creating an Github issue.

### Everytime another 1,000,000 edits happens

Bot checks this data every 30 minutes, the value that is checked is live.

![](https://i.imgur.com/Nr9MSVQ.png)

### Everytime Item count crosses a 1,000,000 boundary

Bot checks this data every 30 minutes, the value that is checked is calculated once a day.

Screenshot comming soon...

### Everytime Property count crosses a 100 boundary

Bot checks this data every 30 minutes, the value that is checked is calculated once a day.

Screenshot comming soon...

### Everytime Lexeme count crosses a 10,000 boundary

Bot checks this data every 30 minutes, the value that is checked is calculated once a day.

Screenshot comming soon...

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
- [Wikidata](https://www.wikidata.org) - Getting the edit data (live)
- [Wikimedia Graphite](graphite.wikimedia.org) - Getting entity count data (daily)
- [JSONStorage.net](https://www.jsonstorage.net/) - Some persistence between runs
- [Twitter](https://www.twitter.com) - For publishing the tweets

Libraries:

- [addwiki/mediawiki-api-base](https://github.com/addwiki/mediawiki-api-base) - Talking to the Wikidata API
- [atymic/twitter](https://github.com/atymic/twitter) - Talking to the Twitter API
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) - Reading env vars from a .env file
- [guzzlehttp/guzzle](https://github.com/guzzle/guzzle) - Talking to the JSONStorage.net API
