<?php
/**
 * @file
 * Contains ImageFieldCropService.php.
 */

namespace Drupal\imagefield_crop;
use Drupal\field\FieldStorageConfigInterface;
class ImageFieldCropService implements ImageFieldCropServiceInterface {
  /**
   * Obtain the list of field permissions.
   *
   * @param string $field_label
   *   The human readable name of the field to use when constructing permission
   *   names. Usually this will be derived from one or more of the field
   *   instance labels.
   */
  public static function getList($field_label = '') {
    return array(
      'create' => array(
        'label' => t('Create field'),
        'title' => t('Create own value for field @field', array('@field' => $field_label)),
      ),
      'edit own' => array(
        'label' => t('Edit own field'),
        'title' => t('Edit own value for field @field', array('@field' => $field_label)),
      ),
      'edit' => array(
        'label' => t('Edit field'),
        'title' => t("Edit anyone's value for field @field", array('@field' => $field_label)),
      ),
      'view own' => array(
        'label' => t('View own field'),
        'title' => t('View own value for field @field', array('@field' => $field_label)),
      ),
      'view' => array(
        'label' => t('View field'),
        'title' => t("View anyone's value for field @field", array('@field' => $field_label)),
      ),
    );
  }

}
