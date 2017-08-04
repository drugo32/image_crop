<?php

namespace Drupal\field_example\Plugin\Field\FieldType;

use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldType\FileItem;

/**

 * Plugin implementation of the 'field_example_rgb' field type.
 *
 * @FieldType(
 *   id = "imagecrop",
 *   label = @Translation("Example Color RGB"),
 *   module = "field_example",
 *   category = @Translation("Reference"),
 *   description = @Translation("Demonstrates a field composed of an RGB color."),
 *   default_widget = "field_example_text",
 *   default_formatter = "field_example_simple_text",
 *   column_groups = {
 *     "file" = {
 *       "label" = @Translation("File"),
 *       "columns" = {
 *         "target_id", "width", "height"
 *       },
 *       "require_all_groups_for_translation" = TRUE
 *     },
 *     "alt" = {
 *       "label" = @Translation("Alt"),
 *       "translatable" = TRUE
 *     },
 *     "title" = {
 *       "label" = @Translation("Title"),
 *       "translatable" = TRUE
 *     },
 *   },
 *   list_class = "\Drupal\file\Plugin\Field\FieldType\FileFieldItemList",
 *   constraints = {"ReferenceAccess" = {}, "FileValidation" = {}}
 * )
 */
class ImageCropItem extends ImageItem {

  /**
 * The entity manager.
 *
 * @var \Drupal\Core\Entity\EntityManagerInterface
 */
 protected $entityManager;

  /**
   * {@inheritdoc} field definision settings node/edit.
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema["columns"]['value'] = array(
        'type' => 'text',
        'size' => 'tiny',
        'not null' => FALSE,
      );
    $schema["columns"]["target_id2"] = array(
        'description' => 'The ID of the file entity.',
        'type' => 'int',
        'unsigned' => TRUE,
    );
    return $schema;
  }

  /**
   * {@inheritdoc}
   */

  /*
  public function isEmpty() {
    $value = $this->get('target_id')->getValue();
    return $value === NULL || $value === '';
  }
  */

  // fieldSettingsForm -> (Campo)
  public static function defaultFieldSettings() {
    return array(
      'resolution' => '100x100',
      'croparea' => '200x200',
      'enforce_minimum' => 1,
      'enforce_ratio' => 1,
      'collapsible' => 0,
      'value' => 10,
    ) + parent::defaultFieldSettings();
  }

  // storageSettingsForm -> (Memorizzaione del campo)

  public static function defaultStorageSettings() {
    return [] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}  fieldSettingsForm -> (Campo)
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Value'))
      ->setDescription(t('The width of the image in pixels.'));

    $properties['target_id2'] = DataDefinition::create('string')
      ->setLabel(t('Value'))
      ->setDescription(t('The width of the image in pixels.'));

    return $properties;
  }

  /**
   * {@inheritdoc} (Settings memorizza campo)
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = [];
    $element += parent::storageSettingsForm($form, $form_state, $has_data);
    return $element;
  }

  /**
   * {@inheritdoc} (Settings campo)
   */
   public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    // Get base form from FileItem.
    //$settings = $field_definition->getSettings()
    $settings = $this->getSettings();
    //print_r($settings);
    // $element = parent::fieldSettingsForm($form, $form_state);

    $element = parent::fieldSettingsForm($form, $form_state);
    $element['collapsible'] = array(
      '#type' => 'radios',
      '#title' => t('Collapsible behavior'),
      '#options' => array(
        t('None.'),
        t('Collapsible, expanded by default.'),
        t('Collapsible, collapsed by default.'),
      ),
      '#default_value' => $settings['collapsible'],
      '#element_validate' => [[get_class($this), 'validateCollapsible']],
    );
    // Resolution settings.
    $resolution = explode('x', $settings['resolution']) + array('', '');
    //$resolution = explode('x', $settings['resolution']) + array('', '');
    $element['resolution'] = array(
      '#title' => t('The resolution to crop the image onto'),
      '#element_validate' => array('_image_field_resolution_validate', '_imagefield_crop_widget_resolution_validate'),
      '#theme_wrappers' => array('form_element'),
      '#description' => t('The output resolution of the cropped image, expressed as WIDTHxHEIGHT (e.g. 640x480). Set to 0 not to rescale after cropping. Note: output resolution must be defined in order to present a dynamic preview.'),
      '#element_validate' => [[get_class($this), 'validateRresolution']],
    );
    $element['resolution']['x'] = array(
      '#type' => 'textfield',
      '#default_value' => $resolution[0],
      '#size' => 5,
      '#maxlength' => 5,
      '#field_suffix' => ' x ',
      '#theme_wrappers' => array(),
    );
    $element['resolution']['y'] = array(
      '#type' => 'textfield',
      '#default_value' => $resolution[1],
      '#size' => 5,
      '#maxlength' => 5,
      '#field_suffix' => ' ' . t('pixels'),
      '#theme_wrappers' => array(),
    );
    $element['enforce_ratio'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enforce crop box ratio'),
      '#default_value' => $settings['enforce_ratio'],
      '#description' => t('Check this to force the ratio of the output on the crop box. NOTE: If you leave this unchecked but enforce an output resolution, the final image might be distorted'),
    );
    $element['enforce_minimum'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enforce minimum crop size based on the output size'),
      '#default_value' => $settings['enforce_minimum'],
      '#description' => t('Check this to force a minimum cropping selection equal to the output size. NOTE: If you leave this unchecked you might get zoomed pixels if the cropping area is smaller than the output resolution.'),
      //'#element_validate' => array('_imagefield_crop_widget_enforce_minimum_validate'),
    );

    // Crop area settings
    $croparea = explode('x', $settings['croparea']) + array('', '');
    $element['croparea'] = array(
      '#title' => t('The resolution of the cropping area'),
      '#element_validate' => array('_imagefield_crop_widget_croparea_validate'),
      '#theme_wrappers' => array('form_element'),
      '#description' => t('The resolution of the area used for the cropping of the image. Image will displayed at this resolution for cropping. Use WIDTHxHEIGHT format, empty or zero values are permitted, e.g. 500x will limit crop box to 500 pixels width.'),
      '#element_validate' => [[get_class($this), 'validateCropArea']],
    );
    $element['croparea']['x'] = array(
      '#type' => 'textfield',
      '#default_value' => $croparea[0],
      '#size' => 5,
      '#maxlength' => 5,
      '#field_suffix' => ' x ',
      '#theme_wrappers' => array(),
    );
    $element['croparea']['y'] = array(
      '#type' => 'textfield',
      '#defavult_value' => $croparea[1],
      '#size' => 5,
      '#maxlength' => 5,
      '#field_suffix' => ' ' . t('pixels'),
      '#theme_wrappers' => array(),
    );
    // print_r(array_keys($element));
    // print_r($element["default_input"]);
    //print_r($element["default_image"]);
    // Add default_image element.
    // static::defaultImageForm($element, $settings);
    // $element['default_image']['#description'] = t("If no image is uploaded, this image will be shown on display and will override the field's default image.");

    return $element;
  }

  /**
   * Element validate function for resolution fields.
   */
   public static function validateRresolution($element, FormStateInterface $form_state) {
    // $form_state->setError($element['x'],t('Both a height and wid'));
    $form_state->setValueForElement($element, $element['x']['#value'] . 'x' . $element['y']['#value']);
  }
  /**
   * Element validate function for Crop Area fields.
   */
  public static function validateCropArea($element, FormStateInterface $form_state) {
    $form_state->setValueForElement($element, $element['x']['#value'] . 'x' . $element['y']['#value']);
  }
  /**
   * Element validate function for Collapsible fields.
   */
  public static function validateCollapsible($element, FormStateInterface $form_state) {
    // print_r($element['#value']);
    // print_r($element);
    // exit(0);
    // $form_state->setValueForElement("collapsible", $element['#value']);
  }


  /**
   * Gets the entity manager.
   *
   * @return \Drupal\Core\Entity\EntityManagerInterface
   */
  protected function getEntityManager() {
    if (!isset($this->entityManager)) {
      $this->entityManager = \Drupal::entityManager();
    }
    return $this->entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
  //  $entity = $this->getEntity();
  }

}
