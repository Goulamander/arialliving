/*
|--------------------------------------------------------------------------
| Service Order:  Line items
|--------------------------------------------------------------------------
*/
'use strict'


if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.lineItem = {


	init: function() {
		this.registerEvents()
	},


    
    registerEvents: function() {

        let self = this

        $(document).on('click', '[data-open-item]', function() {
            self.open($(this).attr('data-open-item'))
        })
    },



    open: function(line_item_id) {

        if(!line_item_id)
            return
        
        let modal = $('#mod-line-item')

        axios.get(`/admin/item/service-items/get/${line_item_id}`)
            .then(function(response) {

                if(!response.data)
                    return false

                myapp.modal.fillThenOpen(modal, response.data.data)
                return
            })
            .catch(e => _errorResponse(e))
    }

}

myapp.lineItem.init()