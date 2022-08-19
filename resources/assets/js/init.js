var App = (function () {

	'use strict';

	var config = {}

	return {

		conf: config,
	
		init: function (options) {

			document.body.classList.add('app-ready')

			// Extends basic config with custom options
			$.extend( config, options )

			/**
			 *  DataTable Global Configuration.
			 */
			$.extend( true, $.fn.dataTable.defaults, {
				processing: true,
				serverSide: true,
				bLengthChange: false,
				responsive: true,
				deferRender: true,
				autoWidth: false,
				stateSave: false,
				dom: 'Blrtip',
				pageLength: 100,
				oLanguage: {
					oPaginate: {
						sPrevious: '<i class="material-icons">keyboard_arrow_left</i>',
						sNext: '<i class="material-icons">keyboard_arrow_right</i>'
					},
					sEmptyTable: "No results found",
					sProcessing: "<i class='ion ion-ios-refresh table-loading'></i>"
				}
			})

			// ajax Setup
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				error: function(xhr, status, error) {
				
					let err = eval("(" + xhr.responseText + ")")
					
					if(err && err.errors) {
						
						var ul = "<ul>"
						$.each(err.errors, function (index, value) {
							if(index != 'error') ul += "<li>"+ value +"</li>";
						})
						ul += "</ul>";
	
						sc.alert.show('alert-danger', ul)
						if($form) _releaseSubmitBtn($form)
						return
					}
				}
			})


		},

		// Helpers
		helpers: {

			// Format address
			formatAddress: (obj) => {
				if(!obj) return
				return `${obj.street_address_1} ${obj.suburb}, ${obj.postcode} ${obj.state}`
			}

		}
	}


})()