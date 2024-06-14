# EFC Fiftyniners

Mono repository

- Website
- CLI

## Console Commands

Send an email with template `template_name` to all members:

```shell
bin/console app:email:send template_name
```

Send an email only to members with tag 'tag_1':

```shell
bin/console app:email:send template_name -f tag_1
```

## Template Variables

- `member` - Member entity
- `ticket` - SeasonTicket entity
- `season` - season expression (e. g. '2024/25')
- `seasonStartYear` - start year of season (e. g. 2024 for season 2024/25)

To overwrite current season use `--season` or `-s` option:

```shell
bin/console app:email:send template_name -s 2025
```

Means

```twig
season = '2025/26'
seasonStartYear = 2025
```

## PHP Debugging

Using Xdebug 3 with Docker.

Debugging via FPM/CLI is enabled and ready in both fpm/cli containers. Just create a breakpoint and start listening in PHPStorm.

Debugging via CLI:

```shell
export XDEBUG_MODE=debug
php -dxdebug.start_with_request=yes bin/console app:message:send shirt_effv_2024 -s 2024
php -dxdebug.start_with_request=yes vendor/bin/codecept run -- Acceptance MessageSendCest
```

Debugging with cURL:

```shell
curl -b XDEBUG_SESSION=PHPSTORM https://domain.tld/api/v1/live
```