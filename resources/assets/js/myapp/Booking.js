/*
|--------------------------------------------------------------------------
| Booking
|  - Resident's interface booking related functions
|--------------------------------------------------------------------------
*/

if (typeof myapp === 'undefined') {
    myapp = {}
}



myapp.booking = {

    /**
     * Init
     */
    init: function() {

        const scope = myapp.booking

        window.addEventListener('load', (event) => {
            
            const openBookingId = sessionStorage.getItem('openBooking') ? sessionStorage.getItem('openBooking') : ''
            
            if(!openBookingId) {
                return
            }
            // _clear
            sessionStorage.removeItem('openBooking')

            scope.openForResident(openBookingId, true)
            return

        })


        /** Open a booking */
        $(document).on('click', '[data-open-booking]', function() {

            const bookingId = this.dataset.openBooking

            if(!bookingId) {
                return
            }

            scope.openForResident(bookingId) 
        })

        /** Cancel a booking */
        $(document).on('click', '[data-cancel-booking]', function() {

            const bookingId = this.dataset.cancelBooking

            if(!bookingId) {
                return
            }

            scope.cancelBooking(bookingId) 
        })


        
        /**
         * Update the pricing on Attendees Number change
         * 
         *  for create booking
         *  for update booking
         * 
         *  @return void
         */
        $(document).on('change', 'input[name="attendees_num"]', function() {

            const attendees_num = this.value || 1,
                  form = $(this).parents('form'),
                  admin_fee = form[0].querySelector('[name="admin_fee"]')
                  totalUi   = form[0].querySelector('._total')

            if( !admin_fee ) {
                return
            }

            // _calc the new price
            const newTotal = parseInt(attendees_num) * parseInt(admin_fee.value)
            //
            totalUi.innerHTML = '$' + NumberFormat(newTotal)
            return
        })



        $("#addCard").on('show.bs.collapse', function() {
            scope.fixSliderHight()
        })
        $("#addCard").on('hide.bs.collapse', function() {
            scope.fixSliderHight()
        })



    },






    /**
     * Open a booking
     * 
     * @param {*} id - booking ID
     * 
     */
    open: function(id, tippy_el = false, hide_booking_button = false) {
        
        let modal

        if(tippy_el === false) {

            modal = document.getElementById('mod-booking')
            
            cleanModal(modal)
            $(modal).modal('show')
        }  
       
        // Get data and fill-in
        axios.get(`/admin/booking/${id}/get`)
            .then(function (resp) {

                if(!resp.data.data) {
                    return false
                }

                if(tippy_el === false) {
                    // get the template
                    myapp.templates.booking.renderBooking(modal, resp.data.data, hide_booking_button)
                }
                else {
           
                    let template = myapp.templates.booking.renderBooking(false, resp.data.data, hide_booking_button)
                        template = $.parseHTML(template)

                    if(id == window.open_booking_id) {
                       return
                    }
                
                    if( window.booking_tippy && window.booking_tippy.state.isDestroyed == false) {
                        window.booking_tippy.destroy()
                    }

                    window.booking_tippy = tippy(tippy_el, {
                        content: $(template)[1],
                        allowHTML: true,
                        interactive: true,
                        appendTo: () => document.body,
                        delay: [0, 500],
                        trigger: 'click',
                        theme: 'light',
                        animation: 'shift-toward-subtle',
                        showOnCreate: true,
                        hideOnClick: 'toggle',
                        maxWidth: 'none',
                        placement: 'auto',
                        zIndex: 9999,
                        onHidden(instance) {
                            //
                            instance.destroy()
                            //
                            delete window.open_booking_id   
                        },
                        onShow(instance) {

                            let closeBtn = this.content.querySelector('._close')

                            // Close tippy on close click 
                            closeBtn.addEventListener('click', function() {
                                instance.hide()
                            })
                            // Close tippy on escape button
                            document.addEventListener('keyup', function(e) {
                                if(e.key === "Escape") { 
                                    if(instance.state.isDestroyed === false) instance.hide()
                                }
                            })

                            hideAllTippy({ exclude: instance })
                            //
                            window.open_booking_id = id
                        }
                    })      
                }

                // do inits
                myapp.booking.bookingManagerOpened(resp.data.data)
                return false

            })
            .catch(e => _errorResponse(e))
            return false
    },




    /**
     * Open a booking (for Residents)
     * 
     * @param {*} id - booking ID
     * 
     */
    openForResident: function(id, show_response = false) {
    
        const modal = document.getElementById('mod-booking')
        // empty all contents
        cleanModal(modal)

        $(modal).modal('show')

        // Get data and fill-in
        axios.get(`/booking/${id}`)
            .then(function (resp) {
                
                if(!resp.data.data) {
                    return false
                }

                // get the template
                myapp.templates.booking.renderResidentBooking(modal, resp.data.data, show_response, false)

                myapp.booking.bookingManagerOpened(resp.data.data)
                return false

            })
            .catch(e => _errorResponse(e))
            return false
    
    },

    /** Card Opened callback */
    opened: function() {


    },




    /**
     * Booking manager Opened
     *  init scripts
     */
    bookingManagerOpened: function(data) {

        const scope = myapp.booking

        //init the Booking Calendar
        scope.initBookingCalendar({
            item_url: `/items/${data.type_label_url}/${data.bookable_item_id}`,
            start: data._calendar_start, 
            end: data._calendar_end,
            exclude_booking_id: data.id,
            defaultDate: data._calendar_default_date,
            mode: data._calendar_mode
        })

        // re-init tippy
        const newTippies = document.querySelector('.manage__booking').querySelectorAll('[data-tippy-content]')

        tippy(newTippies, {
            appendTo: 'parent',
            hideOnClick: true,
            trigger: 'mouseenter',
            theme: 'translucent',
            animation: 'shift-toward-subtle',
        })
        return
    },


    /**
     * init the Booking Calendar for managing Bookings
     * 
     */
    initBookingCalendar: function(calendarOpts) {

        const form       = document.getElementById('updateBooking'),
              date_start = form.querySelector('input[name="date_start"]'),
              date_end   = form.querySelector('input[name="date_end"]'),
              time_start = form.querySelector('select[name="time_start"]'),
              time_end   = form.querySelector('select[name="time_end"]'),
              // get the UI elements
              ui_date    = document.getElementById('booking_date'),
              ui_length  = document.getElementById('booking_length'),
              in_length  = form.querySelector('input[name="booking_length"]'),
              in_length_unit = form.querySelector('input[name="booking_length_unit"]'),
              // for hire booking 
              ui_payment_date = form.querySelector('.payment_date')


        window.ResidentBooking = flatpickr("#bookingCalendar", {
            altFormat: "M j, Y",
            dateFormat: "Y-m-d",
            altInputClass: "edit-booking",
            defaultDate: calendarOpts.defaultDate,
            mode: calendarOpts.mode,
            minDate: "today",
            shorthandCurrentMonth: true,
            static: true,
            position: "right",
            locale: {
                "firstDayOfWeek": 1 // start week on Monday
            },
            //
            onReady: function(dates, dateStr, f) {
          
                // single date
                let start = moment(dates[0]),
                      end = start

                // date-range 
                if( typeof dates[1] !== 'undefined' ) {
                    end = moment(dates[1])
                }

                const _start = start.format('YYYY-MM-DD'),
                        _end = end.format('YYYY-MM-DD')

                // update the hidden inputs
                date_start.value = _start
                date_end.value = _end

                // for Service orders, we don't need to run the time validation
                if( !ui_date ) {
                    return
                }
                
                /**
                 * Build the validated TimePickers **/ 
                if(time_start && time_end) {
                    myapp.booking.validateDate({
                        item_path: calendarOpts.item_url,
                        start: _start,
                        end: _end,
                        time_start_el: time_start,
                        exclude_booking_id: calendarOpts.exclude_booking_id
                    })
                }
                return
            },
            //
            onChange: function(selectedDates, dateStr, instance) {
    
                // single date
                let start = moment(selectedDates[0]),
                      end = start

                // date-range 
                if( typeof selectedDates[1] !== 'undefined' ) {
                    end = moment(selectedDates[1])
                }

                const _start = start.format('YYYY-MM-DD'),
                        _end = end.format('YYYY-MM-DD')

                // update the hidden inputs
                date_start.value = _start
                date_end.value = _end

                // for Service orders, we don't need to run the time validation
                if( !ui_date ) {
                    return
                }

                /**
                 * Build the validated TimePickers **/
                if(time_start && time_end) {

                    myapp.booking.validateDate({
                        item_path: calendarOpts.item_url,
                        start: _start,
                        end: _end,
                        time_start_el: time_start,
                        exclude_booking_id: calendarOpts.exclude_booking_id
                    })
                }

    
                //_set date
                ui_date.innerHTML = myapp.ResidentBooking._formatDate(start, end)
                return
            },
            //
            onMonthChange: function(selectedMonth, dateStr, instance) {

                // Get the date setting for this period
                const start_str = (instance.currentMonth + 1) +'/1/' + instance.currentYear,
                      start = moment(start_str, 'l').add(-1, 'weeks').format('YYYY-MM-DD')
                      end   = moment(start_str, 'l').add(6, 'weeks').format('YYYY-MM-DD')

                myapp.ResidentBooking.setCalendar(start, end, calendarOpts.item_url, calendarOpts.exclude_booking_id)
            },
            //
            onYearChange: function(selectedMonth, dateStr, instance) {
                
                // Get the date setting for this period
                const start_str = (instance.currentMonth + 1) +'/1/' + instance.currentYear,
                      start = moment(start_str, 'l').add(-1, 'weeks').format('YYYY-MM-DD')
                      end   = moment(start_str, 'l').add(6, 'weeks').format('YYYY-MM-DD')

                myapp.ResidentBooking.setCalendar(start, end, calendarOpts.item_url, calendarOpts.exclude_booking_id)
            },
        })

        /** Set the calendar */
        myapp.ResidentBooking.setCalendar(calendarOpts.start, calendarOpts.end, calendarOpts.item_url, calendarOpts.exclude_booking_id)


        // init From (time picker events)
        myapp.booking.timeStartOnChange(time_start, time_end)

        // init To (time picker events)
        myapp.booking.timeEndOnChange({
            date_start: date_start,
            date_end: date_end,
            time_start: time_start,
            time_end: time_end,
            ui_length: ui_length,
            in_length: in_length,
            in_length_unit: in_length_unit,
            ui_payment_date: ui_payment_date,
            item_url: calendarOpts.item_url,
            calculate_price: true
        })
        return
    },


    /**
     * Validate a date or date-range
     * 
     * This function will attach the validated from|to dropdown options to DOM
     * - Run: date picker onChange (also on Booking editing onReady)
     * 
     * @param {*} data {item_path, start, end, timePickerElement}
     * @return {void}
     */
    validateDate(data) {

        axios.post(data.item_path + "/validate-date", {
                start: data.start,
                end: data.end,
                exclude_booking_id: data.exclude_booking_id || ''
            })
            .then(function (response) {

                if(!response.data.data) {
                    return false
                }

                const timeObj = response.data.data

                // build the dropdown options
                let from_options = ``

                if( timeObj[data.start] && timeObj[data.start].time_dropdown_options ) {
                    from_options = timeObj[data.start].time_dropdown_options.map(function(opt) {
                        return `<option value="${opt['value']}" data-min="${opt['min']}" data-max="${opt['max']}">${moment(opt['value'], "HH:mm:ss").format('hh:mm a')}</option>`
                    }).join("")
                }

                // insert option
                if(from_options) {

                    let picker = $(data.time_start_el)

                    // _get the selected value (for booking updates)
                    let selected = picker.find('option[data-selected]') ? picker.find('option[data-selected]').attr('value') : ''

                    // _insert options
                    picker.html(from_options)

                    // _select default value for the booking updates
                    if( selected && picker.find(`option[value="${selected}"]`).length ) {
                        picker.val(selected)
                    }

                    // _ trigger update on the "To" time picker 
                    picker.trigger('change')
                }

            })
            .catch(e => _errorResponse(e))

    },



    /**
     * 
     * @param {*} time_start 
     * @param {*} time_end 
     */
    timeStartOnChange(time_start, time_end) {

        $(time_start).change(function() {

            let range_from = this.options[this.selectedIndex].dataset.min,
                  range_to = this.options[this.selectedIndex].dataset.max

            if(!range_from || !range_to) return

            // moment parse
            range_from = moment(range_from, "HH:mm:ss")
            range_to = moment(range_to, "HH:mm:ss")

            // Create the "to" options
            let to_options = `<option value="${range_from.format('HH:mm:ss')}">${range_from.format('hh:mm a')}</option>`

            if(range_from.format('HH:mm:ss') != range_to.format('HH:mm:ss')) {

                // create the 15min slots
                for ($i = 0; $i <= 94; $i ++) {
                    // increase by 15min
                    range_from = range_from.add(15, 'm')
                    //
                    to_options += `<option value="${range_from.format('HH:mm:ss')}">${range_from.format('hh:mm a')}</option>`
                    //
                    if(range_from >= range_to) break
                }
            }

            let picker = $(time_end)
            
            // _get the selected value (for booking updates)
            let selected = picker.find('option[data-selected]') ? picker.find('option[data-selected]').attr('value') : ''
   
            // _insert options
            picker.html(to_options)

            if(selected && picker.find(`option[value="${selected}"]`).length) {
                picker.val(selected)
            } 
            // Trigger change for updating booking length and pricing
            picker.trigger('change')
            
        })

    },


    /**
     * To - Time picker field onChange event.
     *  - This function updates the booking length in the UI, and also in the hidden inputs
     *  - For the Hire bookings it also request and inserts the Booking Cancellation Cut-Off date
     * 
     * @param {*} obj
     * @return {void} 
     */
    timeEndOnChange(obj) {

        $(obj.time_end).change(function() {
            
            try {
                const start = moment(obj.date_start.value+' '+obj.time_start.value, "YYYY-MM-DD HH:mm:ss"),
                        end = moment(obj.date_end.value+' '+this.value, "YYYY-MM-DD HH:mm:ss")

                // calc the difference
                duration = moment.duration(end.diff(start))

                let length = duration.asHours(),

                    length_HTML = '',
                    length_unit = ''

                if(length >= 24) {
                    // in days
                    length = length / 24

                    length = length <= 0.5 ? 0.5 : round(length, 0.5)
                    length_unit = 'day'
                    length_HTML = length == 1 ? length+' day' : length+' days'
                }
                else {
                    // in hours
                    if( duration.get("minutes") > 0 ) {
                        length_HTML = Math.floor(duration.get("hours")) + ':' + duration.get("minutes") + 'min'
                    }
                    else {
                        length_HTML = length <= 1 ? length+' hr' : length+' hrs' 
                    }
                    length_unit = 'hour'
                }

                // update the ui
                obj.ui_length.innerHTML = length_HTML
                // update hidden inputs
                obj.in_length.value = length
                obj.in_length_unit.value = length_unit


                // Run the pricing update
                if(obj.calculate_price == true) {
                    myapp.booking.updatePricing(1, length, length_unit)
                }

                // For hire bookings get the Cancellation Cut Off
                if(!obj.ui_payment_date) {
                    return
                }

                axios.post(obj.item_url + '/get-cancellation-cut-off', {
                        booking_start: start
                    })
                    .then(function (response) {

                        if(!response.data.data) {
                            return false
                        }
                        obj.ui_payment_date.innerHTML = moment(response.data.data.cut_off_date).format('MMMM DD, YYYY')
                    })
                    .catch(e => _errorResponse(e))
            } 
            catch (error) {
                console.log(error)
                return 
            }
        })
    },


    /**
     * Update the pricing section on Date changes.
     * 
     */
    updatePricing(qty, length, length_unit) {

        const costing = document.getElementById('bookingSum')

        if(!costing) return

            // interface elements
        const subtotalUi  = costing.querySelector('._subtotal'),
              totalUi     = costing.querySelector('._total'),
    
            //qtyIn         = costing.querySelector('input[name="_qty"]'),
            priceIn       = costing.querySelector('input[name="_price"]'),
            price_unitIn  = costing.querySelector('input[name="_price_unit"]'),
            subtotalIn    = costing.querySelector('input[name="_subtotal"]'),
            bondIn        = costing.querySelector('input[name="_bond"]')

            // Adjust length if pricing and length unit isn't matching
            const price_unit = price_unitIn.value

            let adjusted_length = length

            if(length_unit != price_unit) {

                switch(price_unit) {
                    // length should be returned in days
                    case 'day':
                        if(length_unit == 'hour') {
                            adjusted_length = length / 24
                            // adjust length (for days, the smallest unit is .5)
                            adjusted_length = adjusted_length <= 0.5 ? 0.5 : round(adjusted_length, 0.5)
                        }
                        break;

                    // length should be returned in hours
                    case 'hour':
                        if(length_unit == 'day') {
                            adjusted_length = length * 24
                        }
                        break;    
                }
            }

            // Calc Subtotal, total
            const subtotal = (qty * Number(priceIn.value)) * adjusted_length,
                     total = bondIn.value ? subtotal + Number(bondIn.value) : subtotal
                     
            // Fill-in Ui elements
            subtotalUi.innerHTML = '$' + NumberFormat(subtotal)
            totalUi.innerHTML =  '$' + NumberFormat(total)
            return
    },



    /**
     * Fix the hight of the slider when its content changes.
     * 
     * @param slider el
     * @return void
     */
    fixSliderHight() {

        if(!window.slider) {
            return
        }

        // todo: find a better way for fixing slick's hight
        slider.find('.slick-slide:not(.slick-active)').height('0')
        slider.find('.slick-slide.slick-active').height('0')
        slider.find('.slick-slide.slick-active').height('auto')
        slider.find('.slick-list').height('auto')
        slider.slick('setOption', null, null, true)
        return
    },


    /**
     *  Redirect User, and show the Booking
     */
    afterBookingSubmission(data) {

        if(data && data.id) {
            sessionStorage.setItem('openBooking', data.id);
            window.location.href = "/"
        }
    },


    /**
     * Cancel a booking
     * s
     * @param {*} bookingID 
     */
    cancelBooking(bookingID, $form = null) {

        if(!bookingID) {
            return
        }

        axios.post(`/booking/${bookingID}/cancel`, {
        })
        .then(function (response) {

            if(!response.data.data) {
                return false
            }
            
            // refresh the page
            sessionStorage.setItem('success', response.data.message);
            window.location.reload()
        })
        .catch(e => _errorResponse(e))
    }











}

myapp.booking.init()