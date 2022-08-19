/*
|--------------------------------------------------------------------------
| Setting / Category
|--------------------------------------------------------------------------
*/
'use strict'


if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.category = {

        init: function() {
            this.registerEvents()
        },
    

        registerEvents: function() {
            let self = this
            $(document).on('click', '[data-open-category]', function() {
                self.open($(this).attr('data-open-category'))
            })
        },
    
        
        open: function(category_id) {
    
            if(!category_id)
                return
        
            let modal = $('#mod-category')
    
            axios.get(`/admin/settings/category/get/${category_id}`)
                .then(function(response) {
    
                    if(!response.data)
                        return false
    
                    myapp.modal.fillThenOpen(modal, response.data.data)
                    return
                })
                .catch(e => _errorResponse(e))
        },
    
}


myapp.category.init()