<?php

namespace Drupal\imagefield_crop\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
/**
 * Plugin implementation of the 'image_crop' formatter.
 *
 * @FieldFormatter(
 *   id = "image_crop",
 *   module = "imagefield_crop",
 *   label = @Translation("Crop the image"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
 class  ImageCropFormatter extends ImageFormatter {
   /**
    * {@inheritdoc}
    */
    /**
     * {@inheritdoc}
     */
     // EntityReferenceFieldItemListInterface
    protected function getEntitiesToView($items, $langcode) {
      // Add Image crop if isset.
      // dpm($items->getValue());
      if (!$items->isEmpty()) {
        $items = clone $items;
        $overide_items = [];
        foreach ($items->getValue() as $key => $item) {
          $file = _imagefield_crop_file_to_crop($items->getValue()[$key]['target_id']);
          if($item['target_id'] != $file->id()) {
            $image = \Drupal::service('image.factory')->get($file->getFileUri());
            $overide_items[] = array(
              'target_id' => $file->id(),
              'alt' => $item['alt'],
              'title' => $item['title'],
              'width' => $image->getWidth(),
              'height' => $image->getHeight(),
              'entity' => $file,
              '_loaded' => TRUE,
              // '_is_default' => TRUE,
            );
            $file->_referringItem = $items[$key];
          }
          else {
            $overide_items[] = $item;
          }
      }
      $items->setValue($overide_items);
      return parent::getEntitiesToView($items, $langcode);
    }
  }


}
