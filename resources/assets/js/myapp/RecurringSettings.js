/*
|--------------------------------------------------------------------------
| Recurring event
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.recurringSetting = {

    /**
     * Init
     */
    init() {

        let scope = myapp.recurringSetting

        $(function() {


            /**
             * Set as recurring onChange
             */
            $(document).on('change', 'select[name="event.type"]', function() {
            
                const el = $(this).parents("form"),
                      single_field_group = el.find('.single_event'),
                      repeating_field_group = el.find('.recurring_options')
            
                // Recurring      
                if( $(this).val() == 2 ) {
                    single_field_group.removeClass('active')
                    repeating_field_group.addClass('active')
                    // Clear Start / End / Next date
                    // scope.clearDateFields(el)
                }
                // Not recurring
                else {
                    single_field_group.addClass('active')
                    repeating_field_group.removeClass('active')
                    // Clear Start / End / Next date
                    // scope.clearDateFields(el)
                }
            })
            

            $(document).on('change', 'input[name="repeat_start"], input[name="repeat_end"]', function() {

                const el = $(this).parents("form")
                
                scope.recurring_preview(el)
            })


            /**
             * Repeat every value: onChange event
             */
            $(document).on('change', 'input[name="repeat_every"]', function() {

                const el = $(this).parents("form"),
                        frequency_select = $('select[name="frequency"]')

                // Update frequency values from singular to plural and back.
                if( this.value > 1 ) {
                    // plural
                    frequency_select.find('option').each(function() {
                        switch(this.text) {
                            case 'Week':
                                this.text = 'Weeks'
                                break
                            case 'Month':
                                this.text = 'Months'
                                break
                            case 'Year':
                                this.text = 'Years'
                                break
                        }
                    })
                }
                else {
                    // singular
                    frequency_select.find('option').each(function() {
                        switch(this.text) {
                            case 'Weeks':
                                this.text = 'Week'
                                break
                            case 'Months':
                                this.text = 'Month'
                                break
                            case 'Years':
                                this.text = 'Year'
                                break
                        }
                    })
                }
                scope.recurring_preview(el)
            })


            /**
             * Repeating frequency onChange event
             */
            $(document).on('change', 'select[name="frequency"]', function() {
    
                const el = $(this).parents("form"),
                    opt_1 = el.find('.freq_7')
        
                switch($(this).val())
                {
                    case '7':
                        opt_1.addClass('active')
                        break
    
                    case '30':
                        opt_1.removeClass('active')
                        break
    
                    case '365':
                        opt_1.removeClass('active')
                        break
                }
                scope.recurring_preview(el)
            })
    

            /**
             * Update Preview on any change
             * 
             */
            $(document).on('change', 'input[name="frequency_week_days"]', function() {
                
                let el = $(this).parents("form"),
                    selected_date = el.find('input[name="repeat_start"]').val()

                if( ! selected_date ) {
                    return
                }
                // Make sure that the default day coming form the datepicker is always checked.
                $(`#_week_days_${moment(selected_date).day()}`).prop("checked", true)

                scope.recurring_preview(el)
            })

        })

    },


    /**
     * Next Repeat Preview
     *  
     *  Get the next repeat form the recurring settings.
     * @param {Element} el 
     * @return Void
     */
    recurring_preview(el) {

        const preview = el.find('.recurring_settings_preview'),
              repeat_next = el.find('input[name="repeat_next"]'),
              params = myapp.form.collectInputs(el.find('.recurring_options'))

        axios.get(`/sources/events/get-repeat-next-date`, {
            params: params
        })
        .then(function (response) {
        
            // repeating ended
            let next_date = response.data.data

            if( !next_date ) {
                preview.html('<small>Next repeat</small><strong style="color:#ca0000">Repeating ended</strong>')
                repeat_next.val('')
                return
            }

            preview.html(`<small>Next repeat</small><strong>${moment(next_date, `YYYY-MM-DD`).format(`(ddd) MMM DD, YYYY`)}</strong>`)
            repeat_next.val(next_date)
            return

        })
        .catch(function (error) {
            
            if(error.response.status == 422) {
                console.log([error])
            }
            else {
                console.log([error])
            }

            preview.html('')
            repeat_next.val('')
            return false
        }) 
    },



    /**
     * Clear the date Fields
     * 
     * @param {*} el 
     */
    clearDateFields(el) {

        if(!el) {
            return
        }

        el.find('input[name="repeat_start"]').val('')
        el.find('input[name="repeat_next"]').val('')
        // el.find('input[name="repeat_next"]').val('')

        el.find('.recurring_settings_preview').html('')
        return
    }
    

}

myapp.recurringSetting.init()
