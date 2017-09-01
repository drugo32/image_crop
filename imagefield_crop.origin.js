(function ($) {
/**
 * @file
 * Imagefield_crop module js
 *
 * JS for cropping image widget
 */
Drupal.behaviors.imagecrop_field = {
  attach: function (context, settings) {
    // wait till 'fadeIn' effect ends (defined in filefield_widget.inc)
    setTimeout(attachJcrop, 1000, context);
    //attachJcrop(context);
    // var settings = settings.imagecrop_field;
    // console.log(settings);
    function attachJcrop(context) {
      if ($('.cropbox', context).length == 0) {
        // no cropbox, probably an image upload (http://drupal.org/node/366296)
        return;
      }

      $('.cropbox', context).once('cropbox').each(function() {
      // add Jcrop exactly once to each cropbox
      //$('.cropbox', context).once(function() {
        var self = $(this);

        //alert("found a cropbox" + self.attr('id'));

        // get the id attribute for multiple image support
        var self_id = self.attr('id');
        var id = "test";//self_id.substring(0, self_id.indexOf('-cropbox'));
        // get the name attribute for imagefield name
        var widget = self.parent().parent();

          if ($(".edit-image-crop-changed", widget).val() == 1) {
              $('.preview-existing', widget).css({display: 'none'});
              $('.jcrop-preview', widget).css({display: 'block'});
          }

        $(this).Jcrop({
          onChange: function(c) {
            $('.preview-existing', widget).css({display: 'none'});
            var preview = $('.imagefield-crop-preview', widget);
            // skip newly added blank fields
            console.log(drupalSettings);
            console.log(drupalSettings.imagecrop_field);
            if (undefined == drupalSettings.imagecrop_field[id].preview) {
              return;
            }
            var rx = drupalSettings.imagecrop_field[id].preview.width / c.w;
            var ry = drupalSettings.imagecrop_field[id].preview.height / c.h;

            console.log(rx);
            console.log(ry);
            $('.jcrop-preview', preview).css({
              width: Math.round(rx * drupalSettings.imagecrop_field[id].preview.orig_width) + 'px',
              height: Math.round(ry * drupalSettings.imagecrop_field[id].preview.orig_height) + 'px',
              marginLeft: '-' + Math.round(rx * c.x) + 'px',
              marginTop: '-' + Math.round(ry * c.y) + 'px',
              display: 'block'
            });
            // Crop image even if user has left image untouched.
            $(widget).siblings('.preview-existing').css({display: 'none'});
            $(widget).siblings(".edit-image-crop-x").val(c.x);
            $(widget).siblings(".edit-image-crop-y").val(c.y);
            if (c.w) $(widget).siblings(".edit-image-crop-width").val(c.w);
            if (c.h) $(widget).siblings(".edit-image-crop-height").val(c.h);
            $(widget).siblings(".edit-image-crop-changed").val(1);
          },
          /*
          onSelect: function(c) {
            $(widget).siblings('.preview-existing').css({display: 'none'});
            $(widget).siblings(".edit-image-crop-x").val(c.x);
            $(widget).siblings(".edit-image-crop-y").val(c.y);
            if (c.w) $(widget).siblings(".edit-image-crop-width").val(c.w);
            if (c.h) $(widget).siblings(".edit-image-crop-height").val(c.h);
            $(widget).siblings(".edit-image-crop-changed").val(1);
          },*/
          onSelect: function(c) {

  // $(widget).siblings('.preview-existing').css({display: 'none'});
  $(".edit-image-crop-x", widget).val(c.x);
  $(".edit-image-crop-y", widget).val(c.y);
  if (c.w) $(".edit-image-crop-width", widget).val(c.w);
  if (c.h) $(".edit-image-crop-height", widget).val(c.h);
    $(".edit-image-crop-changed", widget).val(1);

          },
          aspectRatio: drupalSettings.imagecrop_field[id].box.ratio,
          boxWidth: drupalSettings.imagecrop_field[id].box.box_width,
          boxHeight: drupalSettings.imagecrop_field[id].box.box_height,
          minSize: [drupalSettings.imagecrop_field[id].minimum.width, drupalSettings.imagecrop_field[id].minimum.height],
          /*
           * Setting the select here calls onChange event, and we lose the original image visibility
          */
          setSelect: [
         parseInt($(".edit-image-crop-x", widget).val()),
         parseInt($(".edit-image-crop-y", widget).val()),
         parseInt($(".edit-image-crop-width", widget).val()) + parseInt($(".edit-image-crop-x", widget).val()),
         parseInt($(".edit-image-crop-height", widget).val()) + parseInt($(".edit-image-crop-y", widget).val())
        ]
          /*
          setSelect: [
            parseInt($(widget).siblings(".edit-image-crop-x").val()),
            parseInt($(widget).siblings(".edit-image-crop-y").val()),
            parseInt($(widget).siblings(".edit-image-crop-width").val()) + parseInt($(widget).siblings(".edit-image-crop-x").val()),
            parseInt($(widget).siblings(".edit-image-crop-height").val()) + parseInt($(widget).siblings(".edit-image-crop-y").val())
          ]
          */
        });
      });
    };
  }
};

})(jQuery);
