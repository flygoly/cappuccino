(function ($) {
	'use strict';

	var frame;

	function updatePreview(url) {
		var $preview = $('#wpd-image-preview');
		var $removeBtn = $('#wpd-remove-btn');

		if (url) {
			$preview.html('<img src="' + url + '" alt="" />');
			$removeBtn.show();
		} else {
			$preview.html('<span class="wpd-no-image">尚未选择图片</span>');
			$removeBtn.hide();
		}
	}

	$('#wpd-upload-btn').on('click', function (e) {
		e.preventDefault();

		if (frame) {
			frame.open();
			return;
		}

		frame = wp.media({
			title: '选择赞赏图片',
			button: { text: '使用此图片' },
			multiple: false,
			library: { type: 'image' }
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$('#wpd-image-id').val(attachment.id);
			$('#wpd-image-url').val(attachment.url);
			updatePreview(attachment.url);
		});

		frame.open();
	});

	$('#wpd-remove-btn').on('click', function (e) {
		e.preventDefault();
		$('#wpd-image-id').val('0');
		$('#wpd-image-url').val('');
		updatePreview('');
	});
})(jQuery);
