<?php

namespace Drupal\indra_rest\Plugin\rest\resource;

use Drupal\user\Entity\User;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "indra_get_users_rest_resource",
 *   label = @Translation("Get user indra rest"),
 *   uri_paths = {
 *     "canonical" = "/api/indra/users",
 *     "https://www.drupal.org/link-relations/create" = "/api/indra/users"
 *   }
 * )
 */
class GetUsersRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->logger = $container->get('logger.factory')->get('indra_rest');
    $instance->currentUser = $container->get('current_user');

    return $instance;
  }

  /**
   * Responds to GET requests.
   *
   * @param string $id
   *   User ID.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   */
  public function get($id = NULL) {
    if ($id == NULL) {
      $accounts = User::loadMultiple();
    }
    else {
      $accounts = User::loadMultiple([$id]);
    }

    $this->validateAccount($accounts);

    $build = [
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    $response = new ResourceResponse(
      [
        'account' => [$accounts],
      ],
      200
    );

    return $response->addCacheableDependency($build);
  }

  /**
   * Validate that the user exists.
   *
   * @param mixed $accounts
   *   The users account.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   If validation errors are found.
   */
  protected function validateAccount($accounts) {
    if (empty($accounts)) {
      throw new BadRequestHttpException('user not found.');
    }
  }

}
