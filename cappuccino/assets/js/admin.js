(function ($) {
	'use strict';

	var frame;

	function updatePreview(url) {
		var $preview = $('#capp-image-preview');
		var $removeBtn = $('#capp-remove-btn');

		if (url) {
			$preview.html('<img src="' + url + '" alt="" />');
			$removeBtn.show();
		} else {
			$preview.html('<span class="capp-no-image">尚未选择图片</span>');
			$removeBtn.hide();
		}
	}

	$('#capp-upload-btn').on('click', function (e) {
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
			$('#capp-image-id').val(attachment.id);
			$('#capp-image-url').val(attachment.url);
			updatePreview(attachment.url);
		});

		frame.open();
	});

	$('#capp-remove-btn').on('click', function (e) {
		e.preventDefault();
		$('#capp-image-id').val('0');
		$('#capp-image-url').val('');
		updatePreview('');
	});
})(jQuery);
