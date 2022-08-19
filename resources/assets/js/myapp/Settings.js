
/*
|--------------------------------------------------------------------------
| Init / Settings
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}

myapp.settings = {


    init() {

        let scope = this

    //     $(function() {

    //         $('#setting-form .btn-save-settings').on('click', function() {
    //             scope.storeSettings($(this))
    //         })
    
    //     })
    },




    /**
     * Store the Setting changes
     * 
     * @param {*} submitBtn 
     * @return void
     */
    storeSettings(submitBtn) {

        submitBtn.prop('disabled', true).addClass('loading')

        const form = $('#setting-form')
        
        let $submit_obj = myapp.form.collectInputs(form)

        $('.html_editor').each(function(index, item) {
            let code = $(item).attr('data-code') || ''
            if(code) {
                $submit_obj[code] = this.quill.root.innerHTML
            }
        })

        axios.post(form.attr('action'), $submit_obj)
            .then(function (response) {

                if(!response.data.data) {
                    return false
                }

                if(response.data.message) {
                    myapp.alerts.show('alert-success', response.data.message, 8000)
                }
                submitBtn.prop('disabled', false).removeClass('loading')
                return
                    
            })
            .catch(e => _errorResponse(e))

    }


}

myapp.settings.init()