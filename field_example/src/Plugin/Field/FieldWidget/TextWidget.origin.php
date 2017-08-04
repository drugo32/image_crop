<?php

namespace Drupal\field_example\Plugin\Field\FieldWidget;

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

      $field_settings = $this->getFieldSettings();
      // print_r($field_settings);
      //print_r($field_settings);
  // Add upload resolution validation.
  if ($field_settings['max_resolution'] || $field_settings['min_resolution']) {
    $element['#upload_validators']['file_validate_image_resolution'] = [$field_settings['max_resolution'], $field_settings['min_resolution']];
  }

  // If not using custom extension validation, ensure this is an image.
  $supported_extensions = ['png', 'gif', 'jpg', 'jpeg'];
  $extensions = isset($element['#upload_validators']['file_validate_extensions'][0]) ? $element['#upload_validators']['file_validate_extensions'][0] : implode(' ', $supported_extensions);
  $extensions = array_intersect(explode(' ', $extensions), $supported_extensions);
  $element['#upload_validators']['file_validate_extensions'][0] = implode(' ', $extensions);

  // Add mobile device image capture acceptance.
  $element['#accept'] = 'image/*';

  // Add properties needed by process() method.
  $element['#preview_image_style'] = $this->getSetting('preview_image_style');
  $element['#title_field'] = $field_settings['title_field'];
  $element['#title_field_required'] = $field_settings['title_field_required'];
  $element['#alt_field'] = $field_settings['alt_field'];
  $element['#alt_field_required'] = $field_settings['alt_field_required'];

  $element['#resolution'] = $field_settings['resolution'];
  $element['#croparea'] = $field_settings['croparea'];


  // Default image.
  $default_image = $field_settings['default_image'];
  if (empty($default_image['uuid'])) {
    $default_image = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('default_image');
  }
  // Convert the stored UUID into a file ID.
  if (!empty($default_image['uuid']) && $entity = \Drupal::entityManager()->loadEntityByUuid('file', $default_image['uuid'])) {
    $default_image['fid'] = $entity->id();
  }
  $element['#default_image'] = !empty($default_image['fid']) ? $default_image : [];

  return $element;
}

  /**
   * Validate the color text field.
   */
  public function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];
    if (strlen($value) == 0) {
      $form_state->setValueForElement($element, '');
      return;
    }
    if (!preg_match('/^#([a-f0-9]{6})$/iD', strtolower($value))) {
      $form_state->setError($element, t("Color must be a 6-digit hexadecimal value, suitable for CSS."));
    }
  }


  /**
   * Form API callback: Processes a image_image field element.
   *
   * Expands the image_image type to include the alt and title fields.
   *
   * This method is assigned as a #process callback in formElement() method.
   */
  public static function process($element, FormStateInterface $form_state, $form) {
      $item = $element['#value'];
      $item['fids'] = $element['fids']['#value'];

      // $element['#theme'] = 'image_widget';
/*
      // Add the image preview.
      if (!empty($element['#files']) && $element['#preview_image_style']) {
        $file = reset($element['#files']);
        $variables = [
          'style_name' => $element['#preview_image_style'],
          'uri' => $file->getFileUri(),
        ];

        // Determine image dimensions.
        if (isset($element['#value']['width']) && isset($element['#value']['height'])) {
          $variables['width'] = $element['#value']['width'];
          $variables['height'] = $element['#value']['height'];
        }
        else {
          $image = \Drupal::service('image.factory')->get($file->getFileUri());
          if ($image->isValid()) {
            $variables['width'] = $image->getWidth();
            $variables['height'] = $image->getHeight();

          }
          else {
            $variables['width'] = $variables['height'] = NULL;
          }
        }
*/

if (!empty($element['#files'])) {
  $file = reset($element['#files']);
        /*
        $element['preview'] = [
          '#weight' => -10,
          '#theme' => 'image_style',
          '#width' => $variables['width'],
          '#height' => $variables['height'],
          '#style_name' => $variables['style_name'],
          '#uri' => $variables['uri'],
        ];
        */
        if(!empty($file)) {

        $image = \Drupal::service('image.factory')->get($file->getFileUri());
        if ($image->isValid()) {
        list($width, $height) = explode('x', $element["#resolution"]);
        $settings = array(
            'test' => array(
              'preview' => array(
                'orig_width' => $image->getWidth(),
                'orig_height' => $image->getHeight(),
                'width' => (integer)$width,
                'height' => (integer)$height,
              ),
            ),
          );

        $element['imagecrop'] = [
          '#type' => 'item',
          '#theme' => 'field_example_widget',
          '#weight' => -10,
          '#attached' => array(
            'library' => array(
              'field_example/cropimage',
            ),
            'drupalSettings' => array('imagecrop_field' => $settings),
          ),
        ];

        $element['imagecrop']['#cropbox'] = [
            '#theme' => 'image',
            '#width' => $image->getWidth(),
            '#height' => $image->getHeight(),
            // '#style_name' => $variables['style_name'],
            '#attributes' => array(
              'id' => 'jcrop_target',
            ),
            '#uri' => $file->getFileUri(),
          ];


          list($width, $height) = explode('x', $element["#resolution"]);
          $element['imagecrop']['#preview'] = [
            // '#type' => 'item',
            '#theme' => 'field_example_preview',
            '#width' => $width,
            '#height' => $height,
            // '#style_name' => $variables['style_name'],
            '#uri' => $file->getFileUri(),
            '#attributes' => [
              'class' => 'jcrop-preview',
              /*'style' => 'display:none'*/
              ],
            ];

          }
        }
      }
/*
        // Store the dimensions in the form so the file doesn't have to be
        // accessed again. This is important for remote files.
        $element['width'] = [
          '#type' => 'hidden',
          '#value' => $variables['width'],
        ];
        $element['height'] = [
          '#type' => 'hidden',
          '#value' => $variables['height'],
        ];
      }
      elseif (!empty($element['#default_image'])) {
        $default_image = $element['#default_image'];
        $file = File::load($default_image['fid']);
        if (!empty($file)) {
          $element['preview'] = [
            '#weight' => -10,
            '#theme' => 'image_style',
            '#width' => $default_image['width'],
            '#height' => $default_image['height'],
            '#style_name' => $element['#preview_image_style'],
            '#uri' => $file->getFileUri(),
          ];
        }
      }
*/
      /*
      // Add the additional alt and title fields.
      $element['alt'] = [
        '#title' => t('Alternative text'),
        '#type' => 'textfield',
        '#default_value' => isset($item['alt']) ? $item['alt'] : '',
        '#description' => t('This text will be used by screen readers, search engines, or when the image cannot be loaded.'),
        // @see https://www.drupal.org/node/465106#alt-text
        '#maxlength' => 512,
        '#weight' => -12,
        '#access' => (bool) $item['fids'] && $element['#alt_field'],
        '#required' => $element['#alt_field_required'],
        '#element_validate' => $element['#alt_field_required'] == 1 ? [[get_called_class(), 'validateRequiredFields']] : [],
      ];
      $element['title'] = [
        '#type' => 'textfield',
        '#title' => t('Title'),
        '#default_value' => isset($item['title']) ? $item['title'] : '',
        '#description' => t('The title is used as a tool tip when the user hovers the mouse over the image.'),
        '#maxlength' => 1024,
        '#weight' => -11,
        '#access' => (bool) $item['fids'] && $element['#title_field'],
        '#required' => $element['#title_field_required'],
        '#element_validate' => $element['#title_field_required'] == 1 ? [[get_called_class(), 'validateRequiredFields']] : [],
      ];
      */
      return parent::process($element, $form_state, $form);
  }


}
