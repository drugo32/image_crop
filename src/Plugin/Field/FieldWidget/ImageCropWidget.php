<?php

/**
 * @file
 * Contains \Drupal\imagefield_crop\Plugin\Field\FieldWidget\ImageCropWidget.
 */
//namespace Drupal\image\Plugin\Field\FieldWidget;
namespace Drupal\imagefield_crop\Plugin\Field\FieldWidget;

use Drupal;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Plugin\views\field\Field;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldWidget\FileWidget;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;
use Drupal\field\Entity\FieldConfig;

/**
 * Plugin implementation of the 'image_image_crop' widget.
 *
 * @FieldWidget(
 *   id = "image_image_crop",
 *   label = @Translation("Image Crop"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageCropWidget extends ImageWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'progress_indicator' => 'throbber',
      'preview_image_style' => 'thumbnail',
      'collapsible' => 2,
      'resolution' => '200x150',
      'enforce_ratio' => TRUE,
      'enforce_minimum' => TRUE,
      'croparea' => '500x500',
      'size' => 60,
    ) + parent::defaultSettings();
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
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['collapsible'] = array(
      '#type' => 'radios',
      '#title' => t('Collapsible behavior'),
      '#options' => array(
        1 => t('None.'),
        2 => t('Collapsible, expanded by default.'),
        3 => t('Collapsible, collapsed by default.'),
      ),
      '#default_value' => $this->getSetting('collapsible'),
    );
    // Resolution settings.
    $resolution = explode('x', $this->getSetting('resolution')) + array('', '');
    $element['resolution'] = array(
      '#title' => t('The resolution to crop the image onto'),
/*
      '#element_validate' => array(
        '_image_field_resolution_validate',
        '_imagefield_crop_widget_resolution_validate'
      ),
*/
      '#element_validate' => [[get_class($this), 'validateRresolution']],
      '#theme_wrappers' => array('form_element'),
      '#description' => t('The output resolution of the cropped image, expressed as WIDTHxHEIGHT (e.g. 640x480). Set to 0 not to rescale after cropping. Note: output resolution must be defined in order to present a dynamic preview.'),
    );
    $element['resolution']['x'] = array(
      '#type' => 'textfield',
      '#default_value' => isset($resolution[0]) ? $resolution[0] : '',
      '#size' => 5,
      '#maxlength' => 5,
      '#field_suffix' => ' x ',
      '#theme_wrappers' => array(),
    );
    $element['resolution']['y'] = array(
      '#type' => 'textfield',
      '#default_value' => isset($resolution[1]) ? $resolution[1] : '',
      '#size' => 5,
      '#maxlength' => 5,
      '#field_suffix' => ' ' . t('pixels'),
      '#theme_wrappers' => array(),
    );
    $element['enforce_ratio'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enforce crop box ratio'),
      '#default_value' => $this->getSetting('enforce_ratio'),
      '#description' => t('Check this to force the ratio of the output on the crop box. NOTE: If you leave this unchecked but enforce an output resolution, the final image might be distorted'),
      '#element_validate' => array('_imagefield_crop_widget_enforce_ratio_validate'),
    );
    $element['enforce_minimum'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enforce minimum crop size based on the output size'),
      '#default_value' => $this->getSetting('enforce_minimum'),
      '#description' => t('Check this to force a minimum cropping selection equal to the output size. NOTE: If you leave this unchecked you might get zoomed pixels if the cropping area is smaller than the output resolution.'),
      '#element_validate' => array('_imagefield_crop_widget_enforce_minimum_validate'),
    );
    // Crop area settings
    // $croparea = $this->getSetting('croparea') ;

    $croparea = explode('x', $this->getSetting('croparea')) + array('', '');

    $element['croparea'] = array(
      '#title' => t('The resolution of the cropping area'),
      // '#element_validate' => array('_imagefield_crop_widget_croparea_validate'),
      '#theme_wrappers' => array('form_element'),
      '#description' => t('The resolution of the area used for the cropping of the image. Image will displayed at this resolution for cropping. Use WIDTHxHEIGHT format, empty or zero values are permitted, e.g. 500x will limit crop box to 500 pixels width.'),
      '#element_validate' => [[get_class($this), 'validateCropArea']],
    );
    $element['croparea']['x'] = array(
      '#type' => 'textfield',
      '#default_value' => isset($croparea[0]) ? $croparea[0] : '',
      '#size' => 5,
      '#maxlength' => 5,
      '#field_suffix' => ' x ',
      '#theme_wrappers' => array(),
    );
    $element['croparea']['y'] = array(
      '#type' => 'textfield',
      '#default_value' => isset($croparea[1]) ? $croparea[1] : '',
      '#size' => 5,
      '#maxlength' => 5,
      '#field_suffix' => ' ' . t('pixels'),
      '#theme_wrappers' => array(),
    );

    return $element;
  }


  /**
     * Element validate function for resolution fields.
     */
     public static function validateRresolution($element, FormStateInterface $form_state) {
      // $form_state->setError($element['x'],t('Both a height and wid'));

      dpm("validateResolution");

      $form_state->setValueForElement($element, $element['x']['#value'] . 'x' . $element['y']['#value']);
    }
    /**
      * Element validate function for Crop Area fields.
      */
    public static function validateCropArea($element, FormStateInterface $form_state) {
        dpm("validateCrop area");
        $form_state->setValueForElement($element, $element['x']['#value'] . 'x' . $element['y']['#value']);
    }
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Add properties needed by process() method.

//    if(isset($defaults[$file->id()])) {

        // \Drupal::configFactory()->getEditable('field.imagecrop_field.values')->delete()->save();
        // $a = $defaults  = \Drupal::config('field.imagecrop_field.values')->get(1);
        // dpm()
        //->set($defaults)
        //->save();
  //  }

    //$test = \Drupal::config('field.storage.node.field_cropx');
    //dpm($test);

    $settings = self::defaultSettings();
    foreach(array_keys($settings) as $key) {
      if(!isset($element['#' . $key])) {
        $element['#' . $key] = $this->getSetting($key);
      }
    }
    //$element['#element_validate'] = [[get_class($this), 'validateCropInfo']];

    //$element["#value_callback"][] = [[get_class($this), 'FieldCropvalue']];

    //dpm($element);
    // $element['#file_value_callbacks'][] = [[get_class($this), 'validateCropInfo']];

    return $element;
  }




  /**
   * Form API callback: Processes a image_image field element.
   *
   * Expands the image_image type to include the alt and title fields.
   *
   * This method is assigned as a #process callback in formElement() method.
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $element =  parent::process($element, $form_state, $form);

    if(isset($element['preview'])) { unset($element['preview']); }
    $element['#description'] = t('Click on the image and drag to mark how the image will be cropped');
    $path = drupal_get_path('module', 'imagefield_crop');

    // $element['#attached']['js'][] = "$path/Jcrop/js/jquery.Jcrop.js";
    // We must define Drupal.behaviors for ahah to work, even if there is no file
    //  $element['#attached']['js'][] = "$path/imagefield_crop.js";
    //
    // $element['#attached']['css'][] = "$path/Jcrop/css/jquery.Jcrop.css";
    // Add the image preview.
    if (!empty($element['#files']) && $element['#preview_image_style']) {

      $file = reset($element['#files']);
      $variables = array(
        'style_name' => $element['#preview_image_style'],
        'uri' => $file->getFileUri(),
      );

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

      list($rwidth, $rheight) = explode('x', $element['#resolution']);
      list($crop_w, $crop_h) = explode('x', $element['#croparea']);

      $element['imagecrop'] = [
        '#type' => 'item',
        '#theme' => 'imagefield_crop_widget',
        '#weight' => -10,
      ];

      $element['imagecrop']['#preview'] = array(
        '#weight' => -10,
        '#theme' => 'imagefield_crop_preview',
        '#width' => $rwidth,
        '#height' => $rheight,
        '#uri' => $variables['uri'],
        '#attributes' => [
          'class' => array('preview-existing', 'jcrop-preview'),
          /*'style' => 'display:none'*/
        ],
      );

      $element['imagecrop']['#cropbox'] = array(
        '#theme' => 'image',
        '#attributes' => array(
          'class' => 'cropbox',
          'id' => $element['#id'] . '-cropbox',
        ),
        '#uri' => $variables['uri'],
      );

      $fids = array_keys($element['#files']);
      $defaults  = \Drupal::config('field.imagecrop_field.values')->get($fids[0]);
      if(isset($defaults) && !empty($defaults)) {
        $element['cropinfo'] = self::imagefield_add_cropinfo_fields($fids[0]);
      }
      else {
        $element['cropinfo'] = self::imagefield_add_cropinfo_fields();
      }

      $preview_js = array(
        'orig_width' =>   $variables['width'],//$image->getWidth(),
        'orig_height' =>   $variables['height'],//$image->getHeight(),
        'width' => $rwidth,
        'height' => $rheight,
      );

      $settings = array(
        "test" => array(
          'box' => array(
            'ratio' => $rheight ? $element['#enforce_ratio'] * ($rwidth/$rheight) : 0,
            'box_width' => $crop_w,
            'box_height' => $crop_h,
          ),
          'minimum' => array(
            'width'   => $element['#enforce_minimum'] ? $res_w : NULL,
            'height'  => $element['#enforce_minimum'] ? $res_h : NULL,
          ),
          'preview' => $preview_js,
        ),
      );

      $element['#attached'] = array(
        'library' => array(
          'imagefield_crop/cropimage',
        ),
        'drupalSettings' => array('imagecrop_field' => $settings),
      );

    }


    //  $element['remove_button']['#submit'][] = [get_called_class(), 'imagefield_crop_widget_delete'];

    //  dpm($element['remove_button']);
    //  array_unshift($element['remove_button']['#submit'], 'imagefield_crop_widget_delete');

    return $element;
    // return
  }
  /*
  public function validate($element, FormStateInterface $form_state) {


  }
*/
public static function imagefield_crop_widget_delete($form, FormStateInterface $form_state) {
    // During the form rebuild, formElement() will create field item widget
    // elements using re-indexed deltas, so clear out FormState::$input to
    // avoid a mismatch between old and new deltas. The rebuilt elements will
    // have #default_value set appropriately for the current state of the field,
    // so nothing is lost in doing this.
    /*
    $button = $form_state->getTriggeringElement();
    //dpm($form_state['input']);
    $parents = array_slice($button['#parents'], 0, -2);
    //  dpm($parents)
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));
    dpm($element->getValues());
    $submitted_values = NestedArray::getValue($form_state->getValues(), array_slice($button['#parents'], 0, -2));
    dpm($submitted_values);
    // dpm($element);
    //  dpm($form_state['values']);
    dpm($parents);
    dpm($parents);
    //dpm(  $button );
    dpm("delete");
    */
}

/**
  * Element validate function for Crop Area fields.
  */
public static function validateCropInfo($element, FormStateInterface $form_state) {
    dpm("validateCrop Info");
  /*  //dpm($form_s)
    dpm($element["#title"]);
    dpm($element["#name"]);
    dpm($element["#parents"][0]);
    dpm($form_state->isSubmitted());

    $val = $form_state->getValue($element["#parents"][0]);
    dpm($val[0]['fids']);
    // => field_cropx[0][cropinfo][x]);
  fids
    dpm($form_state->getValues());
    dpm($form_state->getValue("nid"));

    // [#title] => x
    //  $form_state->setValueForElement($element, $element['x']['#value'] . 'x' . $element['y']['#value']);
    */
}

  public static function imagefield_add_cropinfo_fields($fid = NULL) {

    $defaults = array(
      'x'       => 0,
      'y'       => 0,
      'width'   => 50,
      'height'  => 50,
      'changed' => 0,
    );
    if ($fid) {
      $defaults  = \Drupal::config('field.imagecrop_field.values')->get($fid);
      $defaults['changed'] = 0;
    }
    // dpm($defaults);
    foreach ($defaults as $name => $default) {
      $element[$name] = array(
        '#type' => 'textfield',
        '#title' => $name,
        '#attributes' => array('class' => array('edit-image-crop-' . $name)),
        '#default_value' => $default,
      );
    }
    return $element;
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

  public static function value($element, $input, FormStateInterface $form_state) {
    $return = parent::value($element, $input, $form_state);
    /*
    // save widget info vars.
    if(isset($input['fids']) && !empty($input['fids']) ) {
      $info_titems = Array('x', 'y', 'width', 'height');
      $fid = (is_array($input['fids'])) ? $input['fids'][0] : $input['fids'];
      $values = array();
      foreach($info_titems as $name) {
        $values[$name] = (int) $input['cropinfo'][$name];
      }
      \Drupal::configFactory()->getEditable('field.imagecrop_field.values')
           ->set($fid, $values)
           ->save();
    }
    */
    return $return;
  }

  public static function submit($form, FormStateInterface $form_state) {

  }

}
