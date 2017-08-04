<?php

namespace Drupal\field_example\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_example_simple_text' formatter.
 *
 * @FieldFormatter(
 *   id = "field_example_simple_text",
 *   module = "field_example",
 *   label = @Translation("Simple text-based formatter"),
 *   field_types = {
 *     "field_example_rgb"
 *   }
 * )
 */
class SimpleTextFormatter extends FormatterBase {

  private function _imagefield_crop_file_to_crop($fid) {
    $query = \Drupal::database()->select('file_usage', 'fu');
    $query->fields('fu', ['fid']);
    $query->condition('fu.id', $fid);
    $query->condition('fu.module', 'imagefield_crop');
    $query->range(0, 1);
    $result = $query->execute()->fetchAssoc();
    if (!empty($result) && isset($result['fid'])) {
      $fid = $result['fid'];
    }
    return file_load($fid);
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    foreach ($items as $delta => $item) {
      if (is_numeric($item->target_id)) {
      //  return $element;
        $filecrop = $this->_imagefield_crop_file_to_crop($item->target_id);
        //  $image = \Drupal::service('image.factory')->get($file->getFileUri());
        // settare alt e title.
        $image = [
        '#theme' => 'image',
        '#width' =>  $item->width,
        '#height' => $item->height,
        '#uri' => $filecrop->getFileUri(),
        '#attributes' => [
          'alt' => 'jcrop-preview',
           'class' => array('preview-existing', 'jcrop-preview'),
           // 'style' => 'display:none',
         ],
        ];
        $elements[$delta] = $image;
      }
    }
    return $elements;
  }

}
