<?php

namespace Drupal\okta_saml_login\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class SigninController extends ControllerBase {

  protected $config;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $requestStack;

  /**
   * SigninController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   An instance of ConfigFactory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(ConfigFactory $config,
                              RequestStack $request_stack) {
    $this->config = $config->get('okta_api.settings');
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function signin() {
    return [
      '#theme' => 'okta_saml_login_signin_widget',
      '#title' => 'Sign in',
      '#vars' => [],
      '#attached' => [
        'library' => [
          'okta_saml_login/okta_saml_login.okta',
        ],
        'drupalSettings' => [
          'okta_saml_login' => [
            'okta_org_url' => $this->getOktaDomain(),
            'redirect_url' => $this->getRedirectUrl(),
          ],
        ],
      ],
    ];

  }

  /**
   * @return string
   */
  private function getOktaDomain() {
    $oktaApiDomain = $this->config->get('okta_domain');
    $oktaPreviewDomain = $this->config->get('preview_domain');

    if ($oktaPreviewDomain == TRUE) {
      $oktaApiDomain = 'oktapreview.com';
    }

    return 'https://' . $this->config->get('organisation_url') . '.' . $oktaApiDomain;
  }

  /**
   * @return \Drupal\Core\GeneratedUrl|string
   */
  private function getRedirectUrl() {
    $request = $this->requestStack->getCurrentRequest();

    $returnToUrl = '/';

    // See if a URL has been explicitly provided in ReturnTo.
    if ($request->query->get('ReturnTo')) {
      $returnToUrl = $request->query->get('ReturnTo');
    }

    $returnTo = Url::fromUserInput($returnToUrl, ['absolute' => TRUE])->toString();

    $redirectUrl = Url::fromRoute(
      'simplesamlphp_auth.saml_login',
      [],
      [
        'absolute' => TRUE,
        'query' => ['ReturnTo' => $returnTo],
      ]
    )->toString();

    return $redirectUrl;
  }

}
