jQuery(function($) {
	'use strict';

	$('.colourme').wpColorPicker();

	var devices = $('#rpb_devices > div');

	function activateDevices() {
		var sizeInput = parseInt($('#mobile_size_input').val(), 10) || 680;

		devices.each(function() {
			var deviceSize = parseInt($(this).attr('data-size'), 10) || 0;
			$(this).toggleClass('active', deviceSize <= sizeInput);
		});
	}

	devices.on('click', function() {
		$('#mobile_size_input').val($(this).attr('data-size'));
		activateDevices();
	});

	function showLocationFields() {
		var selected = $('input[name="RPB_options[location]"]:checked').val() || 'address';
		$('.location_option').hide();
		$('.location_option[data-type="' + selected + '"]').show();
	}

	$('input[name="RPB_options[location]"]').on('change', showLocationFields);

	activateDevices();
	showLocationFields();
});
