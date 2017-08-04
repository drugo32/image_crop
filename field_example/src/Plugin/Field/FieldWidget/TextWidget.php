<?php

namespace Drupal\field_example\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;

/**
 * Plugin implementation of the 'field_example_text' widget.
 *
 * @FieldWidget(
 *   id = "field_example_text",
 *   module = "field_example",
 *   label = @Translation("RGB value as #ffffff"),
 *   field_types = {
 *     "field_example_rgb"
 *   }
 * )
 */
class TextWidget extends ImageWidget {

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
  public static function defaultSettings() {
    return [
      'progress_indicator' => 'throbber',
      'preview_image_style' => 'thumbnail',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $entity = $items->getEntity();
    $field_settings = $this->getFieldSettings();

    // $element['#nid'] = $entity->id();
    $element['#resolution'] = $field_settings['resolution'];
    $element['#croparea'] = $field_settings['croparea'];
    $element['#enforce_minimum'] = $field_settings['enforce_minimum'];
    $element['#enforce_ratio'] = $field_settings['enforce_ratio'];
    $element['#element_validate'] = array(array($this, 'validate'));

    return $element;
  }

  public static function value($element, $input, FormStateInterface $form_state) {
    $return = parent::value($element, $input, $form_state);
    return $return;
  }

  /**
   * Validate the color text field.
   */
  public function validate($element, FormStateInterface $form_state) {
      $defaults = array(
        'x'       => 0,
        'y'       => 0,
        'width'   => 50,
        'height'  => 50,
      );
      foreach($defaults as $name => $val) {
        $element["#value"][$name] = (int) $element["#value"][$name];
      }
      $form_state->setValueForElement($element, $element["#value"]);
  }


  /**
   * Form API callback: Processes a image_image field element.
   *
   * Expands the image_image type to include the alt and title fields.
   *
   * This method is assigned as a #process callback in formElement() method.
   */
  public static function process($element, FormStateInterface $form_state, $form) {

      $element = parent::process($element, $form_state, $form);
      $item = $element['#value'];
      $item['fids'] = $element['fids']['#value'];
      unset($element['preview']);
      if (!empty($element['#files'])) {
        $file = reset($element['#files']);
        if(!empty($file)) {

          $image = \Drupal::service('image.factory')->get($file->getFileUri());
          if ($image->isValid()) {
          list($width, $height) = explode('x', $element["#resolution"]);
          list($res_w, $res_h) = explode('x',  $element['#resolution']);
          list($crop_w, $crop_h) = explode('x', $element['#croparea']);
          $force_ratio = $element['#enforce_ratio'];
          $force_minimum =  $element['#enforce_minimum'];

          $settings = array(
            'test' => array(
              'preview' => array(
                'orig_width' => $image->getWidth(),
                'orig_height' => $image->getHeight(),
                'width' => (integer)$width,
                'height' => (integer)$height,
              ),
              'box' => array(
                'ratio' => $res_h ? $force_ratio * $res_w/$res_h : 0,
                'box_width' => $crop_w,
                'box_height' => $crop_h,
              ),
              'minimum' => array(
                'width'   => $force_ratio ? $res_w : NULL,
                'height'  => $force_minimum ? $res_h : NULL,
              ),
            ),
          );

          $element['#attached'] = array(
            'library' => array(
              'field_example/cropimage',
            ),
            'drupalSettings' => array('imagecrop_field' => $settings),
          );

          $element['imagecrop'] = [
            '#type' => 'item',
            '#theme' => 'field_example_widget',
            '#weight' => -10,

          ];

          $element['imagecrop']['#cropbox'] = [
            '#theme' => 'image',
            '#width' => $image->getWidth(),
            '#height' => $image->getHeight(),
            // '#style_name' => $variables['style_name'],
            // '#element_validate' => array(get_called_class(), 'validate'),
            '#attributes' => array(
              'class' => 'cropbox',
              'id' => 'test-cropbox',
            ),
            '#uri' => $file->getFileUri(),
          ];

          $element['imagecrop']['#preview'] = [
          // '#type' => 'item',
          '#theme' => 'field_example_preview',
          '#width' => $width,
          '#height' => $height,
          // '#style_name' => $variables['style_name'],
          '#uri' => $file->getFileUri(),
          '#attributes' => [
            'class' => array('preview-existing', 'jcrop-preview'),
            /*'style' => 'display:none'*/
            ],
          ];

          $defaults = array(
          'x'       => 0,
          'y'       => 0,
          'width'   => 50,
          'height'  => 50,
          'changed' => 0,
          );
          /*Override value image field*/
          foreach ($defaults as $name => $default) {
            $element[$name] = array(
              '#type' => 'hidden',
              //' #title' => $name,
              '#attributes' => array('class' => array('edit-image-crop-' . $name)),
              '#default_value' => $default,
            );
          }

        }
      }
    }

    return $element;
  }

/*
  public function delete() {
    parent::delete();
  }
*/

}
