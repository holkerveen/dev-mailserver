# Development Mailserver for Docker

## TL;DR

```shell script
docker run --rm -p8123:80 -p81:81 -p25:25 holkerveen/dev-mailserver
```

The web interface is available at [localhost:8123](http://localhost:8123)

## Introduction

This docker image wil set up a docker image for use as a mail server in your local development setup. It provides you with a mail server which captures all email into a single account, and shares access to it through a web interface. It features near-instant mail delivery and a near-zero configuration setup.

> Note that this image is solely intended as a development environment. Only use it in an isolated development setup because there are some big security holes when this image is exposed to the internet.

This image is intended for use in testing email sending capacity of your application, and functional testing of your email features.

### Postfix MTA

There is a postfix MTA provided which is configured without any security measures. As long as you have access to it, you can connect to port 25 and start sending mail. All emails, directed at any email address on any domain, will be captured and delivered to a single local inbox.

### API, Webapp, Websockets

The received mail is available through a very simple API and a web app. The web app leverages a websocket connection to receive near-instant updates when new mail arrives.

# Getting started

You can run the image standalone.

```shell script
docker run --rm -p80:80 -p81:81 -p25:25
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

# Test 

Test your mail server by sending some mail to it. For a quick test, I always find a simple telnet session 
the easiest way to do so. See [this microsoft document](https://docs.microsoft.com/en-us/exchange/mail-flow/test-smtp-with-telnet?view=exchserver-2019#step-3-use-telnet-on-port-25-to-test-smtp-communication) for detailed instructions. 

Next, open the webbrowser running at [localhost:1080](http://localhost:1080) or similar. You should be rewarded with a simple inbox-style webpage.

Said webpage should have also opened a websocket connection to your mailserver. Try sending another mail; the mail you sent should almost instantly be shown on your page.

# Tips & tricks

## Persist mail file

Internally, all mail is stored in `/var/mail/root` as this is the default location for the root user's mail folder. If you want to keep your emails across container resets, you could mount a volume at `/var/mail`.

Symfony Framworks' `Mailer` component uses a DSN address to specify email server settings. You can simply use smtp://<hostname>:25 since the server does not require authentication. You will not need a third party transport. See [Sending Emails with Mailer](https://symfony.com/doc/current/mailer.html)
