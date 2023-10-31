<?php

namespace Drupal\auth0\Util;

/**
 * @file
 * Contains \Drupal\auth0\Util\AuthHelper.
 */
use Auth0\SDK\Auth0;
use Auth0\SDK\Exception\InvalidTokenException;
///use Auth0\SDK\API\Authentication;
///use Auth0\SDK\API\Helpers\ApiClient;
///use Auth0\SDK\API\Helpers\InformationHeaders;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Token;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;

/**
 * Controller routines for auth0 authentication.
 */
class AuthHelper {

  const AUTH0_LOGGER = 'auth0_helper';
  const AUTH0_DOMAIN = 'auth0_domain';
  const AUTH0_CUSTOM_DOMAIN = 'auth0_custom_domain';
  const AUTH0_CLIENT_ID = 'auth0_client_id';
  const AUTH0_CLIENT_SECRET = 'auth0_client_secret';
  const AUTH0_REDIRECT_FOR_SSO = 'auth0_redirect_for_sso';
  const AUTH0_JWT_SIGNING_ALGORITHM = 'auth0_jwt_signature_alg';
  const AUTH0_SECRET_ENCODED = 'auth0_secret_base64_encoded';
  const AUTH0_OFFLINE_ACCESS = 'auth0_allow_offline_access';

  private $logger;
  private $config;
  private $domain;
  private $customDomain;
  private $clientId;
  private $clientSecret;
  private $redirectForSso;
  private $auth0JwtSignatureAlg;
  private $secretBase64Encoded;
  private $auth0;
  private $httpClient;

  /**
   * Initialize the Helper.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
      LoggerChannelFactoryInterface $logger_factory,
      ConfigFactoryInterface $config_factory
  ) {
    global $base_root;

    $this->logger = $logger_factory->get(AuthHelper::AUTH0_LOGGER);
    $this->config = $config_factory->get('auth0.settings');
    $this->domain = $this->config->get(AuthHelper::AUTH0_DOMAIN);
    $this->customDomain = $this->config->get(AuthHelper::AUTH0_CUSTOM_DOMAIN);
    $this->clientId = $this->config->get(AuthHelper::AUTH0_CLIENT_ID);
    $this->clientSecret = $this->config->get(AuthHelper::AUTH0_CLIENT_SECRET);
    $this->redirectForSso = $this->config->get(AuthHelper::AUTH0_REDIRECT_FOR_SSO);
    $this->auth0JwtSignatureAlg = $this->config->get(
        AuthHelper::AUTH0_JWT_SIGNING_ALGORITHM, AUTH0_DEFAULT_SIGNING_ALGORITHM
    );
    $this->secretBase64Encoded = FALSE || $this->config->get(AuthHelper::AUTH0_SECRET_ENCODED);

    $guzzleClient = \Drupal::httpClient();

    $this->httpClient = new GuzzleAdapter($guzzleClient);

    $sdk_configuration = new SdkConfiguration(
        domain: $this->getAuthDomain(), clientId: $this->clientId,
        clientSecret: $this->clientSecret,
        redirectUri: "$base_root/auth0/callback", httpClient: $this->httpClient,
        cookieSecret: $this->getCookieSecret(),
        usePkce: FALSE,
        /*
          // Specify a PSR-18 HTTP client factory:
          httpClient: $httpClient

          // Specify PSR-17 request/response factories:
          httpRequestFactory: $httpFactory
          httpResponseFactory: $httpFactory
          httpStreamFactory: $httpFactory */
    );

    $this->auth0 = new Auth0($sdk_configuration);
  }

  public function getCookieSecret() {
    $cookieSecret = $this->config->get('auth0_cookie_secret');

    if (empty($cookieSecret)) {
      $cookieSecret = uniqid();
      $config = \Drupal::configFactory()->getEditable('auth0.settings');
      $config->set('auth0_cookie_secret', $cookieSecret);
      $config->save();
    }

    return $cookieSecret;
  }

  /**
   * Get the user using token.
   *
   * @param string $refreshToken
   *   The refresh token to use to get the user.
   *
   * @return array
   *   A user array of named claims from the ID token.
   *
   * @throws RefreshTokenFailedException
   * @throws CoreException
   * @throws InvalidTokenException
   */
  public function getUserUsingRefreshToken($refreshToken) {
    global $base_root;

    $auth0Api = $this->auth0->authentication();
    try {
      $tokens = $auth0Api->oauthToken([
        'grantType'    => 'refresh_token',
        'clientId'     => $this->clientId,
        'clientSecret' => $this->clientSecret,
        'refreshToken' => $refreshToken,
      ]);

      return $this->validateIdToken($tokens->idToken);
    }
    catch (\Exception $e) {
      throw new RefreshTokenFailedException($e);
    }
  }

  /**
   * Validate the ID token.
   *
   * @param string $idToken
   *   The ID token to validate.
   *
   * @return array
   *   A user array of named claims from the ID token.
   *
   * @throws InvalidTokenException
   * @throws \Exception
   */
  public function validateIdToken(string $idToken): array {
    $decoded = $this->auth0->decode($idToken);

    if ($decoded) {
      return $decoded->toArray();
    }
  }

  /**
   * Return the custom domain, if one has been set.
   *
   * @return mixed
   *   A string with the domain name
   *   A empty string if the config is not set
   */
  public function getAuthDomain() {
    return !empty($this->customDomain) ? $this->customDomain : $this->domain;
  }

  /**
   * Get the tenant CDN base URL based on the Application domain.
   *
   * @param string $domain
   *   Tenant domain.
   *
   * @return string
   *   Tenant CDN base URL
   */
  public static function getTenantCdn($domain) {
    preg_match('/^[\w\d\-_0-9]+\.([\w\d\-_0-9]*)[\.]*auth0\.com$/', $domain,
        $matches);
    return 'https://cdn' .
        (empty($matches[1]) || $matches[1] == 'us' ? '' : '.' . $matches[1])
        . '.auth0.com';
  }
}
