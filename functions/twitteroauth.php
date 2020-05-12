<?php
/**
 * Handle setting and storing config for TwitterOAuth.
 *
 * @author Abraham Williams <abraham@abrah.am>
 */
class Config
{
    /** @var int How long to wait for a response from the API */
    protected $timeout = 5;
    /** @var int how long to wait while connecting to the API */
    protected $connectionTimeout = 5;
    /** @var int How many times we retry request when API is down */
    protected $maxRetries = 0;
    /** @var int Delay in seconds before we retry the request */
    protected $retriesDelay = 1;

    /**
     * Decode JSON Response as associative Array
     *
     * @see http://php.net/manual/en/function.json-decode.php
     *
     * @var bool
     */
    protected $decodeJsonAsArray = false;
    /** @var string User-Agent header */
    protected $userAgent = 'TwitterOAuth (+https://twitteroauth.com)';
    /** @var array Store proxy connection details */
    protected $proxy = [];

    /** @var bool Whether to encode the curl requests with gzip or not */
    protected $gzipEncoding = true;

    /** @var integer Size for Chunked Uploads */
    protected $chunkSize = 250000; // 0.25 MegaByte

    /**
     * Set the connection and response timeouts.
     *
     * @param int $connectionTimeout
     * @param int $timeout
     */
    public function setTimeouts($connectionTimeout, $timeout)
    {
        $this->connectionTimeout = (int)$connectionTimeout;
        $this->timeout = (int)$timeout;
    }

    /**
     *  Set the number of times to retry on error and how long between each.
     *
     * @param int $maxRetries
     * @param int $retriesDelay
     */
    public function setRetries($maxRetries, $retriesDelay)
    {
        $this->maxRetries = (int)$maxRetries;
        $this->retriesDelay = (int)$retriesDelay;
    }

    /**
     * @param bool $value
     */
    public function setDecodeJsonAsArray($value)
    {
        $this->decodeJsonAsArray = (bool)$value;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = (string)$userAgent;
    }

    /**
     * @param array $proxy
     */
    public function setProxy(array $proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * Whether to encode the curl requests with gzip or not.
     *
     * @param boolean $gzipEncoding
     */
    public function setGzipEncoding($gzipEncoding)
    {
        $this->gzipEncoding = (bool)$gzipEncoding;
    }

    /**
     * Set the size of each part of file for chunked media upload.
     *
     * @param int $value
     */
    public function setChunkSize($value)
    {
        $this->chunkSize = (int)$value;
    }
}
/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith
 */
class Consumer
{
    /** @var string  */
    public $key;
    /** @var string  */
    public $secret;
    /** @var string|null  */
    public $callbackUrl;

    /**
     * @param string $key
     * @param string $secret
     * @param null $callbackUrl
     */
    public function __construct($key, $secret, $callbackUrl = null)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->callbackUrl = $callbackUrl;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "Consumer[key=$this->key,secret=$this->secret]";
    }
}
/**
 * @author Abraham Williams <abraham@abrah.am>
 */
class TwitterOAuthException extends \Exception
{
}
/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith
 */
/**
 * A class for implementing a Signature Method
 * See section 9 ("Signing Requests") in the spec
 */
abstract class SignatureMethod
{
    /**
     * Needs to return the name of the Signature Method (ie HMAC-SHA1)
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Build up the signature
     * NOTE: The output of this function MUST NOT be urlencoded.
     * the encoding is handled in OAuthRequest when the final
     * request is serialized
     *
     * @param Request $request
     * @param Consumer $consumer
     * @param Token $token
     *
     * @return string
     */
    abstract public function buildSignature(Request $request, Consumer $consumer, Token $token = null);

    /**
     * Verifies that a given signature is correct
     *
     * @param Request $request
     * @param Consumer $consumer
     * @param Token $token
     * @param string $signature
     *
     * @return bool
     */
    public function checkSignature(Request $request, Consumer $consumer, Token $token, $signature)
    {
        $built = $this->buildSignature($request, $consumer, $token);

        // Check for zero length, although unlikely here
        if (strlen($built) == 0 || strlen($signature) == 0) {
            return false;
        }

        if (strlen($built) != strlen($signature)) {
            return false;
        }

        // Avoid a timing leak with a (hopefully) time insensitive compare
        $result = 0;
        for ($i = 0; $i < strlen($signature); $i++) {
            $result |= ord($built{$i}) ^ ord($signature{$i});
        }

        return $result == 0;
    }
}
/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith
 */
/**
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104]
 * where the Signature Base String is the text and the key is the concatenated values (each first
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&'
 * character (ASCII code 38) even if empty.
 *   - Chapter 9.2 ("HMAC-SHA1")
 */
class HmacSha1 extends SignatureMethod
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return "HMAC-SHA1";
    }

    /**
     * {@inheritDoc}
     */
    public function buildSignature(Request $request, Consumer $consumer, Token $token = null)
    {
        $signatureBase = $request->getSignatureBaseString();

        $parts = [$consumer->secret, null !== $token ? $token->secret : ""];

        $parts = Util::urlencodeRfc3986($parts);
        $key = implode('&', $parts);

        return base64_encode(hash_hmac('sha1', $signatureBase, $key, true));
    }
}
/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith
 */
class Request
{
    protected $parameters;
    protected $httpMethod;
    protected $httpUrl;
    protected $json;
    public static $version = '1.0';

    /**
     * Constructor
     *
     * @param string     $httpMethod
     * @param string     $httpUrl
     * @param array|null $parameters
     */
    public function __construct($httpMethod, $httpUrl, array $parameters = [])
    {
        $parameters = array_merge(Util::parseParameters(parse_url($httpUrl, PHP_URL_QUERY)), $parameters);
        $this->parameters = $parameters;
        $this->httpMethod = $httpMethod;
        $this->httpUrl = $httpUrl;
    }

    /**
     * pretty much a helper function to set up the request
     *
     * @param Consumer $consumer
     * @param Token    $token
     * @param string   $httpMethod
     * @param string   $httpUrl
     * @param array    $parameters
     *
     * @return Request
     */
    public static function fromConsumerAndToken(
        Consumer $consumer,
        Token $token = null,
        $httpMethod,
        $httpUrl,
        array $parameters = [],
        $json = false
    ) {
        $defaults = [
            "oauth_version" => Request::$version,
            "oauth_nonce" => Request::generateNonce(),
            "oauth_timestamp" => time(),
            "oauth_consumer_key" => $consumer->key
        ];
        if (null !== $token) {
            $defaults['oauth_token'] = $token->key;
        }

        // The json payload is not included in the signature on json requests,
        // therefore it shouldn't be included in the parameters array.
        if ($json) {
            $parameters = $defaults;
        } else {
            $parameters = array_merge($defaults, $parameters);
        }

        return new Request($httpMethod, $httpUrl, $parameters);
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param $name
     *
     * @return string|null
     */
    public function getParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param $name
     */
    public function removeParameter($name)
    {
        unset($this->parameters[$name]);
    }

    /**
     * The request parameters, sorted and concatenated into a normalized string.
     *
     * @return string
     */
    public function getSignableParameters()
    {
        // Grab all parameters
        $params = $this->parameters;

        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }

        return Util::buildHttpQuery($params);
    }

    /**
     * Returns the base string of this request
     *
     * The base string defined as the method, the url
     * and the parameters (normalized), each urlencoded
     * and the concated with &.
     *
     * @return string
     */
    public function getSignatureBaseString()
    {
        $parts = [
            $this->getNormalizedHttpMethod(),
            $this->getNormalizedHttpUrl(),
            $this->getSignableParameters()
        ];

        $parts = Util::urlencodeRfc3986($parts);

        return implode('&', $parts);
    }

    /**
     * Returns the HTTP Method in uppercase
     *
     * @return string
     */
    public function getNormalizedHttpMethod()
    {
        return strtoupper($this->httpMethod);
    }

    /**
     * parses the url and rebuilds it to be
     * scheme://host/path
     *
     * @return string
     */
    public function getNormalizedHttpUrl()
    {
        $parts = parse_url($this->httpUrl);

        $scheme = $parts['scheme'];
        $host = strtolower($parts['host']);
        $path = $parts['path'];

        return "$scheme://$host$path";
    }

    /**
     * Builds a url usable for a GET request
     *
     * @return string
     */
    public function toUrl()
    {
        $postData = $this->toPostdata();
        $out = $this->getNormalizedHttpUrl();
        if ($postData) {
            $out .= '?' . $postData;
        }
        return $out;
    }

    /**
     * Builds the data one would send in a POST request
     *
     * @return string
     */
    public function toPostdata()
    {
        return Util::buildHttpQuery($this->parameters);
    }

    /**
     * Builds the Authorization: header
     *
     * @return string
     * @throws TwitterOAuthException
     */
    public function toHeader()
    {
        $first = true;
        $out = 'Authorization: OAuth';
        foreach ($this->parameters as $k => $v) {
            if (substr($k, 0, 5) != "oauth") {
                continue;
            }
            if (is_array($v)) {
                throw new TwitterOAuthException('Arrays not supported in headers');
            }
            $out .= ($first) ? ' ' : ', ';
            $out .= Util::urlencodeRfc3986($k) . '="' . Util::urlencodeRfc3986($v) . '"';
            $first = false;
        }
        return $out;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toUrl();
    }

    /**
     * @param SignatureMethod $signatureMethod
     * @param Consumer        $consumer
     * @param Token           $token
     */
    public function signRequest(SignatureMethod $signatureMethod, Consumer $consumer, Token $token = null)
    {
        $this->setParameter("oauth_signature_method", $signatureMethod->getName());
        $signature = $this->buildSignature($signatureMethod, $consumer, $token);
        $this->setParameter("oauth_signature", $signature);
    }

    /**
     * @param SignatureMethod $signatureMethod
     * @param Consumer        $consumer
     * @param Token           $token
     *
     * @return string
     */
    public function buildSignature(SignatureMethod $signatureMethod, Consumer $consumer, Token $token = null)
    {
        return $signatureMethod->buildSignature($this, $consumer, $token);
    }

    /**
     * @return string
     */
    public static function generateNonce()
    {
        return md5(microtime() . mt_rand());
    }
}
/**
 * The result of the most recent API request.
 *
 * @author Abraham Williams <abraham@abrah.am>
 */
class Response
{
    /** @var string|null API path from the most recent request */
    private $apiPath;
    /** @var int HTTP status code from the most recent request */
    private $httpCode = 0;
    /** @var array HTTP headers from the most recent request */
    private $headers = [];
    /** @var array|object Response body from the most recent request */
    private $body = [];
    /** @var array HTTP headers from the most recent request that start with X */
    private $xHeaders = [];

    /**
     * @param string $apiPath
     */
    public function setApiPath($apiPath)
    {
        $this->apiPath = $apiPath;
    }

    /**
     * @return string|null
     */
    public function getApiPath()
    {
        return $this->apiPath;
    }

    /**
     * @param array|object $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return array|object|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param int $httpCode
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            if (substr($key, 0, 1) == 'x') {
                $this->xHeaders[$key] = $value;
            }
        }
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getsHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $xHeaders
     */
    public function setXHeaders(array $xHeaders = [])
    {
        $this->xHeaders = $xHeaders;
    }

    /**
     * @return array
     */
    public function getXHeaders()
    {
        return $this->xHeaders;
    }
}
/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith
 */
class Token
{
    /** @var string */
    public $key;
    /** @var string */
    public $secret;

    /**
     * @param string $key    The OAuth Token
     * @param string $secret The OAuth Token Secret
     */
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * Generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "oauth_token=%s&oauth_token_secret=%s",
            Util::urlencodeRfc3986($this->key),
            Util::urlencodeRfc3986($this->secret)
        );
    }
}
/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith
 */
class Util
{
    /**
     * @param $input
     *
     * @return array|mixed|string
     */
    public static function urlencodeRfc3986($input)
    {
        $output = '';
        if (is_array($input)) {
            $output = array_map([__NAMESPACE__ . '\Util', 'urlencodeRfc3986'], $input);
        } elseif (is_scalar($input)) {
            $output = rawurlencode($input);
        }
        return $output;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function urldecodeRfc3986($string)
    {
        return urldecode($string);
    }

    /**
     * This function takes a input like a=b&a=c&d=e and returns the parsed
     * parameters like this
     * array('a' => array('b','c'), 'd' => 'e')
     *
     * @param string $input
     *
     * @return array
     */
    public static function parseParameters($input)
    {
        if (!is_string($input)) {
            return [];
        }

        $pairs = explode('&', $input);

        $parameters = [];
        foreach ($pairs as $pair) {
            $split = explode('=', $pair, 2);
            $parameter = Util::urldecodeRfc3986($split[0]);
            $value = isset($split[1]) ? Util::urldecodeRfc3986($split[1]) : '';

            if (isset($parameters[$parameter])) {
                // We have already recieved parameter(s) with this name, so add to the list
                // of parameters with this name

                if (is_scalar($parameters[$parameter])) {
                    // This is the first duplicate, so transform scalar (string) into an array
                    // so we can add the duplicates
                    $parameters[$parameter] = [$parameters[$parameter]];
                }

                $parameters[$parameter][] = $value;
            } else {
                $parameters[$parameter] = $value;
            }
        }
        return $parameters;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public static function buildHttpQuery(array $params)
    {
        if (empty($params)) {
            return '';
        }

        // Urlencode both keys and values
        $keys = Util::urlencodeRfc3986(array_keys($params));
        $values = Util::urlencodeRfc3986(array_values($params));
        $params = array_combine($keys, $values);

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');

        $pairs = [];
        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                // June 12th, 2010 - changed to sort because of issue 164 by hidetaka
                sort($value, SORT_STRING);
                foreach ($value as $duplicateValue) {
                    $pairs[] = $parameter . '=' . $duplicateValue;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        return implode('&', $pairs);
    }
}
/**
 * @author louis <louis@systemli.org>
 */
class JsonDecoder
{
    /**
     * Decodes a JSON string to stdObject or associative array
     *
     * @param string $string
     * @param bool   $asArray
     *
     * @return array|object
     */
    public static function decode($string, $asArray)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            return json_decode($string, $asArray, 512, JSON_BIGINT_AS_STRING);
        }

        return json_decode($string, $asArray);
    }
}

/**
 * TwitterOAuth class for interacting with the Twitter API.
 *
 * @author Abraham Williams <abraham@abrah.am>
 */
class TwitterOAuth extends Config
{
    const API_VERSION = '1.1';
    const API_HOST = 'https://api.twitter.com';
    const UPLOAD_HOST = 'https://upload.twitter.com';

    /** @var Response details about the result of the last request */
    private $response;
    /** @var string|null Application bearer token */
    private $bearer;
    /** @var Consumer Twitter application details */
    private $consumer;
    /** @var Token|null User access token details */
    private $token;
    /** @var HmacSha1 OAuth 1 signature type used by Twitter */
    private $signatureMethod;
    /** @var int Number of attempts we made for the request */
    private $attempts = 0;

    /**
     * Constructor
     *
     * @param string      $consumerKey      The Application Consumer Key
     * @param string      $consumerSecret   The Application Consumer Secret
     * @param string|null $oauthToken       The Client Token (optional)
     * @param string|null $oauthTokenSecret The Client Token Secret (optional)
     */
    public function __construct($consumerKey, $consumerSecret, $oauthToken = null, $oauthTokenSecret = null)
    {
        $this->resetLastResponse();
        $this->signatureMethod = new HmacSha1();
        $this->consumer = new Consumer($consumerKey, $consumerSecret);
        if (!empty($oauthToken) && !empty($oauthTokenSecret)) {
            $this->setOauthToken($oauthToken, $oauthTokenSecret);
        }
        if (empty($oauthToken) && !empty($oauthTokenSecret)) {
            $this->setBearer($oauthTokenSecret);
        }
    }

    /**
     * @param string $oauthToken
     * @param string $oauthTokenSecret
     */
    public function setOauthToken($oauthToken, $oauthTokenSecret)
    {
        $this->token = new Token($oauthToken, $oauthTokenSecret);
        $this->bearer = null;
    }

    /**
     * @param string $oauthTokenSecret
     */
    public function setBearer($oauthTokenSecret)
    {
        $this->bearer = $oauthTokenSecret;
        $this->token = null;
    }

    /**
     * @return string|null
     */
    public function getLastApiPath()
    {
        return $this->response->getApiPath();
    }

    /**
     * @return int
     */
    public function getLastHttpCode()
    {
        return $this->response->getHttpCode();
    }

    /**
     * @return array
     */
    public function getLastXHeaders()
    {
        return $this->response->getXHeaders();
    }

    /**
     * @return array|object|null
     */
    public function getLastBody()
    {
        return $this->response->getBody();
    }

    /**
     * Resets the last response cache.
     */
    public function resetLastResponse()
    {
        $this->response = new Response();
    }

    /**
     * Resets the attempts number.
     */
    private function resetAttemptsNumber()
    {
        $this->attempts = 0;
    }

    /**
     * Delays the retries when they're activated.
     */
    private function sleepIfNeeded()
    {
        if ($this->maxRetries && $this->attempts) {
            sleep($this->retriesDelay);
        }
    }


    /**
     * Make URLs for user browser navigation.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    public function url($path, array $parameters)
    {
        $this->resetLastResponse();
        $this->response->setApiPath($path);
        $query = http_build_query($parameters);
        return sprintf('%s/%s?%s', self::API_HOST, $path, $query);
    }

    /**
     * Make /oauth/* requests to the API.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array
     * @throws TwitterOAuthException
     */
    public function oauth($path, array $parameters = [])
    {
        $response = [];
        $this->resetLastResponse();
        $this->response->setApiPath($path);
        $url = sprintf('%s/%s', self::API_HOST, $path);
        $result = $this->oAuthRequest($url, 'POST', $parameters);

        if ($this->getLastHttpCode() != 200) {
            throw new TwitterOAuthException($result);
        }

        parse_str($result, $response);
        $this->response->setBody($response);

        return $response;
    }

    /**
     * Make /oauth2/* requests to the API.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array|object
     */
    public function oauth2($path, array $parameters = [])
    {
        $method = 'POST';
        $this->resetLastResponse();
        $this->response->setApiPath($path);
        $url = sprintf('%s/%s', self::API_HOST, $path);
        $request = Request::fromConsumerAndToken($this->consumer, $this->token, $method, $url, $parameters);
        $authorization = 'Authorization: Basic ' . $this->encodeAppAuthorization($this->consumer);
        $result = $this->request($request->getNormalizedHttpUrl(), $method, $authorization, $parameters);
        $response = JsonDecoder::decode($result, $this->decodeJsonAsArray);
        $this->response->setBody($response);
        return $response;
    }

    /**
     * Make GET requests to the API.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array|object
     */
    public function get($path, array $parameters = [])
    {
        return $this->http('GET', self::API_HOST, $path, $parameters, false);
    }

    /**
     * Make POST requests to the API.
     *
     * @param string $path
     * @param array  $parameters
     * @param bool   $json
     *
     * @return array|object
     */
    public function post($path, array $parameters = [], $json = false)
    {
        return $this->http('POST', self::API_HOST, $path, $parameters, $json);
    }

    /**
     * Make DELETE requests to the API.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array|object
     */
    public function delete($path, array $parameters = [])
    {
        return $this->http('DELETE', self::API_HOST, $path, $parameters, false);
    }

    /**
     * Make PUT requests to the API.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array|object
     */
    public function put($path, array $parameters = [])
    {
        return $this->http('PUT', self::API_HOST, $path, $parameters, false);
    }

    /**
     * Upload media to upload.twitter.com.
     *
     * @param string $path
     * @param array  $parameters
     * @param boolean  $chunked
     *
     * @return array|object
     */
    public function upload($path, array $parameters = [], $chunked = false)
    {
        if ($chunked) {
            return $this->uploadMediaChunked($path, $parameters);
        } else {
            return $this->uploadMediaNotChunked($path, $parameters);
        }
    }

    /**
     * Progression of media upload
     *
     * @param string $media_id
     *
     * @return array|object
     */
    public function mediaStatus($media_id)
    {
        return $this->http('GET', self::UPLOAD_HOST, 'media/upload', [
            'command' => 'STATUS',
            'media_id' => $media_id
        ], false);
    }

    /**
     * Private method to upload media (not chunked) to upload.twitter.com.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array|object
     */
    private function uploadMediaNotChunked($path, array $parameters)
    {
        if (! is_readable($parameters['media']) ||
            ($file = file_get_contents($parameters['media'])) === false) {
            throw new \InvalidArgumentException('You must supply a readable file');
        }
        $parameters['media'] = base64_encode($file);
        return $this->http('POST', self::UPLOAD_HOST, $path, $parameters, false);
    }

    /**
     * Private method to upload media (chunked) to upload.twitter.com.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array|object
     */
    private function uploadMediaChunked($path, array $parameters)
    {
        $init = $this->http('POST', self::UPLOAD_HOST, $path, $this->mediaInitParameters($parameters), false);
        // Append
        $segmentIndex = 0;
        $media = fopen($parameters['media'], 'rb');
        while (!feof($media)) {
            $this->http('POST', self::UPLOAD_HOST, 'media/upload', [
                'command' => 'APPEND',
                'media_id' => $init->media_id_string,
                'segment_index' => $segmentIndex++,
                'media_data' => base64_encode(fread($media, $this->chunkSize))
            ], false);
        }
        fclose($media);
        // Finalize
        $finalize = $this->http('POST', self::UPLOAD_HOST, 'media/upload', [
            'command' => 'FINALIZE',
            'media_id' => $init->media_id_string
        ], false);
        return $finalize;
    }

    /**
     * Private method to get params for upload media chunked init.
     * Twitter docs: https://dev.twitter.com/rest/reference/post/media/upload-init.html
     *
     * @param array  $parameters
     *
     * @return array
     */
    private function mediaInitParameters(array $parameters)
    {
        $return = [
            'command' => 'INIT',
            'media_type' => $parameters['media_type'],
            'total_bytes' => filesize($parameters['media'])
        ];
        if (isset($parameters['additional_owners'])) {
            $return['additional_owners'] = $parameters['additional_owners'];
        }
        if (isset($parameters['media_category'])) {
            $return['media_category'] = $parameters['media_category'];
        }
        return $return;
    }

    /**
     * Cleanup any parameters that are known not to work.
     *
     * @param array  $parameters
     *
     * @return array
     */
    private function cleanUpParameters(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            // PHP coerces `true` to `"1"` which some Twitter APIs don't like.
            if (is_bool($value)) {
                $parameters[$key] = var_export($value, true);
            }
        }
        return $parameters;
    }

    /**
     * @param string $method
     * @param string $host
     * @param string $path
     * @param array  $parameters
     * @param bool   $json
     *
     * @return array|object
     */
    private function http($method, $host, $path, array $parameters, $json)
    {
        $this->resetLastResponse();
        $this->resetAttemptsNumber();
        $url = sprintf('%s/%s/%s.json', $host, self::API_VERSION, $path);
        $this->response->setApiPath($path);
        if (!$json) {
            $parameters = $this->cleanUpParameters($parameters);
        }
        return $this->makeRequests($url, $method, $parameters, $json);
    }

    /**
     *
     * Make requests and retry them (if enabled) in case of Twitter's problems.
     *
     * @param string $method
     * @param string $url
     * @param string $method
     * @param array  $parameters
     * @param bool   $json
     *
     * @return array|object
     */
    private function makeRequests($url, $method, array $parameters, $json)
    {
        do {
            $this->sleepIfNeeded();
            $result = $this->oAuthRequest($url, $method, $parameters, $json);
            $response = JsonDecoder::decode($result, $this->decodeJsonAsArray);
            $this->response->setBody($response);
            $this->attempts++;
            // Retry up to our $maxRetries number if we get errors greater than 500 (over capacity etc)
        } while ($this->requestsAvailable());

        return $response;
    }

    /**
     * Checks if we have to retry request if API is down.
     *
     * @return bool
     */
    private function requestsAvailable()
    {
        return ($this->maxRetries && ($this->attempts <= $this->maxRetries) && $this->getLastHttpCode() >= 500);
    }

    /**
     * Format and sign an OAuth / API request
     *
     * @param string $url
     * @param string $method
     * @param array  $parameters
     * @param bool   $json
     *
     * @return string
     * @throws TwitterOAuthException
     */
    private function oAuthRequest($url, $method, array $parameters, $json = false)
    {
        $request = Request::fromConsumerAndToken($this->consumer, $this->token, $method, $url, $parameters, $json);
        if (array_key_exists('oauth_callback', $parameters)) {
            // Twitter doesn't like oauth_callback as a parameter.
            unset($parameters['oauth_callback']);
        }
        if ($this->bearer === null) {
            $request->signRequest($this->signatureMethod, $this->consumer, $this->token);
            $authorization = $request->toHeader();
            if (array_key_exists('oauth_verifier', $parameters)) {
                // Twitter doesn't always work with oauth in the body and in the header
                // and it's already included in the $authorization header
                unset($parameters['oauth_verifier']);
            }
        } else {
            $authorization = 'Authorization: Bearer ' . $this->bearer;
        }
        return $this->request($request->getNormalizedHttpUrl(), $method, $authorization, $parameters, $json);
    }

    /**
     * Set Curl options.
     *
     * @return array
     */
    private function curlOptions()
    {
        $options = [
            // CURLOPT_VERBOSE => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectionTimeout,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => $this->userAgent,
        ];

        if ($this->useCAFile()) {
            $options[CURLOPT_CAINFO] = __DIR__ . DIRECTORY_SEPARATOR . 'cacert.pem';
        }

        if ($this->gzipEncoding) {
            $options[CURLOPT_ENCODING] = 'gzip';
        }

        if (!empty($this->proxy)) {
            $options[CURLOPT_PROXY] = $this->proxy['CURLOPT_PROXY'];
            $options[CURLOPT_PROXYUSERPWD] = $this->proxy['CURLOPT_PROXYUSERPWD'];
            $options[CURLOPT_PROXYPORT] = $this->proxy['CURLOPT_PROXYPORT'];
            $options[CURLOPT_PROXYAUTH] = CURLAUTH_BASIC;
            $options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
        }

        return $options;
    }

    /**
     * Make an HTTP request
     *
     * @param string $url
     * @param string $method
     * @param string $authorization
     * @param array $postfields
     * @param bool $json
     *
     * @return string
     * @throws TwitterOAuthException
     */
    private function request($url, $method, $authorization, array $postfields, $json = false)
    {
        $options = $this->curlOptions();
        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_HTTPHEADER] = ['Accept: application/json', $authorization, 'Expect:'];

        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                $options[CURLOPT_POST] = true;
                if ($json) {
                    $options[CURLOPT_HTTPHEADER][] = 'Content-type: application/json';
                    $options[CURLOPT_POSTFIELDS] = json_encode($postfields);
                } else {
                    $options[CURLOPT_POSTFIELDS] = Util::buildHttpQuery($postfields);
                }
                break;
            case 'DELETE':
                $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
            case 'PUT':
                $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                break;
        }

        if (in_array($method, ['GET', 'PUT', 'DELETE']) && !empty($postfields)) {
            $options[CURLOPT_URL] .= '?' . Util::buildHttpQuery($postfields);
        }


        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, $options);
        $response = curl_exec($curlHandle);

        // Throw exceptions on cURL errors.
        if (curl_errno($curlHandle) > 0) {
            throw new TwitterOAuthException(curl_error($curlHandle), curl_errno($curlHandle));
        }

        $this->response->setHttpCode(curl_getinfo($curlHandle, CURLINFO_HTTP_CODE));
        $parts = explode("\r\n\r\n", $response);
        $responseBody = array_pop($parts);
        $responseHeader = array_pop($parts);
        $this->response->setHeaders($this->parseHeaders($responseHeader));

        curl_close($curlHandle);

        return $responseBody;
    }

    /**
     * Get the header info to store.
     *
     * @param string $header
     *
     * @return array
     */
    private function parseHeaders($header)
    {
        $headers = [];
        foreach (explode("\r\n", $header) as $line) {
            if (strpos($line, ':') !== false) {
                list ($key, $value) = explode(': ', $line);
                $key = str_replace('-', '_', strtolower($key));
                $headers[$key] = trim($value);
            }
        }
        return $headers;
    }

    /**
     * Encode application authorization header with base64.
     *
     * @param Consumer $consumer
     *
     * @return string
     */
    private function encodeAppAuthorization(Consumer $consumer)
    {
        $key = rawurlencode($consumer->key);
        $secret = rawurlencode($consumer->secret);
        return base64_encode($key . ':' . $secret);
    }

    /**
     * Is the code running from a Phar module.
     *
     * @return boolean
     */
    private function pharRunning()
    {
        return class_exists('Phar') && \Phar::running(false) !== '';
    }

    /**
     * Use included CA file instead of OS provided list.
     *
     * @return boolean
     */
    private function useCAFile()
    {
        /* Use CACert file when not in a PHAR file. */
        return !$this->pharRunning();
    }
}
