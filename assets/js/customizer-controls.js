(function ($) {
  function parseIds(value) {
    return (value || '')
      .split(',')
      .map(function (id) {
        return parseInt(id, 10);
      })
      .filter(function (id) {
        return !Number.isNaN(id) && id > 0;
      });
  }

  function renderPreview($control, ids) {
    var $preview = $control.find('.garner-multi-image-preview');
    $preview.empty();

    if (!ids.length) {
      $preview.append('<p class="description">No images selected.</p>');
      return;
    }

    ids.forEach(function (id) {
      var attachment = wp.media.attachment(id);
      attachment.fetch().then(function () {
        var data = attachment.toJSON();
        var thumb = data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url;
        var item = $('<img>', {
          src: thumb,
          alt: data.alt || '',
          css: {
            width: '72px',
            height: '72px',
            objectFit: 'cover',
            marginRight: '6px',
            marginBottom: '6px',
            border: '1px solid #dcdcde'
          }
        });
        $preview.append(item);
      });
    });
  }

  $(function () {
    $('.customize-control-garnernewtheme_multi_image').each(function () {
      var $control = $(this);
      var $input = $control.find('.garner-multi-image-input');

      renderPreview($control, parseIds($input.val()));

      $control.on('click', '.garner-multi-image-select', function (event) {
        event.preventDefault();

        var selectedIds = parseIds($input.val());
        var frame = wp.media({
          title: 'Select Hero Carousel Images',
          button: { text: 'Use selected images' },
          multiple: true,
          library: { type: 'image' }
        });

        frame.on('open', function () {
          var selection = frame.state().get('selection');
          selectedIds.forEach(function (id) {
            var attachment = wp.media.attachment(id);
            attachment.fetch();
            selection.add(attachment);
          });
        });

        frame.on('select', function () {
          var ids = frame
            .state()
            .get('selection')
            .map(function (attachment) {
              return attachment.id;
            });

          $input.val(ids.join(',')).trigger('change');
          renderPreview($control, ids);
        });

        frame.open();
      });

      $control.on('click', '.garner-multi-image-clear', function (event) {
        event.preventDefault();
        $input.val('').trigger('change');
        renderPreview($control, []);
      });
    });
  });
})(jQuery);
