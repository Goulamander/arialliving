/*
|--------------------------------------------------------------------------
| DateTme Pickers
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.dateTimePickers = {


    /**
     * Init
     */
    init: () => {

        // DatePickr inits
        flatpickr(".datePickr", {
            // date format
            altInput: true,
            altFormat: "M j, Y",
            dateFormat: "Y-m-d",
            positionElement: '.modal',
            static: true,
            onChange: function(selectedDates, dateStr, instance) {
            
                switch(instance.element.name) 
                {
                    /**
                     * Repeating Settings:
                     *  - Check the day of the selected date.
                     */
                    case 'repeat_start':

                        // Un-check the previous default day for the recurring / week days.
                        $('input[name="frequency_week_days"].primary')
                            .prop("checked", false)
                            .removeClass("primary")
    
                        // Check the new default day   
                        $(`#_week_days_${moment(dateStr).day()}`)
                            .prop("checked", true)
                            .addClass('primary')

                        // Set the min available date option in the repeat end dropdown
                        $(`input[name="repeat_end"]`)[0]
                            .flatpickr()
                            .set('minDate', dateStr)
                        return
                        break

                    default:
                        return
                }

            },
        })

                                    
        // Date Time Pickr inits
        flatpickr(".datetimePickr", {
            enableTime: true,
            // format
            altInput: true,
            altFormat: "M j, Y h:i K",
            dateFormat: "Y-m-d H:i:ss",	
        })

        // Time Pickr inits
        flatpickr(".timePickr", {
            noCalendar: true,
            enableTime: true,
            // format
            altInput: true,
            altFormat: "h:i K",
            dateFormat: "H:i:ss",
            positionElement: '.modal',
            static: true
        })

        // Time Pickr inits
        flatpickr(".time24Pickr", {
            noCalendar: true,
            enableTime: true,
            // format
            // altInput: true,
            // altFormat: "h:i",
            dateFormat: "H:i",
            positionElement: '.modal',
            static: true,
            time_24hr: true,
        })

        // Date Time Pickr inits
        flatpickr(".dateRangePickr", {
            // format
            altInput: true,
            altFormat: "M j, Y",
            dateFormat: "Y-m-d",
            mode: "range",
            allowInput: false
        })



        // Booking Calendar
        
        // window.ResidentBooking = flatpickr("#bookingCalendar", {
        //     altFormat: "M j, Y",
        //     dateFormat: "Y-m-d",
        //     inline: true,
        //     shorthandCurrentMonth: true,
        //     locale: {
        //         "firstDayOfWeek": 1 // start week on Monday
        //     }
        // })


        
        
    }
    



}

myapp.dateTimePickers.init()


