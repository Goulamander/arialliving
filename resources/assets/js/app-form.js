let request;

// init Parsley: JS Form Validation
$('form').parsley({
    errorsContainer: function(el) {
        return el.$element.closest('.form-group');
    }
})


// Mobile Phone Number
$('.mobile-number').inputmask('9999 999 999', { placeholder: '____ ___ ____' })
// Phone Number
$('.phone-number').inputmask('(09) 9999 9999', { placeholder: '(0_) ____ ____' })
// Credit Card
$('.credit-input').inputmask('9999 9999 9999 9999', { placeholder: 'xxxx-xxxx-xxxx-xxxx' })



$(function() {

    $(document).on('click', 'button[type="submit"]', function(e) {
        let form = $(this).parents('form')

        if ( form.parsley().isValid() ) {
            e.preventDefault()

            $(this).prop('disabled', true).addClass('loading')

            SubmitForm(form)
        }
        else {
            myapp.booking.fixSliderHight()
        }
    })
})


const SubmitForm = (form) => {
    let obj = myapp.form.collectInputs(form)
        obj['action_route'] = form.attr('action') || ''

    // Add content form the html editors
    let html_editors = form[0].querySelectorAll('._html_content')

    if(html_editors) 
    {
        html_editors.forEach(editor => {
            obj[editor.dataset.name] = editor.quill.root.innerHTML
        })
    }

    // Add content form the html editors
    if($(form).find('._full_html_editor').length) {

        let editor = $(form).find('._full_html_editor')[0]
        obj[editor.dataset.name] = editor.quill.root.innerHTML
    }

    if(obj === false) {
        form.find('button[type="submit"]').prop('disabled', false).removeClass('loading')
        return
    }

    let _form_enctype = form.attr('enctype');

    //  form is upload file
    if (_form_enctype === 'multipart/form-data') {
        let bodyFormData = new FormData();
        Object.entries(obj).forEach(([key, value]) => {
            bodyFormData.append(key, value);
        });
        form.find("input[type=file]").each(function() {
            bodyFormData.set($(this).attr('name'), $(this)[0].files[0]);
        });
        StoreData({action_route: obj.action_route, data: bodyFormData}, form) // store with form data
        return false;
    }

    StoreData(obj, form)
}


/**
 * 
 * Store data from Forms
 * 
 * @param {*} $obj
 * @param {*} $form
 * 
 * @return Void
 */
const StoreData = function($obj, $form = null) {
    let _data = $obj;
    if ($form && $form.attr('enctype') === 'multipart/form-data') {
        _data = $obj.data;
    }
    axios.post($obj.action_route, _data)
        .then(function (response) {
            // empty response
            if(!response.data) {
                return false
            }

            /**
             * Refresh the dataTable in the background
             */
            if( typeof window.dataTable !== 'undefined' ) {
                window.dataTable.DataTable().ajax.reload()
            }

            if( typeof window.AdminCalendar !== 'undefined') {
                myapp.calendar.CalendarRefetch()
            }

            /**
             * Resident Booking Response
             */
            if('ResidentBooking' in response.data.data) {

                if('redirect_to' in response.data.data) {
                    window.location.href = response.data.data.redirect_to
                    return
                }
                myapp.booking.afterBookingSubmission(response.data.data.ResidentBooking)
                return
            }

            /**
             * Update PDF list after submission
             */
            if( 'PDFUpdate' in response.data.data ) {
                myapp.fileManager.updatePDFListAfterUpdate(response.data.data)
            }


            /**
             * Clear the form, show response and return.
             */
            if($form) {

                if($form[0].dataset.reload && $form[0].dataset.reload == 'true') {
                    window.location.reload()
                    return
                }

                let modal = $form.parents('.modal')

                if( modal.length ) {
                    // reset form
                    $form[0].reset()
                    // Close the modal
                    modal.modal('hide')
                }

                _releaseSubmitBtn($form);

                // Confirm password prompt
                let _form_id = $form.attr('id');
                if(_form_id && _form_id === 'ConfirmPasswordForm') {
                    SubmitForm($('#BookingForm'))
                    return;
                }
            }

            sc.alert.show('alert-success', response.data.message || 'Successful update')
            return
        })
        .catch(function (error) {
            _errorResponse(error)
            if($form) _releaseSubmitBtn($form)
            return
        })
}


/**
 * Release the Submit button
 * @param {*} $form 
 */
const _releaseSubmitBtn = function($form) {
    if(!$form) {
        return;
    }
    $form.find('button[type="submit"]')
        .removeClass('loading')
        .attr('disabled', false);
}

/**
 * Fill in the modal (delete this maybe??)
 * 
 * @param {String} data_type [location]
 * @param {Int} id
 */
const editData = function(modal, data_type, id) {

    let get_url = ''

    switch(data_type) 
    {
        case 'location':
            get_url = '/location/' + id
            break

        default:
            get_url = ''
            return
    }

    if(!get_url) return

    // add loader

    $.ajax({
        dataType: 'json',
        url: get_url,
        type: 'GET',

        success: function ( $resp ) {

            /**
             * Show the Error Response
             */
            if($resp.error) {
                sc.alert.show('alert-danger', 'Something went wrong. ' + $resp.error)
                return
            }

            $(modal).find('form').removeClass('jsSubmit')


            /* Find & fill the fields */
            $.each( $resp.data, function(k, v) {

                var el = $(modal).find('[name="'+ k +'"]')

                if(el.length) {

                    switch( el.attr('type') ) 
                    {
                        case 'radio':
                        case 'checkbox':
                            // check
                            el.each( function() {
                                if( $(this).val() == v) {
                                    $(this).attr('checked', true)
                                }   
                            })
                            break

                        case 'date':
                            // fill date
                            if(v && v !== '-0001-11-30 00:00:00') {
                                el.val( moment(v, 'YYYY-MM-DD').format('YYYY-MM-DD') )
                            }
                            break

                        default:
                            // fill inputs / textarea
                            el.val(v)
                            break;
                    }
                }

            })

            // remove loader
            return
        }
    })
}
