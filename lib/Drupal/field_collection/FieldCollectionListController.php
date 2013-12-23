<?php

/**
 * Contains \Drupal\node\FieldCollectionListController.
 */

namespace Drupal\field_collection;

use Drupal\Core\Config\Entity\ConfigEntityListController;
use Drupal\Core\Entity\EntityControllerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of field collections.
 */
class FieldCollectionListController extends ConfigEntityListController implements EntityControllerInterface {

  /**
   * Overrides Drupal\Core\Entity\EntityListController::load().
   */
  public function load() {
    $entities = parent::load();

    // Sort the entities using the entity class's sort() method.
    // See \Drupal\Core\Config\Entity\ConfigEntityBase::sort().
    uasort($entities, array($this->entityInfo['class'], 'sort'));
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['title'] = array(
      'data' => $this->getLabel($entity),
      'class' => array('menu-label'),
    );
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = array('title' => $this->t('Machine name'),);
    return $header + parent::buildHeader();
  }
}
