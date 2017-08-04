(function ($) {
/**
 * @file
 * Imagefield_crop module js
 *
 * JS for cropping image widget
 */
Drupal.behaviors.imagefield_crop = {
  attach: function (context, settings) {
    // wait till 'fadeIn' effect ends (defined in filefield_widget.inc)
    setTimeout(attachJcrop, 1000, context);
    //attachJcrop(context);

    function attachJcrop(context) {

      if ($('.cropbox', context).length == 0) {
        // no cropbox, probably an image upload (http://drupal.org/node/366296)
        return;
      }
      // add Jcrop exactly once to each cropbox


      $('.cropbox', context).once('cropbox').each(function() {
        var self = $(this);
        console.log(self.attr('id'));
        if(self.attr('id') == undefined) {
          console.log("return");
          return;
        }
          console.log(drupalSettings.imagecrop_field.test);
        // get the id attribute for multiple image support
        var self_id = self.attr('id');
        // console.log(self_id);
        var id = self_id.substring(0, self_id.indexOf('-cropbox'));
        // get the name attribute for imagefield name
        var widget = self.parents(".field--type-field-example-rgb");
          if ($(".edit-image-crop-changed", widget).val() == 1) {
              $('.preview-existing', widget).css({display: 'none'});
              $('.jcrop-preview', widget).css({display: 'block'});
          }


        $(this).Jcrop({
          onChange: function(c) {
            //$('.preview-existing', widget).css({display: 'none'});
            var preview = $('.imagefield-crop-preview', widget);
            // skip newly added blank fields
            //if (undefined == settings.imagefield_crop[id].preview) {
            //  return;
            //}
            console.log("change");
            var rx = drupalSettings.imagecrop_field.test.preview.width / c.w;
            var ry = drupalSettings.imagecrop_field.test.preview.height / c.h;
            $('.jcrop-preview', preview).css({
              width: Math.round(rx * drupalSettings.imagecrop_field.test.preview.orig_width) + 'px',
              height: Math.round(ry * drupalSettings.imagecrop_field.test.preview.orig_height) + 'px',
              marginLeft: '-' + Math.round(rx * c.x) + 'px',
              marginTop: '-' + Math.round(ry * c.y) + 'px',
              display: 'block'
            });
            // Crop image even if user has left image untouched.
            // $(widget).siblings('.preview-existing').css({display: 'none'});

            $(".edit-image-crop-x", widget).val(c.x);
            $(".edit-image-crop-y", widget).val(c.y);

            if (c.w) $(".edit-image-crop-width", widget).val(c.w);
            if (c.h) $(".edit-image-crop-height", widget).val(c.h);
            $(".edit-image-crop-changed", widget).val(1);

          },
          onSelect: function(c) {

            // $(widget).siblings('.preview-existing').css({display: 'none'});
            $(".edit-image-crop-x", widget).val(c.x);
            $(".edit-image-crop-y", widget).val(c.y);
            if (c.w) $(".edit-image-crop-width", widget).val(c.w);
            if (c.h) $(".edit-image-crop-height", widget).val(c.h);
            $(".edit-image-crop-changed", widget).val(1);

          },


          aspectRatio: drupalSettings.imagecrop_field.test.box.ratio,
          boxWidth: drupalSettings.imagecrop_field.test.box.box_width,
          boxHeight: drupalSettings.imagecrop_field.test.box.box_height,
          minSize: [drupalSettings.imagecrop_field.test.minimum.width, drupalSettings.imagecrop_field.test.minimum.height],


          //  Setting the select here calls onChange event, and we lose the original image visibility

          setSelect: [
           parseInt($(".edit-image-crop-x", widget).val()),
           parseInt($(".edit-image-crop-y", widget).val()),
           parseInt($(".edit-image-crop-width", widget).val()) + parseInt($(".edit-image-crop-x", widget).val()),
           parseInt($(".edit-image-crop-height", widget).val()) + parseInt($(".edit-image-crop-y", widget).val())
          ]
        });

      });
    };
  }
};

})(jQuery);