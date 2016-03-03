<?php

/**
 * @file
 * Contains \Drupal\field_collection\FieldCollectionItemAccessControlHandler
 */

namespace Drupal\field_collection;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

class FieldCollectionItemAccessControlHandler extends EntityAccessControlHandler {

  /**
   * Performs access checks.
   *
   * Uses permissions from host entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to check 'create' access.
   * @param string $operation
   *   The entity operation. Usually one of 'view', 'update', 'create' or
   *   'delete'.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $result = parent::checkAccess($entity, $operation, $account);
    if ($result->isForbidden()) {
      return $result;
    }
    /** @var \Drupal\Core\Entity\ContentEntityInterface $host */
    if ($host = $entity->getHost()) {
      return $host->access($operation, $account, TRUE);
    }
    else {
      // @todo Log error when no host found.
      return AccessResult::forbidden();
    }
  }

}
