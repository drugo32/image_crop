<?php
/**
 * @file
 * Contains ImageFieldCropServiceInterface.php.
 */
namespace Drupal\imagefield_crop;
use Drupal\field\FieldStorageConfigInterface;
interface ImageFieldCropServiceInterface {
  /**
   * Obtain the list of field permissions.
   *
   * @param string $field_label
   *   The human readable name of the field to use when constructing permission
   *   names. Usually this will be derived from one or more of the field
   *   instance labels.
   */
  public static function getList($field_label = '');


  //_imagefield_crop_items_save_settings($item)
  public function ImageFieldItemSaveInfo($item) {

  }

  // _imagefield_crop_items_remove_settings
  public function ImageFieldItemRemoveInfo($item_id) {

  }

  //_imagefield_crop_check_widget
  public function EntityHasFiledImageCropWidget() {

  }

  // _imagefield_crop_iteme_save($entity)
  public static function SaveEntityWithFieldImageCrop($entity) {

  }

  // _imagefield_crop_iteme_remove
  public function CheckRemove($entity) {

  }

  public static function getFileToCrop($fid) {

  }


  // _imagefield_crop_clone_file($entity_file,  $orig_uri)
  public function CloneFile($entity_file,  $orig_uri) {

  }
  // _imagefield_crop_clone_crop($item, $orig_uri)
  public function CropFile($entity_file,  $orig_uri) {

  }


}
