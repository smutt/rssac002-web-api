# rssac002-web-api
A web API for RSSAC002 and root server system (RSS) instance data.
For RSSAC002 data it uses the data collected [here](https://github.com/rssac-caucus/RSSAC002-data).
For RSS instance data it uses the data available [here](https://root-servers.org/archives/).
For root zone maintainer (RZM) data it uses the data available [here](https://a.root-servers.org/rssac-metrics/raw/).

It is currently used by the charts hosted [here](https://rssac002.root-servers.org).

## Entry Points
There are 10 entry points for the API that can receive HTTP GET requests and will return time series output in JSON.

`api/v1/load-time`

`api/v1/rcode-volume`

`api/v1/traffic-volume`

`api/v1/unique-sources`

`api/v1/udp-request-sizes`

`api/v1/udp-response-sizes`

`api/v1/tcp-request-sizes`

`api/v1/tcp-response-sizes`

`api/v1/zone-size`

`api/v1/instances-count`

`api/v1/instances-detail`

## Parameters
The following parameters are described below:
  `rsi`, `start_date`, `end_date`, `sum`, `totals`, and `week`.

#### rsi
A list of Root Server Identifiers(RSIs) to return data for. The `-` and `,` characters are special delimiters.
##### Examples:
`a-m`
`a,b,m,f`
`m,c-k`

`rsi` is not required and has no effect for entry point `zone-size`. All other entry points require `rsi`.

#### start_date
An inclusive date in the form YYYY-MM-DD that marks the beginning of the time series. `start-date` is required for all entry points.

#### end_date
An inclusive date in the form YYYY-MM-DD that marks the end of the time series. `end-date` is required for all entry points.

#### sum
`sum` may be set or not set for all entry points.
If set, data for all RSIs is summed together for each date. If `week` is set, data is summed together for each week.

`sum` has no effect for entry points `instances-detail`, `load-time`, and `zone-size`.

`sum` defaults to `null`.

#### week
`week` may be set or not set for all entry points.
If set, data is returned per ISO 8601 week instead of per date. The first week is the week containing `start_date`, and the last week is the week containing `end_date`.

The values for each date in a week are summed together. If the value for a date is `null`, a value of `0` is used instead.

For `zone-size` data for all root zone serial numbers is returned.

For `instances-detail` setting `week` has no effect.

`week` defaults to `null`.

### traffic-volume
In addition to the standard parameters, the `traffic-volume` entry point can take the additional parameter `totals`.

#### totals
`totals` can be set to either `sent` or `received` when calling `traffic-volume`.

If set to `sent` the sum total of dns-tcp-responses-sent-ipv4, dns-tcp-responses-sent-ipv6, dns-udp-responses-sent-ipv4, and
dns-udp-responses-sent-ipv6 will be returned.

If set to `received` the sum total of dns-tcp-queries-received-ipv4, dns-tcp-queries-received-ipv6, dns-udp-queries-received-ipv4, and
dns-udp-queries-received-ipv6 will be returned.

`totals` defaults to `null`.

## Returned Data
If `sum` is unset, data is returned in JSON dictionary format per rsi per date. If `sum` is set, data is returned in JSON dictionary format per date.

In some cases the RSSAC002 data files cannot be read or are missing, in which case `null` will be returned for a given value. Programs using this data should be able to handle potential `null` values for any value.

If a Javascript program is going to be using this data, a function like the following is useful.
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
`prep_data.php` must be run from the CLI prior to serving any data. It reads the RSSAC002 and instance data files, parses their values into data structures, and then
serializes these to disk. Subsequent calls to the API then only have to read the serialized data structures from disk.

At the top of `lib.php` are the variables `$RSSAC002_DATA_ROOT`, `$INSTANCE_DATA_ROOT`, and `$SERIALIZED_ROOT`. These must be set to the location of the RSSAC002
data files, instance data files, and destination for the serialized data structures, respectively.
