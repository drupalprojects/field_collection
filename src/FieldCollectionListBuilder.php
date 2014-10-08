<?php

/**
 * Contains \Drupal\node\FieldCollectionListController.
 */

namespace Drupal\field_collection;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of field collections.
 */
class FieldCollectionListBuilder extends ConfigEntityListBuilder {
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

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    return array();
  }
}
