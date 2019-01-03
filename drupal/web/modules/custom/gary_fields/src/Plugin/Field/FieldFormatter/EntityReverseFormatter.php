<?php

namespace Drupal\gary_fields\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'entity_reverse_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reverse_formatter",
 *   label = @Translation("Link to Entity"),
 *   field_types = {
 *     "Random"
 *   }
 * )
 */
class EntityReverseFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the linked label of the entity');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = ['#markup' => $item->value];
    }

    return $element;
  }

}
