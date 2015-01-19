<?php

/**
 * @file
 * Contains \Drupal\field_collection\Access\FieldCollectionItemAddToHostCheck.
 */

namespace Drupal\field_collection\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Determines access to update the new field collection item's future host.
 */
class FieldCollectionItemAddToHostCheck implements AccessInterface {

  /**
   * Checks access to update the field collection item's future host.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   *
   * TODO: Document params
   *
   * @return string
   *   A \Drupal\Core\Access\AccessInterface constant value.
   */
  public function access(AccountInterface $account,
                         $host_type = NULL,
                         $host_id = NULL)
  {
    $host = entity_load($host_type, $host_id);

    return AccessResult::allowedIf($host && $host->access('update', $account))
      ->cachePerRole();
  }


}
