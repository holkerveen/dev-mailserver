# Development Mailserver for Docker

This docker image wil set up a docker image for use as a mail server in your local development setup. It provides you with a mail server which captures all email into a single account, and shares access to it through a web interface.

> Note that this image is solely intended as a development environment. Only use it in an isolated development setup because there are some big security holes when this image is exposed to the internet.

This image is intended for use in testing email sending capacity of your application, and functional testing of your email features.

## Postfix MTA

There is a postfix MTA provided which is configured without any security measures. As long as you have access to it, you can connect to port 25 and start sending mail. All emails, directed at any email address on any domain, will be captured and delivered to a single local inbox.

## API, Webapp, Websockets

The received mail is available through a very simple API and a web app. The web app leverages a websocket connection to receive near-instant updates when new mail arrives.

# Getting started

You can run the image standalone.

```shell script
docker run --rm -p80:80 -p81:81
```

You will probably change your port numbers to allow other containers to run on port 80, however you will then need to tell the Web app where to find the websocket target as the default value will not work:

```shell script
docker run --rm -p1080:80 -p1081:81 -e WEBSOCKET_URL=ws://localhost:1081
```

## Using docker-compose

Add the following service definition to your docker-compose.yml file in order to use it in your own setup:

```yaml
services:
    mail:
      image: holkerveen/dev-mailserver
    ports:
      - 1080:80
      - 1081:81
    environment:
      - WEBSOCKET_URL=ws://localhost:1081
```
