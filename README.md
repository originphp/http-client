# HTTP Client

![license](https://img.shields.io/badge/license-MIT-brightGreen.svg)
[![build](https://travis-ci.com/originphp/http-client.svg?branch=master)](https://travis-ci.com/originphp/http-client)
[![coverage](https://coveralls.io/repos/github/originphp/http-client/badge.svg?branch=master)](https://coveralls.io/github/originphp/http-client?branch=master)

The HTTP client is a simple yet very powerful utility for making HTTP requests.

## Installation

To install this package

```linux
$ composer require originphp/http-client
```

## Sending Requests

### Get Request

To send a GET request

```php
use Origin\HttpClient\Http;
$http = new Http();
$response = $http->get('https://api.example.com/posts');

// To use query parameters. https://api.example.com/posts?api_token=1234-1234-1234-1234
$response = $http->get('https://api.example.com/posts',[
    'query' => ['api_token' => '1234-1234-1234-1234']
]);

// An example with more options
$response = $http->get('https://api.example.com/posts',[
    'headers' => ['Accept' => 'application/json'],
    'cookies' => ['name' => 'value'],
    'userAgent' => 'MyApp',
    'referer' => 'https://www.somewebsite.com/search'
]);
```

The full list of options are detailed below.

### Head Request

To make HEAD request, where the body of the request is not fetched.

```php
use Origin\HttpClient\Http;
$http = new Http();
$response = $http->head('https://api.example.com/posts');

// To use query parameters. https://api.example.com/posts?api_token=1234-1234-1234-1234
$response = $http->head('https://api.example.com/posts',[
    'query' => ['api_token' => '1234-1234-1234-1234']
]);

// An example with more options
$response = $http->head('https://api.example.com/posts',[
    'headers' => ['Accept' => 'application/json'],
    'cookies' => ['name' => 'value'],
    'userAgent' => 'MyApp',
    'referer' => 'https://www.somewebsite.com/search'
]);
```

### Post Request

To send a POST request. In REST terms post requests are used to create a record.

```php
use Origin\HttpClient\Http;
$http = new Http();

// to send a post request with empty data
$response = $http->post('https://api.example.com/posts');

// to send a post request with data
$response = $http->post('https://api.example.com/posts',[
    'data' => [
        'title' => 'Article Title',
        'body' => 'Article body'
    ]
]);

// example with other options
$response = $http->post('https://api.example.com/posts',[
    'data' => [
        'title' => 'Article Title',
        'body' => 'Article body'
    ],
    'headers' => ['Accept' => 'application/json'],
    'cookies' => ['name' => 'value'],
    'userAgent' => 'MyApp',
    'referer' => 'https://www.somewebsite.com/search'
]);

```

To upload files using a post request.

```php
use Origin\HttpClient\Http;
$http = new Http();
$response = $http->post('https://api.example.com/posts',[
    'data' => [
        'contacts' => Http::file('/docs/contacts.csv')
    ]
]);

// Shortcut for Http::file
$response = $http->post('https://api.example.com/posts',[
    'data' => [
        'contacts' => '@/docs/contacts.csv'
    ]
]);
```

### Put Request

To send a PUT request. In REST terms put requests are used to modify a record with complete data (overwriting).

```php
use Origin\HttpClient\Http;
$http = new Http();
$response = $http->put('https://api.example.com/posts/1',[
    'data' => [
        'title' => 'Changed Article Title',
        'body' => 'Article body'
    ],
]);
```

### Patch Request

To send a PATCH request. In REST terms patch requests are used to modify an existing record with partial data.

```php
use Origin\HttpClient\Http;
$http = new Http();
$response = $http->patch('https://api.example.com/posts/1',[
     'data' => [
        'title' => 'Another Article Title',
    ],
]);
```

### Delete Request

To send a DELETE request.

```php
use Origin\HttpClient\Http;
$http = new Http();
$response = $http->delete('https://api.example.com/posts/1');

// Example passing some options
$response = $http->delete('https://api.example.com/posts/1',[
    'userAgent' => 'OriginPHP'
]);
```

## HTTP Request Options

The available options when making requests are

- query: appends vars to the query. e.g ['api_token' => '1234-1234-1234-1234']
- userAgent: the name of the user agent to use e.g. 'originPHP'
- referer: default null. The url of the referer e.g. 'https://www.example.com/search'
- redirect: default true. set to false to not follow redirects
- timeout: default timeout is 30 seconds
- cookieJar: default true. Persists cookies for instance. Set to false to not perist cookies. Pass a string with filename and path to save to and read cookies from. e.g. '/var/www/data/cookies.data'
- type: request and accept content type (json xml) e.g. 'json'
- auth: authtentication details. An array with username, password, and type (basic|digest|nltm)
- proxy: proxy server details. An array with proxy, username, password.
- curl: an array of curl options either string or constant e.g [CURLOPT_SSL_VERIFYHOST=>0, 'ssl_verifypeer'=>0]
- headers: an array of headers to set. e.g ['Accept' => 'application/json']
- cookies: an array of cookies to set. e.g. ['name' => 'value']
- fields: This option is for post/put/patch requests. Its an array of fields to be posted  e.g. ['title' => 'Article #1','description' => 'An article']

## Configuring the Http Client

You configure Http client so that you don't have to keep on passing options which makes code longer and more prone to errors. 

This is particularly useful when working with multiple requests in an instance. Any options passed when creating the instance will be used as default for each request, unless you specify something else during the request.


```php
use Origin\HttpClient\Http;
$http = new Http([
    'base' => 'https://www.example.com/api'
]);
$response = $http->get('/posts');
```

Other options:

- userAgent
- timeout
- cookieJar
- type
- auth
- proxy
- referer
- headers
- cookies
- verbose

## Exceptions (version ^2.0)

In 2.0 various exceptions have been added, and a HTTP protocol error handler has also been added. All exceptions from the http client extend the `HttpClientException` class.

### Request Exceptions

In the event of connection issues (DNS, timeout etc), a `ConnectionException` will be thrown, and a `TooManyRedirectsException` will be thrown on redirect loops, and any other cURL error will trigger a generic `RequestException`.

### HTTP Protocol Exceptions

By default any 4xx and 5xx errors will throw either `ClientErrorException` or `ServerErrorException` which both extend the `HttpException` class.

This behavior can be disabled by setting `httpErrors` to `false` when creating the `Http` instance. 

```php
$http = new Http([
    'httpErrors' => false
]);
```

To catch HTTP protocol errors

```php
try {
    (new Http())->get('http://wwww.google.com');
} catch (HttpException $exception) {
    // do something
}
```

## Working with Responses

When you make a HTTP request, a `Response` object is returned.

```php
use Origin\HttpClient\Http;
$http = new Http();
$response = $http->head('https://api.example.com/posts');

// working with the response body
$data = $response->body();
$data = $response->json(); // decodes a JSON response into an array
$data = $response->xml(); // converts XML response into an array

// Response stuff
$headers = $response->headers(); // headers
$cookies = $response->cookies(); // cookies from the *response*
$code = $response->statusCode();

// Assertions
$bool = $response->ok(); // has status code of 200
$bool = $response->success(); // has any 20x status code
$bool = $response->redirect(); // has a redirect status code 30x
```

## Cookies

By default cookies are persisted across all requests for the instance.

To change this behavior use the `cookieJar` option.

```php
use Origin\HttpClient\Http;

// Persist cookies for this session only (stores in array)
$http = new Http([
    'cookieJar' => true
]);

// Don't use cookies
$http = new Http([
    'cookieJar' => false;
]);

// Persist cookies to file
$http = new Http([
    'cookieJar' => '/var/www/data/cookies.data';
]);

```

### cURL Options

Occasionally, you might need to set additional cURL options, one example of this, is when there is an issue with SSL certificates. You can set cURL options with the CURLOPT constant or string version of it.

```php
use Origin\HttpClient\Http;
$http = new Http();
$response = $http->get('https://api.example.com/posts',[
    'curl' => [
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0
        ]
]);

$http = new Http();
$response = $http->get('https://api.example.com/posts',[
    'curl' => [
        'ssl_verifyhost' => 0,
        'ssl_verifypeer' => 0
        ]
]);
```

To set these as default for settings for all the requests, configure it when creating the Http instance.

```php
use Origin\HttpClient\Http;
$http = new Http([
    'curl' => [
        'ssl_verifyhost' => 0,
        'ssl_verifypeer' => 0
        ]
    ]);
```

### Setting Cookies

To set a cookie

```php
use Origin\HttpClient\Http;
$response = $http->get('https://api.example.com/posts',[
    'cookies' => [
        'name' => 'value'
        ]
    ]);
```

### User Authentication

The available authentication types are `basic`, `digest`, `nltm` or `any`.

```php
use Origin\HttpClient\Http;
$response = $http->get('https://api.example.com/posts',[
    'auth' => [
        'username' => 'foo',
        'password' => 'secret',
        'type' => 'digest
        ]
    ]);
```

### Using a Proxy

To send a HTTP request using a proxy server.

```php
use Origin\HttpClient\Http;

// without authentication
$response = $http->get('https://api.example.com/posts',[
    'proxy' => [
        'proxy' => 'https://www.proxy.com:8080'
        ]
    ]);

// with authentication
$response = $http->get('https://api.example.com/posts',[
    'proxy' => [
        'proxy' => 'https://www.proxy.com:8080',
        'username' => 'foo',
        'password' => 'secret'
        ]
    ]);
```