<?php

namespace Drupal\indra_rest\Plugin\rest\resource;

use Drupal\user\UserInterface;
use Drupal\rest\ResourceResponse;
use Drupal\user\Plugin\rest\resource\UserRegistrationResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "indra_register_user_rest_resource",
 *   label = @Translation("Register user indra rest"),
 *   serialization_class = "Drupal\user\Entity\User",
 *   uri_paths = {
 *     "canonical" = "/api/indra/user",
 *     "https://www.drupal.org/link-relations/create" = "/api/indra/user"
 *   }
 * )
 */
class RegisterUserRestResource extends UserRegistrationResource {

  /**
   * Responds to user registration POST request.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account entity.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   */
  public function post(UserInterface $account = NULL) {
    $this->ensureAccountCanRegister($account);
    $account->activate();
    $this->checkEditFieldAccess($account);
    $this->validate($account);
    $this->validatePass($account);
    $this->validateDate($account);
    $account->save();

    return new ResourceResponse(
      [
        'message' => 'Registered user',
        'account' => $account,
      ],
      200
    );
  }

  /**
   * Ensure the account can be registered in this request.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account to register.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   If validation errors are found.
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   If validation errors are found.
   */
  protected function ensureAccountCanRegister(UserInterface $account = NULL) {
    if ($account === NULL) {
      throw new BadRequestHttpException('No user account data for registration received.');
    }

    if (!$this->currentUser->isAnonymous()) {
      throw new AccessDeniedHttpException('Only anonymous users can register a user.');
    }

    if ($this->userSettings->get('register') == UserInterface::REGISTER_ADMINISTRATORS_ONLY) {
      throw new AccessDeniedHttpException('You cannot register a new user account.');
    }
  }

  /**
   * Verifies that an entity does not violate any validation constraints.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account to register.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
   *   If validation errors are found.
   */
  protected function validatePass(UserInterface $account = NULL) {
    if (empty($account->getPassword())) {
      throw new UnprocessableEntityHttpException('No password provided.');
    }
  }

  /**
   * Verifies that an entity does not violate any validation constraints.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account to register.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
   *   If validation errors are found.
   */
  protected function validateDate(UserInterface $account = NULL) {
    $date = $account->get('field_date')->getValue();

    $date = \DateTime::createFromFormat('Y-m-d', $date[0]['value']);
    $date = $date->modify('+18 year');
    $now = new \DateTime('now');

    if ($date > $now) {
      throw new UnprocessableEntityHttpException('The user cannot be registered because he is not of legal age.');
    }
  }

}
