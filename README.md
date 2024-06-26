# EFC Fiftyniners

Mono repository

- Admin App (CLI)
- Website (in planning)

## Accounting

### Import Transactions

Put weekly CSV export from bank account to folder `/tmp` and run

```shell
bin/console accounting:transactions:import
```

### Match Transactions

Try to match transactions with liabilities with

```shell
bin/console accounting:transactions:match
```

### Financial Liability

Assign liabilities to one member with

```shell
bin/console accounting:liability:assign -a 2500 -t subscription -r "Beitrag 2024" --member 24001
```

Assign liabilities to multiple members by tag with

```shell
bin/console accounting:liability:assign -a 2500 -t subscription -r "Beitrag 2024" --tag member_2024
```

Datatable fields:

- `id`
- `member` (n:1)
- `amount` (integer)
- `type` (string) / "subscription", "ticketing", "merchandise"
- `reason` (string) - transaction reason
- `comment` (string) - additional information
- `dueAt` (datetime, nullable)
- `paidAt` (datetime, nullable)
- `transactions` (1:n)

| id | member_id | amount | type         | reason              | comment     | dueAt               | paidAt              | tags                   |
|----|-----------|--------|--------------|---------------------|-------------|---------------------|---------------------|------------------------|
| 1  | 5         | 17200  | ticketing    | Dauerkarte 2024     | Block 45    | 2024-06-06 23:59:59 | <null>              | ["season_ticket_2024"] |
| 2  | 8         | 2500   | subscription | Mitgliedschaft 2024 | <null>      | 2024-06-30 23:59:59 | <null>              | ["member_2024"]        |
| 3  | 8         | 17200  | ticketing    | Dauerkarte 2024     | Block 38    | 2024-06-06 23:59:59 | 2024-06-05 12:13:14 | ["season_ticket_2024"] |
| 4  | 8         | 5000   | merchandise  | Shirt Kurve 2024    | 2x L        | 2024-06-06 23:59:59 | 2024-06-18 14:15:16 | ["shirt_effv_2024"]    |
| 4  | 14        | 5000   | merchandise  | Shirt Kurve 2024    | 1x S, 1x XL | 2024-06-06 23:59:59 | <null>              | ["shirt_effv_2024"]    |

### Financial Transaction

Datatable fields:

- `id`
- `member` (n:1, nullable)
- `method` ("bank_transfer", "paypal")
- `reference` (IBAN or PayPal handle)
- `owner` (`string`) / account owner
- `reason` (string, nullable)
- `amount` (integer)
- `paidAt` (datetime)
- `tags`
- `createdAt` (datetime)
- `updatedAt` (datetime, nullable)

| id | member_id | liablilty_id | method        | reference       | owner    | reason                      | amount | paidAt              | tags                   |
|----|-----------|--------------|---------------|-----------------|----------|-----------------------------|--------|---------------------|------------------------|
| 1  | 8         | 3            | bank_transfer | <null>          | John Doe | Dauerkarte 2024             | 17200  | 2024-06-05 12:13:14 | ["season_ticket_2024"] |
| 2  | 8         | 4            | bank_transfer | <null>          | John Doe | Shirt Kurve 2024            | 5000   | 2024-06-18 14:15:16 | ["shirt_effv_2024"]    |
| 3  | 14        | <null>       | paypal        | julia@jones.com | Jane Doe | Shirt Kurve 2024 / Jane Doe | 2500   | 2024-06-19 13:14:15 | ["shirt_effv_2024"]    |

**Mapping**

Map weekly CSV export file to database table

Process only CSV rows of incoming payments (check column "Buchungstext" for value "Gutschrift").

Mapping of CSV values to datatable field:

- column `Buchungstag` to field `paidAt`
- column `Name Zahlungsbeteiligter` to field `owner`
- ~~column `IBAN Zahlungsbeteiligter` to field `reference`~~; IBAN value currently not filled in CSV export
- column `Verwendungszweck` to field `reason`
- column `Betrag` to field `amount` (convert double to integer, s. notes below)

Notes:

`amount` is an integer field. So multiply the `double` value `Betrag` with `100` and convert to `integer`.

**Owner Mapping**

- first check `bankAccountName`
- then check for `<firstName> <lastName>` or `<lastName>, <firstName>`
- then check for `<firstName> <middleName> <lastName>` or `<lastName>, <firstName> <middleName>`

## Mailing

### Send emails

Send an email with template `template_name` to all members:

```shell
bin/console app:email:send template_name
```

Send an email only to members with tag 'tag_1':

```shell
bin/console app:email:send template_name -f tag_1
```

Template variables:

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