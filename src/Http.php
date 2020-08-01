<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2020 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types = 1);
namespace Origin\HttpClient;

use CURLFile;
use Exception;
use Origin\HttpClient\Exception\ClientErrorException;
use Origin\HttpClient\Exception\ConnectionException;
use Origin\HttpClient\Exception\NotFoundException;
use Origin\HttpClient\Exception\RequestException;
use Origin\HttpClient\Exception\ServerErrorException;
use Origin\HttpClient\Exception\TooManyRedirectsException;

class Http
{

    /**
     * Holds the configuration
     *
     *
     * @var array
     */
    protected $config = [];

    /**
     * Persist cookies in instance
     *
     * @var bool
     */
    protected $persistCookies = false;

    /**
     * Holds cookies to be persisted
     *
     * @var array
     */
    protected $cookies = [];

    /**
     * @var array
     */
    private $statusCodes = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error'
    ];
 
    /**
     * Constructor
     *
     * @param array $config The config options array supports the following keys
     * - base: base url so e.g. https://www.example.com/api
     * - userAgent
     * - timeout: default 30 seconds
     * - cookieJar:  default true. Can be bool or file to save and read cookies from
     * - redirect: default:true. follow location headers
     * - type: json/xml
     * - auth (username, password, type)
     * - proxy (username, password,proxy)
     * - httpErrors: default:true. Set to false to disable throwing exceptions on HTTP protocol errors 4xx and 5xx.
     *
     * - referer
     * - curl: curl options
     * - query: appends query to field
     * - headers
     * - cookies array
     * - fields (array of fields)
     * - verbose: default: false. CURL option verbose.
     *
     */
    public function __construct(array $config = [])
    {
        $config += [
            'timeout' => 30,
            'redirect' => true,
            'cookieJar' => true, // if this is set to true then cookies persisted for instance only
            'verbose' => false,
            'httpErrors' => true
        ];
        $this->config = $config;
        if ($this->config['cookieJar'] === true) {
            $this->persistCookies = true;
        }
    }

    /**
     * Gets the cookies that are being persisted during
     * this instance
     *
     * @return array|null
     */
    public function cookies(string $name = null)
    {
        if ($name === null) {
            return $this->cookies;
        }

        if (isset($this->cookies[$name])) {
            return $this->cookies[$name];
        }

        return null;
    }

    /**
    * Sends a GET request
    *
    * @param string $url
    * @param array $options The option keys are :
     * - query: appends vars to the query. e.g ['api_token'=>'1234-1234-1234-1234']
     * - userAgent: the name of the user agent to use e.g. 'originPHP'
     * - referer: default null. The url of the referer e.g. 'https://www.example.com/search'
     * - redirect: default true. set to false to not follow redirects
     * - timeout: default timeout is 30 seconds
     * - cookieJar: file to save and read cookies from. e.g. '/var/www/data/cookies.data'
     * - type: request and accept content type (json xml) e.g. 'json'
     * - auth: authtentication details. An array with username, password, and type (basic|digest|nltm)
     * - proxy: proxy server details. An array with proxy, username, password.
     * - curl: an array of curl options either string or constant e.g [CURLOPT_SSL_VERIFYHOST=>0, 'ssl_verifypeer'=>0]
     * - headers: an array of headers to set. e.g ['header'=>'value']
     * - cookies: an array of cookies to set. e.g. ['name'=>'value']
     *  @return \Origin\HttpClient\Response
    */
    public function get(string $url, array $options = []): Response
    {
        return $this->request('GET', $url, $options);
    }

    /**
    * Sends a HEAD request
    *
    * @param string $url
    * @param array $options
    * - query: appends vars to the query. e.g ['api_token'=>'1234-1234-1234-1234']
    * - userAgent: the name of the user agent to use e.g. 'originPHP'
    * - referer: default null. The url of the referer e.g. 'https://www.example.com/search'
    * - redirect: default true. set to false to not follow redirects
    * - timeout: default timeout is 30 seconds
    * - cookieJar: file to save and read cookies from. e.g. '/var/www/data/cookies.data'
    * - type: request and accept content type (json xml) e.g. 'json'
    * - auth: authtentication details. An array with username, password, and type (basic|digest|nltm)
    * - proxy: proxy server details. An array with proxy, username, password.
    * - curl: an array of curl options either string or constant e.g [CURLOPT_SSL_VERIFYHOST=>0, 'ssl_verifypeer'=>0]
    * - headers: an array of headers to set. e.g ['header'=>'value']
    * - cookies: an array of cookies to set. e.g. ['name'=>'value']
    *  @return \Origin\HttpClient\Response
    */
    public function head(string $url, array $options = []): Response
    {
        return $this->request('HEAD', $url, $options);
    }

    /**
    * Sends a POST request
    *
    * @param string $url
    * @param array $options The option keys are :
    * - fields: An array of fields to be posted  e.g. ['title'=>'Article #1','description'=>'An article']
    * - query: appends vars to the query. e.g ['api_token'=>'1234-1234-1234-1234']
    * - userAgent: the name of the user agent to use e.g. 'originPHP'
    * - referer: default null. The url of the referer e.g. 'https://www.example.com/search'
    * - redirect: default true. set to false to not follow redirects
    * - timeout: default timeout is 30 seconds
    * - cookieJar: file to save and read cookies from. e.g. '/var/www/data/cookies.data'
    * - type: request and accept content type (json xml) e.g. 'json'
    * - auth: authtentication details. An array with username, password, and type (basic|digest|nltm)
    * - proxy: proxy server details. An array with proxy, username, password.
    * - curl: an array of curl options either string or constant e.g [CURLOPT_SSL_VERIFYHOST=>0, 'ssl_verifypeer'=>0]
    * - headers: an array of headers to set. e.g ['header'=>'value']
    * - cookies: an array of cookies to set. e.g. ['name'=>'value']
    *  @return \Origin\HttpClient\Response
    */
    public function post(string $url, array $options = []): Response
    {
        return $this->request('POST', $url, $options);
    }

    /**
    * Sends a PUT request
    *
    * @param string $url
    * @param array $options The option keys are :
    * - fields: An array of fields to be posted  e.g. ['title'=>'Article #1','description'=>'An article']
    * - query: appends vars to the query. e.g ['api_token'=>'1234-1234-1234-1234']
    * - userAgent: the name of the user agent to use e.g. 'originPHP'
    * - referer: default null. The url of the referer e.g. 'https://www.example.com/search'
    * - redirect: default true. set to false to not follow redirects
    * - timeout: default timeout is 30 seconds
    * - cookieJar: file to save and read cookies from. e.g. '/var/www/data/cookies.data'
    * - type: request and accept content type (json xml) e.g. 'json'
    * - auth: authtentication details. An array with username, password, and type (basic|digest|nltm)
    * - proxy: proxy server details. An array with proxy, username, password.
    * - curl: an array of curl options either string or constant e.g [CURLOPT_SSL_VERIFYHOST=>0, 'ssl_verifypeer'=>0]
    * - headers: an array of headers to set. e.g ['header'=>'value']
    * - cookies: an array of cookies to set. e.g. ['name'=>'value']
    *  @return \Origin\HttpClient\Response
    */
    public function put(string $url, array $options = []): Response
    {
        return $this->request('PUT', $url, $options);
    }
    /**
    * Sends a PATCH request
    *
    * @param string $url
    * @param array $options The option keys are :
    * - fields: An array of fields to be posted  e.g. ['title'=>'Article #1','description'=>'An article']
    * - query: appends vars to the query. e.g ['api_token'=>'1234-1234-1234-1234']
    * - userAgent: the name of the user agent to use e.g. 'originPHP'
    * - referer: default null. The url of the referer e.g. 'https://www.example.com/search'
    * - redirect: default true. set to false to not follow redirects
    * - timeout: default timeout is 30 seconds
    * - cookieJar: file to save and read cookies from. e.g. '/var/www/data/cookies.data'
    * - type: request and accept content type (json xml) e.g. 'json'
    * - auth: authtentication details. An array with username, password, and type (basic|digest|nltm)
    * - proxy: proxy server details. An array with proxy, username, password.
    * - curl: an array of curl options either string or constant e.g [CURLOPT_SSL_VERIFYHOST=>0, 'ssl_verifypeer'=>0]
    * - headers: an array of headers to set. e.g ['header'=>'value']
    * - cookies: an array of cookies to set. e.g. ['name'=>'value']
    * @return \Origin\HttpClient\Response
    */
    public function patch(string $url, array $options = []): Response
    {
        return $this->request('PATCH', $url, $options);
    }
    /**
    * Sends a DELETE request
    *
    * @param string $url
    * @param array $options The option keys are :
    * - query: appends vars to the query. e.g ['api_token'=>'1234-1234-1234-1234']
    * - userAgent: the name of the user agent to use e.g. 'originPHP'
    * - referer: default null. The url of the referer e.g. 'https://www.example.com/search'
    * - redirect: default true. set to false to not follow redirects
    * - timeout: default timeout is 30 seconds
    * - cookieJar: file to save and read cookies from. e.g. '/var/www/data/cookies.data'
    * - type: request and accept content type (json xml) e.g. 'json'
    * - auth: authtentication details. An array with username, password, and type (basic|digest|nltm)
    * - proxy: proxy server details. An array with proxy, username, password.
    * - curl: an array of curl options either string or constant e.g [CURLOPT_SSL_VERIFYHOST=>0, 'ssl_verifypeer'=>0]
    * - headers: an array of headers to set. e.g ['header'=>'value']
    * - cookies: an array of cookies to set. e.g. ['name'=>'value']
    * - httpErrors: default:true If set to true, any 4xx and 5xx errors will throw either ClientErrorException or
    *               ServerErrorException which both extend the HttpException class.
    * @return \Origin\HttpClient\Response
    */
    public function delete(string $url, array $options = []): Response
    {
        return $this->request('DELETE', $url, $options);
    }
    /**
      * Sends the actual request through cURL
      *
      * @param array $options
      * @return \Origin\HttpClient\Response
      */
    protected function send(array $options): Response
    {
        $headers = [];
        $body = '';
        
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        if ($response === false) {
            $code = curl_errno($curl);
            $errorMessage = curl_error($curl);
            $status = ($code === CURLE_OPERATION_TIMEOUTED)?500:504; // error 500 or gateway timeout
            curl_close($curl);

            if (in_array($code, [CURLE_COULDNT_RESOLVE_PROXY,CURLE_COULDNT_RESOLVE_HOST,CURLE_COULDNT_CONNECT,CURLE_OPERATION_TIMEDOUT])) {
                throw new ConnectionException($errorMessage, $status);
            }
            if ($code === CURLE_TOO_MANY_REDIRECTS) {
                throw new TooManyRedirectsException($errorMessage, $status);
            }
            
            throw new RequestException($errorMessage, $status);
        }

        # Process Curl Response
        $info = curl_getinfo($curl);
        $code = $info['http_code'];

        if ($this->config['httpErrors']) {
            $this->httpErrorHandler($code);
        }
 
        // parse string responses, using CURLOPT_FILE will return bool
        if (is_string($response)) {
            list($headers, $body) = $this->parseResponse($curl, $response);
        }

        $cookies = $this->parseCookies($headers);
        $headers = $this->normalizeHeaders($headers);

        // Cookies
        curl_close($curl);

        # Create the Response Object
        $response = new Response();
        $response->body($body);
        $response->statusCode($code);
        foreach ($headers as $header => $value) {
            $response->header($header, $value);
        }
        
        foreach ($cookies as $name => $value) {
            $response->cookie($name, $value['value'], $value);
            if ($this->persistCookies) {
                $this->cookies[$name] = $value;
            }
        }
        
        return $response;
    }

    /**
     * HTTP protocol error handler function
     *
     * @param integer $code
     * @return void
     */
    private function httpErrorHandler(int $code) : void
    {
        if ($code >= 400 && $code <= 599) {
            $message = $this->statusCodes[$code] ? $code . ' ' . $this->statusCodes[$code] : 'HTTP Error ' . $code;
            if ($code >= 400 && $code <= 499) {
                throw new ClientErrorException($message, $code);
            }
            throw new ServerErrorException($message, $code);
        }
    }

    /**
     * Builds the request
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @return \Origin\HttpClient\Response
     */
    protected function request(string $method, string $url, array $options = []): Response
    {
        $options += $this->config;
        $url = $this->buildUrl($url, $options);
         
        $options = $this->buildOptions(strtoupper($method), $url, $options);

        return $this->send($options);
    }

    /**
     * Returns a cURL file object
     * @return \CURLFile
     */
    public static function file(string $filename): CURLFile
    {
        if (! file_exists($filename)) {
            throw new NotFoundException("{$filename} could not be found");
        }
        $mime = mime_content_type($filename);
        $name = pathinfo($filename, PATHINFO_BASENAME);

        return new CURLFile($filename, $mime, $name);
    }

    /**
     * Build the headers for the request
     *
     * @param array $options
     * @return array
     */
    protected function buildRequestHeaders(array $options): array
    {
        // Process headers
        $cookies = [];
        if ($options['cookieJar'] === true) {
            foreach ($this->cookies as $cookie) {
                $cookies[] = rawurlencode($cookie['name']) . '=' . rawurlencode($cookie['value']);
            }
        }
        if (! empty($options['cookies']) and is_array($options['cookies'])) {
            foreach ($options['cookies'] as $name => $value) {
                $cookies[] = rawurlencode($name) . '=' . rawurlencode($value);
                $this->cookies[$name] = ['name' => $name,'value' => $value];
            }
        }

        if ($cookies) {
            $options['headers']['Cookie'] = implode('; ', $cookies);
        }

        if (! empty($options['type']) and in_array($options['type'], ['json','xml'])) {
            $type = 'application/' . $options['type'];
            $options['headers']['Content-Type'] = $options['headers']['Accept'] = $type;
        }
        
        $headers = [];
        if (! empty($options['headers'])) {
            foreach ($options['headers'] as $header => $value) {
                $headers[] = "{$header}: {$value}";
            }
        }
        
        return $headers;
    }

    /**
     * Builds the curl options
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @return array
     */
    protected function buildOptions(string $method, string $url, array $options): array
    {
        $out = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $this->buildRequestHeaders($options),
            CURLOPT_TIMEOUT => $options['timeout'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        switch ($method) {
               case 'GET':
                   $out[CURLOPT_HTTPGET] = true;
               break;
               case 'HEAD':
                   $out[CURLOPT_NOBODY] = true;
               break;
               case 'POST':
                   $out[CURLOPT_POST] = true;
               break;
               default:
                   $out[CURLOPT_CUSTOMREQUEST] = $method;
               break;
           }

        if ($options['verbose']) {
            $out[CURLOPT_VERBOSE] = true;
        }
        if (! empty($options['userAgent'])) {
            $out[CURLOPT_USERAGENT] = $options['userAgent'];
        }

        if (! empty($options['referer'])) {
            $out[CURLOPT_REFERER] = $options['referer'];
        }

        if (! empty($options['redirect'])) {
            $out[CURLOPT_FOLLOWLOCATION] = $options['redirect'];
        }

        if (in_array($method, ['POST','PUT','PATCH'])) {
            if (! empty($options['fields']) and is_array($options['fields'])) {
                foreach ($options['fields'] as $key => $value) {
                    if (is_string($value) and substr($value, 0, 1) === '@') {
                        $options['fields'][$key] = Http::file(substr($value, 1));
                    }
                }
                if (! empty($options['type']) and $options['type'] === 'json') {
                    $out[CURLOPT_POSTFIELDS] = json_encode($options['fields']);
                } else {
                    // Passing an array to CURLOPT_POSTFIELDS will encode the data as multipart/form-data,
                    // while passing a URL-encoded string will encode the data as application/x-www-form-urlencoded.
                    // Post not working on the other. Probably missing header?
                    $out[CURLOPT_POSTFIELDS] = http_build_query($options['fields']);
                }
            }
        }

        if (! empty($options['cookieJar']) and is_string($options['cookieJar'])) {
            $out[CURLOPT_COOKIEFILE] = $options['cookieJar'];
            $out[CURLOPT_COOKIEJAR] = $options['cookieJar'];
        }

        if (! empty($options['auth'])) {
            $options['auth'] += ['username' => null,'password' => null,'type' => 'basic'];
            $map = ['basic' => CURLAUTH_BASIC, 'digest' => CURLAUTH_DIGEST, 'ntlm' => CURLAUTH_NTLM,'any' => CURLAUTH_ANY];
            $out[CURLOPT_HTTPAUTH] = $map[$options['auth']['type']] ?? CURLAUTH_BASIC;
            $out[CURLOPT_USERPWD] = $options['auth']['username'] . ':' . $options['auth']['password'];
        }

        if (isset($options['proxy']['proxy'])) {
            $out[CURLOPT_PROXY] = $options['proxy']['proxy'];
            if (isset($options['proxy']['username'])) {
                $password = $options['proxy']['password'] ?? '';
                $out[CURLOPT_PROXYUSERPWD] = $options['proxy']['username'] . ':' . $password;
            }
        }
       
        if (isset($options['curl']) and is_array($options['curl'])) {
            foreach ($options['curl'] as $key => $value) {
                if (is_string($key)) {
                    if (stripos($key, 'CURLOPT_') === false) {
                        $key = 'CURLOPT_' . $key;
                    }
                    $key = constant(strtoupper($key));
                }
                $out[$key] = $value;
            }
        }

        return $out;
    }

    /**
     * Builds the url using options and query
     *
     * @param string $url
     * @param array $options
     * @return string
     */
    protected function buildUrl(string $url, array $options): string
    {
        if (! empty($options['base'])) {
            $url = $options['base'] . $url;
        }
        
        if (! empty($options['query']) and is_array($options['query'])) {
            $url .= '?' . http_build_query($options['query']);
        }

        return $url;
    }

    /**
      * Parses the response to get headers and body
      *
      * @param string $response
      * @return array
      */
    protected function parseResponse($ch, $response): array
    {
        // Parse Response
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerString = trim(substr($response, 0, $headerSize));
        $headers = explode("\r\n", $headerString);
        $body = substr($response, $headerSize);

        return [$headers,$body];
    }

    /**
     * Parses the cookies from headers.
     */
    protected function parseCookies(array &$headers): array
    {
        $cookies = [];
        foreach ($headers as $i => $header) {
            if (substr($header, 0, 12) === 'Set-Cookie: ') {
                $cookie = $this->parseCookie($header);
                $cookies[$cookie['name']] = $cookie;
                unset($headers[$i]);
            }
        }

        return $cookies;
    }

    /**
     * Parses the value of a Set-Cookie: heder
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
     * @param string $header line e.g. Set-Cookie: foo=bar;
     * @return array
     */
    protected function parseCookie(string $header): array
    {
        list($void, $cookie) = explode('Set-Cookie: ', $header);
        $cookie = explode('; ', $cookie);
        // Parse the name and value
        list($name, $value) = explode('=', array_shift($cookie), 2);
        $out = [
            'name' => $name,
            'value' => rawurldecode($value),
            'expires' => null,
            'path' => null,
            'domain' => null,
        ];
        // Parse additional settings. e.g Domain,Path,Expires etc
        foreach ($cookie as $attr) {
            if (strpos($attr, '=') !== false) {
                list($key, $v) = explode('=', $attr, 2);
                if ($key === 'expires') {
                    $v = strtotime($v);
                }
                $out[strtolower($key)] = $v;
            } else {
                $out[] = $attr;
            }
        }

        return $out;
    }

    /**
     * Takes the curl headers and normalizes them
     *
     * @param array $headers
     * @return array $result
     */
    protected function normalizeHeaders(array $headers): array
    {
        $result = [];
      
        foreach ($headers as $header) {
            if (strpos($header, ':') !== false) {
                list($header, $value) = explode(':', $header);
                $value = trim($value);
            } else {
                $value = null;
            }
            $result[$header] = $value;
        }

        return $result;
    }
}
