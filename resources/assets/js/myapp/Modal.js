/*
|--------------------------------------------------------------------------
| Modal related methods
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.modal = {

    init: function() {


        $(function() {


            $(document).on('click', '.md-trigger', function() {

                const modal_id = $(this).attr('data-target') || '',
                      modal = $(modal_id)
                      action_route = $(this).attr('data-route') || ''
                  
                let fill_data = this.dataset.fill ? this.dataset.fill.split(':') : ''

                if(fill_data && fill_data.length == 2) {
                    modal.find(`input[name="${fill_data[0]}"]`).val(fill_data[1])
                }

                if(action_route) {
                    modal.find('form').prop('action', action_route)    
                }

                if(modal.length) {
                    modal.find('input[data-id]').val(this.value)
                } 
            })



            // Send action URL from a tag to the modal form
            $(document).on('click', 'a.actions', function (e) {

                e.preventDefault()

                let id = $(this).data('id') || '',
                    route = $(this).attr('href'),
                    modal = $(this).data('target'),
                    reload  = $(this).data('reload') || false

                $(modal).find('form')
                    .prop('action', route)    
                    .attr('data-reload', reload)

                $(modal).modal('toggle')
            })

        })
    },



    /**
     * Fill and Open modal
     * 
     * @param {el} modal 
     * @param {json} data 
     */
    fillThenOpen(modal, data) {

        if(!modal) {
            return
        }

        if(!data) {
            modal.modal('show')
        }

        // for restoring the model's original state before the open
        let original_content = modal[0].innerHTML

        // Update the title
        modal[0].querySelector('.modal-header h3').innerHTML = data.name || data.title

        // Update the action route
        modal[0].querySelector('form').action = data.update_route

        // fill-in fields
        for(key in data) {

            if(!data.hasOwnProperty(key)) 
                continue
            
            let input = modal[0].querySelector(`[name="${key}"]:not([type="file"])`)

            if(input) 
                input.value = data[key]  
        }

        // thumbnails
        let thumb_uploader = modal[0].querySelector('.inline-uploader')

        if(thumb_uploader && data.thumb_path) {

            thumb_uploader.insertAdjacentHTML('afterbegin', `<input type="hidden" value="${data.thumb_path}">`)
            myapp.fileManager.initInlineUploader([thumb_uploader])
        }

        modal.modal({
            backdrop: 'static',
            keyboard: false
        }, 'show')

        modal.on('hidden.bs.modal', function () {
            modal[0].innerHTML = original_content
            myapp.fileManager.initInlineUploader()
        })

        return

    }





}

myapp.modal.init()