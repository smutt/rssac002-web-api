# rssac002-web-api
A web API for RSSAC002 data using the data collected
[here](https://github.com/rssac-caucus/RSSAC002-data).

It is currently hosted at rssac002.depht.com and a list of example queries can
be viewed [here](http://rssac002.depht.com/). It will not
be hosted here permanently.

## Entry Points
There are 8 entry points for the API that receive HTTP GET requests
and return time series output in JSON.

`api/v1/load-time`

`api/v1/rcode-volume`

`api/v1/traffic-volume`

`api/v1/unique-sources`

`api/v1/udp-request-sizes`

`api/v1/udp-response-sizes`

`api/v1/tcp-request-sizes`

`api/v1/tcp-response-sizes`

## Parameters
All 8 entry points take the following three parameters.

#### rsi
A list of Root Server Identifiers(RSIs) to return data for.
##### Examples:
`a-m`
`a,b,m,f`
`m,c-k`

#### start_date
An inclusive date in the form YYYY-MM-DD that marks the beginning of
the time series.

#### end_date
An inclusive date in the form YYYY-MM-DD that marks the end of
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
`divisor` may be set to 1, or an integer greater than 1 and divisible
by 10. All values returned are divided by `divisor`. This can be
useful when dealing very large values.

`divisor` defaults to 1.

#### week
`week` may be set or not set. If set, data is returned per ISO 8601
week instead of per date. The first week is the week
containing `start_date`, and the last week is the week containing
`end_date`. The values for each date in a week are summed together. If
the value for a date is `null`, a value of `0` is used instead.

`week` defaults to `null`.

## Returned Data
Data is returned in JSON dictionary format per rsi per date. In some
cases the RSSAC002 data files cannot be read or are missing, in which
case `null` will be returned for a given value. Programs using this
data should be able to handle potential `null` values for any value.

If a Javascript program is going to be using this data, a function like the following may be useful.
```
// Summation function for dirty data
// Treat null as zero and ignore non-numbers
function sum_vals(){
  var rv = 0;
  for(var ii = 0; ii < arguments.length; ii++){
    if(arguments[ii] != null){
      if(typeof(arguments[ii]) == 'number'){
        rv += arguments[ii];
      }
    }
  }
  return rv;
}
```


## prep_data.php
`prep_data.php` must be run from the CLI prior to serving any data. It
reads the RSSAC002 data files, parses their values into data structures, and then
serializes these to disk. Subsequent calls to the API then only have to read
the serialized data structures from disk.

At the top of `lib.php` are the variables `$RSSAC002_DATA_ROOT` and
`$SERIALIZED_ROOT`. These must be set to the location of the RSSAC002
data files and destination for the serialized data structures, respectively.
