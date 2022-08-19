$(function() {
        
	$(document).on('click', '.selected-color', function() {

		location_id = $(this).data('id')

		$(this).parents('tr').toggleClass('highlight');
		$(this).parents('tr').siblings().removeClass('highlight');

		__open_colorPicker($(this), location_id)

	})


	// Open the Picker
	function __open_colorPicker(el, location_id) {

		// open
		$('.color-choices').addClass('active');
		
		// remove color selection
		$('.color-choices').find('input').prop("checked", false)
		
		if( el.attr('data-color') ) {
			$('.color-choices').find('input[data-color='+ el.attr('data-color') +']').prop('checked', true)
		}
		
		// Select Color onClick
		$('.color-choices').find('label').unbind().on('click', function() {

			let color = $(this).attr('data-color')

			// ajax save
			$.ajax( {
				dataType: 'json',
				url: '/location/update/' + location_id,
				type: 'POST',
				data: {
					color: color
				},
				success: function ( $resp ) {

					sc.alert.show('alert-success', $resp.message)

					el.attr('style', 'background-color:#' + color)
					el.attr('data-color', color)
					return

				}
			} )
			


		})
	} 
	
			
	// close on esc
	$(document).keydown(function(e) {

		if (e.keyCode != 27) {
			return;
		}

		if( $('.color-choices').hasClass('active') ) {
			$('.color-choices').removeClass('active')
		}
	})

	// close on btn
	$(document).on('click', '#closeColorPicker', function(e) {
		$('.color-choices').removeClass('active')
	})
	
})