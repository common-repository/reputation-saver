jQuery(document).ready(function($){

  var mediaUploader;

  $('.rs-upload-button').click(function(e) {
    var this_btn = $(this);
    e.preventDefault();
    // If the uploader object has already been created, reopen the dialog
      if (mediaUploader) {
      mediaUploader.open();
      return;
    }
    // Extend the wp.media object
    mediaUploader = wp.media.frames.file_frame = wp.media({
      title: 'Choose Image',
      button: {
      text: 'Choose Image'
    }, multiple: false });

    // When a file is selected, grab the URL and set it as the text field's value
    mediaUploader.on('select', function() {
      var attachment = mediaUploader.state().get('selection').first().toJSON();
      this_btn.prev('.rs-upload-url').val(attachment.url);
    });
    // Open the uploader dialog
    mediaUploader.open();
  });

  $(document).on('click', '.rs-upload-url', function(){
    $(this).next('.rs-upload-button').trigger('click');
  });

  // Add Color Picker to all inputs that have 'color-field' class
    // $(function() {
        $('.rs-color-picker').wpColorPicker();
    // });

});
