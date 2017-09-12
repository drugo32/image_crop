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
        console.log(drupalSettings.imagecrop_field["test"]);
        alert("ddss");
        console.log(self.attr('id'));
        // get the id attribute for multiple image support
        var self_id = self.attr('id');
        // console.log(self_id);
        var id = self_id.substring(0, self_id.indexOf('-cropbox'));
        console.log(id);
        // get the name attribute for imagefield name
        var widget = self.parents(".field--type-field-example-rgb");
          if ($(".edit-image-crop-changed", widget).val() == 1) {
              $('.preview-existing', widget).css({display: 'none'});
              $('.jcrop-preview').css({display: 'block'});
          }


        $(this).Jcrop({
          onChange: function(c) {
            console.log("change");
            //$('.preview-existing', widget).css({display: 'none'});
            var preview = $('.imagefield-crop-preview');
            // skip newly added blank fields
            //if (undefined == settings.imagefield_crop[id].preview) {
            //  return;
            //}
            // console.log("change");
          //  alert($(".edit-image-crop-x").val());
            var rx = drupalSettings.imagecrop_field['test'].preview.width / c.w;
            var ry = drupalSettings.imagecrop_field['test'].preview.height / c.h;
            $('.jcrop-preview').css({
              width: Math.round(rx * drupalSettings.imagecrop_field['test'].preview.orig_width) + 'px',
              height: Math.round(ry * drupalSettings.imagecrop_field['test'].preview.orig_height) + 'px',
              marginLeft: '-' + Math.round(rx * c.x) + 'px',
              marginTop: '-' + Math.round(ry * c.y) + 'px',
              display: 'block'
            });
            // Crop image even if user has left image untouched.
            // $(widget).siblings('.preview-existing').css({display: 'none'});

            $(".edit-image-crop-x").val(c.x);
            $(".edit-image-crop-y").val(c.y);

            if (c.w) $(".edit-image-crop-width").val(c.w);
            if (c.h) $(".edit-image-crop-height").val(c.h);
            $(".edit-image-crop-changed").val(1);

          },
          onSelect: function(c) {
            console.log("select");
            console.log( widget);
            console.log($(".edit-image-crop-x", widget));
            // , widget
            // $(widget).siblings('.preview-existing').css({display: 'none'});
            $(".edit-image-crop-x").val(c.x);
            $(".edit-image-crop-y").val(c.y);
            if (c.w) $(".edit-image-crop-width").val(c.w);
            if (c.h) $(".edit-image-crop-height").val(c.h);
            $(".edit-image-crop-changed").val(1);

            console.log("ONSELECT");

          },


          aspectRatio: drupalSettings.imagecrop_field['test'].box.ratio,
          boxWidth: drupalSettings.imagecrop_field['test'].box.box_width,
          boxHeight: drupalSettings.imagecrop_field['test'].box.box_height,
          minSize: [drupalSettings.imagecrop_field['test'].minimum.width, drupalSettings.imagecrop_field['test'].minimum.height],


          //  Setting the select here calls onChange event, and we lose the original image visibility

          setSelect: [
           parseInt($(".edit-image-crop-x").val()),
           parseInt($(".edit-image-crop-y").val()),
           parseInt($(".edit-image-crop-width").val()) + parseInt($(".edit-image-crop-x").val()),
           parseInt($(".edit-image-crop-height").val()) + parseInt($(".edit-image-crop-y").val())
          ]
        });

      });
    };
  }
};

})(jQuery);
