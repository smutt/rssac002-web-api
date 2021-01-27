# rssac002-web-api
A web API for RSSAC002 data using the data collected
[here](https://github.com/rssac-caucus/RSSAC002-data). This project is
a work in progress and subject to change.

## Entry Points
There are five entry points for the API that receive HTTP GET requests
and return time series output in JSON.

`api/v1/load-time`
`api/v1/rcode-volume`
`api/v1/traffic-sizes`
`api/v1/traffic-volume`
`api/v1/unique-sources`

## Parameters
All five entry points take the following three parameters.

#### rsi
A list of Root Server Identifiers(RSIs) to return data for.
##### Examples:
`a-m`
`a,b,m,f`
`m,c-k`

#### start_date
An incusive date in the form YYYY-MM-DD that marks the beginning of
the time series.

#### end_date
An incusive date in the form YYYY-MM-DD that marks the end of
the time series.

### traffic-volume
In addition to the standard parameters, `traffic-volume` takes an
additional two parameters.

#### totals
`totals` can be set to either `sent` or `received`.

If set to `sent` the sum total of dns-tcp-responses-sent-ipv4,
dns-tcp-responses-sent-ipv6, dns-udp-responses-sent-ipv4, and
dns-udp-responses-sent-ipv6 will be returned.

If set to `received` the sum total of dns-tcp-queries-received-ipv4,
dns-tcp-queries-received-ipv6, dns-udp-queries-received-ipv4, and
dns-udp-queries-received-ipv6 will be returned.

`totals` defaults to `null`.

#### divisor
`divisor` may be set to an integer greater than zero and divisible
by 10. All values returned are divided by `divisor`. This can be
useful when dealing very large values.

`divisor` defaults to 1.

## Returned Data
Date is returned in JSON dictionary format per rsi per date. In some
cases the RSSAC002 data files cannot be read or are missing, in which
case `null` will be returned for a given date. Programs using this
data should be able to handle potential `null` values for any rsi and
for any date.

## prep_data.php
`prep_data.php` must be run from the CLI prior to serving any data. It
reads the RSSAC002 data files, parses their values into data structures, and then
serializes to disk. Subsequent calls to the API then only have to read
the serialized data structures from disk.

At the top of `lib.php` are the variables `$RSSAC002_DATA_ROOT` and
`$SERIALIZED_ROOT`. These must be set to the location of the RSSAC002
data files and destination for the serialized data structures, respectively.
