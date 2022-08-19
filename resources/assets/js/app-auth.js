let request

$(function() {

    document.body.classList.add('app-ready')

    // init Parsley: JS Form Validation
    $('form').parsley({
        errorsContainer: function(el) {
            return el.$element.closest('.form-group');
        }
    })        

    $(document).on('click', 'button[type="submit"]', function(e) 
    {
        var form = $(this).parents('form')

        if ( form.parsley().isValid() ) 
        {
            e.preventDefault()

            $(this).prop('disabled', true).addClass('loading')
            form.trigger('submit')
        }
    })

})


