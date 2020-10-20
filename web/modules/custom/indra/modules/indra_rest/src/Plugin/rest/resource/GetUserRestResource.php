<?php

namespace Drupal\indra_rest\Plugin\rest\resource;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "indra_get_user_rest_resource",
 *   label = @Translation("Get user by id indra rest"),
 *   uri_paths = {
 *     "canonical" = "/api/indra/users/{id}",
 *     "https://www.drupal.org/link-relations/create" = "/api/indra/users/{id}"
 *   }
 * )
 */
class GetUserRestResource extends GetUsersRestResource {

}
