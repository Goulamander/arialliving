/*
|--------------------------------------------------------------------------
| Message helpers
|--------------------------------------------------------------------------
|
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}

myapp.messageHelper =  {

    /**
     * Validate the empty comment submissions
     * @param {Element} comment_textarea 
     */
    validateEmpty(el) {

        if( el.val().replace(/^\s+|\s+$/g, "").length == 0 )
        {
            el.parents('.mde-editor').addClass('validationError')
            setTimeout(function() {
                el.parents('.mde-editor').removeClass('validationError')
            }, 600)
            return false
        }
        return true
    },


    /**
     * Clear the form after the submission
     * @param {Element} el 
     */
    clearForm(el) {
        el.val('')
        el.nextAll('.CodeMirror')[0].CodeMirror.getDoc().setValue('')
    },



}