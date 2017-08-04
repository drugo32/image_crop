(function ($) {
/**
 * @file
 * Imagefield_crop module js
 *
 * JS for cropping image widget
 */
Drupal.behaviors.imagefield_crop = {
  attach: function (context, settings) {
    $(function(){
      console.log();
    	$('#jcrop_target').Jcrop({
    		onChange: showPreview,
    		onSelect: showPreview,
    		aspectRatio: 1
    	});

    });

  //   When the selection is moved, this function is called:

    function showPreview(c)
    {
      // previe image size
    	var rx = drupalSettings.imagecrop_field.test.preview.width / c.w;
    	var ry =  drupalSettings.imagecrop_field.test.preview.height / c.h;

/*
      $('.jcrop-preview', preview).css({
                width: Math.round(rx * settings.imagefield_crop[id].preview.orig_width) + 'px',
                height: Math.round(ry * settings.imagefield_crop[id].preview.orig_height) + 'px',
                marginLeft: '-' + Math.round(rx * c.x) + 'px',
                marginTop: '-' + Math.round(ry * c.y) + 'px',
                display: 'block'
              });
  */
  // 500 original size.
    	$('.jcrop-preview-wrapper img').css({
        width: Math.round(rx * drupalSettings.imagecrop_field.test.preview.orig_width) + 'px',
        height: Math.round(ry * drupalSettings.imagecrop_field.test.preview.orig_height) + 'px',
        marginLeft: '-' + Math.round(rx * c.x) + 'px',
        marginTop: '-' + Math.round(ry * c.y) + 'px',
        display: 'block'
      });
    }



  }
};

})(jQuery);
