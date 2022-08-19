/*
|--------------------------------------------------------------------------
| Resident Booking Calendar
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.ResidentBooking = {


    /**
     * Init
     */
    init: () => {

        const scope = myapp.ResidentBooking
        
        const BookingCalendar = document.getElementById("bookingCalendar")


        // init slick slider for the booking steps
        window.slider = scope.initBookingSteps()

        // Go back button, for slick slider
        $('button.booking__go_back').on('click', function() {
            let page = this.dataset.page || 0
            slider.slick('slickGoTo', page)
            // 
            if(this.dataset.enableCartButtons) {
                myapp.cart.setAddCartButtons('enable')
            }
        })


        if(!BookingCalendar) {
            return
        }

        const form        = BookingCalendar.parentNode,
              date_start  = form.querySelector('input[name="date_start"]'),
              date_end    = form.querySelector('input[name="date_end"]'),
              time_start  = form.querySelector('select[name="time_start"]'),
              time_end    = form.querySelector('select[name="time_end"]'),
              // get the UI elements
              ui_date     = document.getElementById('booking_date'),
              ui_length   = document.getElementById('booking_length'),
              in_length   = form.querySelector('input[name="booking_length"]'),
              in_length_unit = form.querySelector('input[name="booking_length_unit"]'),
              // for hire booking 
              ui_payment_date = document.querySelector('.payment_date')

        // const qty_value = qty ? Number(qty.value) : 1;
        // Register the slider navigation events
        $('button[name="complete_booking"]').on('click', function() {
            // get qty
            const qty =  form.querySelector('select[name="booking_qty"]'), qty_value = qty ? Number(qty.value || 1) : 1;

            // update the booking summary UI
            scope.updateBookingSummary(ui_date, time_start, time_end, ui_length)

            scope.calculatePrice(qty_value, in_length.value, in_length_unit.value)

            // go to next slide
            slider.slick('slickGoTo', 1)
        })

        
        window.ResidentBooking = flatpickr("#bookingCalendar", {
            altInput: true,
            altFormat: "M j, Y",
            dateFormat: "Y-m-d",
            minDate: "today",
            maxDate: moment().add(3, 'months').format('YYYY-MM-DD'), // Maximum future booking time period to 3 months from the current day.
            mode: "range",
            inline: true,
            shorthandCurrentMonth: true,
            locale: {
                "firstDayOfWeek": 1 // start week on Monday
            },

            /**
             * On change function
             * : update the interface 
             * @param {*} selectedDates 
             * @param {*} dateStr 
             * @param {*} instance 
             */
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
                    // $("#bookingDetailsServiceTab").collapse('show');
                    // $("#booking_date_service").html(scope._formatDate(start, end));
                    scope.fixSliderHight();
                    $("button[name='continue_order']").attr('disabled', false)
                    return
                }

                /**
                 * Build the validated TimePickers **/ 
                myapp.booking.validateDate({
                    item_path: window.location.href,
                    start: _start,
                    end: _end,
                    time_start_el: time_start
                })

                //_set date
                ui_date.innerHTML = scope._formatDate(start, end)

                // $("#bookingDetailsTab").collapse('show')

                $("button[name='continue_order']").attr('disabled', false)

                $("button[name='complete_booking']").attr('disabled', false)
                // disable 'complete_booking' when available_qty <= 0
                const _available_qty = $("input[name='_available_qty']").val();
                if(_available_qty && Number(_available_qty) <= 0) {
                    $("button[name='complete_booking']").attr('disabled', true)
                }

                // Update slider hight after content changes.
                myapp.booking.fixSliderHight(slider)
            },

            // on Month change
            onMonthChange: function(selectedMonth, dateStr, instance) {

                // Get the date setting for this period
                const start_str = (instance.currentMonth + 1) +'/1/' + instance.currentYear,
                      start = moment(start_str, 'l').add(-1, 'weeks').format('YYYY-MM-DD')
                      end   = moment(start_str, 'l').add(6, 'weeks').format('YYYY-MM-DD')

                scope.setCalendar(start, end)
            },

            // on Year change
            onYearChange: function(selectedMonth, dateStr, instance) {
                
                // Get the date setting for this period
                const start_str = (instance.currentMonth + 1) +'/1/' + instance.currentYear,
                      start = moment(start_str, 'l').add(-1, 'weeks').format('YYYY-MM-DD')
                      end   = moment(start_str, 'l').add(6, 'weeks').format('YYYY-MM-DD')

                scope.setCalendar(start, end)
            },
        })


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
            item_url: window.location.href,
            calculate_price: false
        })

        // init the PDF previewer
        scope.initPDFPreviewer()
        // init the signature box
        scope.initSignatureBox()
    },

    

    /**
     * 
     *init slick slider for the booking steps
     */
    initBookingSteps() {

        const slider = $('#BookingForm')

        if(slider.length == 0) {
            return
        }

        slider.slick({
            infinite: false,
            dots: false,
            arrows: false,
            autoplay: false,
            initialSlide: 0,
            speed: 450,
            cssEase: 'cubic-bezier(0.86, 0, 0.07, 1)',
            draggable: false,
            swipe: false,
            slidesToShow: 1,
            adaptiveHeight: true
        })
        .on('beforeChange', function(e, slick, currentSlide, nextSlide) {
            
            slider.find('[data-slick-index="'+nextSlide+'"]').height('auto')
            slider.find('.slick-list').height('auto')
            slider.slick('setOption', null, null, true)
            
            //return false
        })

        return slider
    },




    /**
     * Update the Booking Summary Ui
     * 
     */
    updateBookingSummary(date, from, to, length) {

        const bs_date = document.querySelector('.booking-summary ._date'),
              bs_time = document.querySelector('.booking-summary ._time'),
              _cleaning_fee = $('#_cleaning_fee')
            
        bs_date.innerHTML = date.innerHTML
        bs_time.innerHTML = `${$(from).find("option:selected").html()} - ${$(to).find("option:selected").html()} (${length.innerHTML})`

        // update chleaning fee UI
        const booking_cleaning_fee = $('input[name="booking_cleaning_fee"]:checked');
        let _booking_cleaning_fee_value = $(booking_cleaning_fee).val();
        if(_booking_cleaning_fee_value) {
            _booking_cleaning_fee_value = JSON.parse(_booking_cleaning_fee_value);
            _cleaning_fee.html(`Cleaning fee (${_booking_cleaning_fee_value.name}) <span>$${NumberFormat(_booking_cleaning_fee_value.fee)}</span>`);
            // update _total UI
            const _total_elm = $('._total');
            let _total_data = Number(_total_elm.data('total') || 0);
            _total_data += Number(_booking_cleaning_fee_value.fee || 0);
            _total_elm.html(`$${NumberFormat(_total_data)}`);
        }
    },


    /**
     * Update the Booking Calculation for Hire bookings
     * 
     * @param int qty
     * @param el length
     * 
     * @return void
     */
    calculatePrice(qty = 1, length, length_unit) {

        const costing = document.getElementById('booking_hire_costing')

        if(!costing) return

            // interface elements
        const qtyUi       = costing.querySelector('._qty'),
              lengthUi    = costing.querySelector('._length'),
              subtotalUi  = costing.querySelector('._subtotal'),
              totalUi     = costing.querySelector('._total'),
            // hidden inputs
            qtyIn         = costing.querySelector('input[name="_qty"]'),
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
            qtyUi.innerHTML = qty
            lengthUi.innerHTML = document.getElementById('booking_length').innerHTML
            subtotalUi.innerHTML = '$' + NumberFormat(subtotal)
            totalUi.innerHTML =  '$' + NumberFormat(total)

            // Fill-in hidden inputs
            qtyIn.value = qty

            return
    },






    /**
     * Format date 
     * 
     * @param {date} start - moment inst
     * @param {date} end - moment inst
     */
    _formatDate(start, end) {

        let format = 'D MMM, YYYY',
            start_format = format,
            end_format = format

        // same year
        if (start.format('YYYY') == end.format('YYYY')) {
            
            start_format = 'MMM D'
    
            // same month
            if (start.format('MMM') == end.format('MMM')) {

                end_format = 'D, YYYY'
                
                // same day
                if (start.format('D') == end.format('D')) {
                    return start.format(format)
                }
            }
        }
    
        return `${start.format(start_format)} - ${end.format(end_format)}`
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
	 * Init PDF Previewer
     * 
     * @return el - pdfList
	 */
	initPDFPreviewer() {

        let checked_terms = []

        const pdfList = document.getElementById('terms-list')
                   
        if(!pdfList) 
            return

        if(pdfList.getAttribute('lg-uid') != 'undefined' && pdfList.getAttribute('lg-uid')) {
            window.lgData[pdfList.getAttribute('lg-uid')].destroy(true)
        }

        lightGallery(pdfList, {
            selector: '.term-link',
            thumbnail: false,
            download: false,
            loop: false,
            controls: false,
            enableDrag: false,
            keyPress: false,
            closable: false
        })

        pdfList.addEventListener('onAfterAppendSubHtml', function(event) {
            
            /** Init Accept terms onClick events */
            const AcceptButton = document.querySelectorAll('input[name="accept_term"]')

            let is_last = true

            if(typeof window.lgData.lg1 != 'undefined') {
                is_last = event.detail.index == (window.lgData.lg1.items.length - 1)
            }
            
            AcceptButton.forEach(
                button => button.addEventListener('change', function() {
                    
                    if(pdfList.getAttribute('lg-uid') != 'undefined' && pdfList.getAttribute('lg-uid')) {
                        
                        // add this to the checked terms
                        checked_terms.push(this.value)

                        if(!is_last) {
                            // go to next
                            window.lgData[pdfList.getAttribute('lg-uid')].goToNextSlide()
                        }
                        else {

                            // All terms are checked?
                            // if checked terms length is same as all terms length
                            if(1 == 1) {
                            
                                const accepted_terms_input = document.querySelector('input[name="accepted_terms"]')
                                
                                // Pass the check terms, and set it to be checked
                                accepted_terms_input.value = JSON.stringify(checked_terms)
                                accepted_terms_input.checked = true
                                // trigger change event
                                accepted_terms_input.dispatchEvent( new Event("change") )
                            }
                            else {
                                // Set invalid (message: something is still unchecked)
                            }

                            // close the term slider
                            window.lgData[pdfList.getAttribute('lg-uid')].destroy()
                            checked_terms = [] // reset
                        }

                    }

                })
            )
        }, false)

          
        // init the Open
        const OpenTermsBtn = document.getElementById('open_terms')

        OpenTermsBtn.addEventListener('click', function() {
            document.querySelector('#terms-list > li:first-child > a').click()
        })

        //
        return pdfList
    },


    /**
     * init the Signature Box
     */
    initSignatureBox() {

        const canvas = document.getElementById('resident_signature')
        
        if(!canvas) return false

        const signaturePad = new SignaturePad(canvas, {
            penColor: "rgb(22, 6, 131)",
            throttle: 8,
            onBegin: () => {
                bodyScrollLock.disableBodyScroll(document.querySelector('body'))
            },
            onEnd: () => {
                bodyScrollLock.enableBodyScroll(document.querySelector('body'))
            }
        })

        const clearButton = document.querySelector(".clearSignature"),
               undoButton = document.querySelector(".undoSignature")

        // register clear action
        clearButton.addEventListener("click", function (event) {
            signaturePad.clear()
        })
        // register undo action
        undoButton.addEventListener("click", function (event) {
            var data = signaturePad.toData()
        
            if (data) {
                data.pop()
                signaturePad.fromData(data)
            }
        })


        /* Fix resizing */
        function resizeCanvas() {
  
            var ratio =  Math.max(window.devicePixelRatio || 1, 1)
          
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
          
            signaturePad.clear()
        }
          
        //window.onresize = resizeCanvas
        resizeCanvas()

        window.BookingSignature = signaturePad

    },



    /**
     * Set the calendar 
     * 
     * @param {date} start
     * @param {date} end
     * @param {str} item_url - bookable_item_action path
     * @param {int} exclude_booking_id - Exclude the passed booking form the validations 
     * 
     * @return void
     */
    setCalendar(start, end, item_url = window.location.href, exclude_booking_id = '') {

        if(!start && !end) {
            return
        }
        
        axios.post(item_url + '/get-dates-in-period', {
                start: start, 
                end: end,
                exclude_booking_id: exclude_booking_id
            })
            .then(function (response) {

                if(!response.data.data) {
                    return false
                }

                const item = response.data.data.item,
                      data = response.data.data.dates 
                
                let cal = ResidentBooking;

                if(item.type > 2) {
                    // service type is 4
                    if(item.type === 4 && (item.service && item.service.is_date === 3)) { // timeslot selection
                        const _service_office_hours = JSON.parse(item['office_hours'] ? item['office_hours'] : item['building']['office_hours']);
                        const _is_service_date_time = !!(item['service'] && item['service']['is_date'] === 2);
                        if(!_is_service_date_time){ // check disable only timeslot selection
                            let _disable_by_office_hours = [];
                            item.NAME_OF_DATE.map((v, k)=> {
                                if(_service_office_hours[v]['status'] == 1){
                                    _disable_by_office_hours = [..._disable_by_office_hours, k == 6 ? 0 : k + 1]
                                }
                            });
                            const _disable_by_office_hours_ = date => {
                                return !_disable_by_office_hours.includes(date.getDay());
                            }
                            cal.config.disable = [_disable_by_office_hours_];
                            cal.redraw()
                        }
                    }
                    return false
                }

                const allow_multiday = item.hire ? item.hire.allow_multiple : item.room.allow_multiday,
                      max_length_of_booking = item.hire ? item.hire.booking_max_length : item.room.booking_max_length 

                let _disabled_dates = Object.values(data['unavailable']),
                    _disabled_dates__with_resident =  _disabled_dates.concat(Object.values(data['disabled_duplicate_book_same_date_by_resident'])),
                    _disabled_dates_with_range = _disabled_dates.concat(Object.values(data['disabled_for_full_range']))

                // const disable_office_date = date => Object.values(data['disabled_date_by_office_hours']).map(v => Number(v)).includes(date.getDay());
                const disable_office_date = date => null;


                // Calendar Settings
                cal.config.mode = allow_multiday == true ? 'range' : 'single'
                // cal.config.disable = _disabled_dates
                cal.config.disable = [..._disabled_dates__with_resident, disable_office_date]
                cal.config.dateFormat = "Y-m-d"

                // onDayCreate
                cal.config.onDayCreate.push(function(dObj, dStr, fp, dayElem) {

                    let date = dayElem.getAttribute('aria-label')
                        date = moment(dayElem.getAttribute('aria-label'), 'MMMM DD, YYYY').format('YYYY-MM-DD')

                    if( _disabled_dates.includes(date) ) {
                        if($(dayElem).hasClass('flatpickr-disabled')) {
                            $(dayElem).addClass('full_availability').removeClass('flatpickr-disabled');
                        }
                        dayElem.innerHTML += "<span class='day-status full'></span>";
                    } else if( data['low_availability'].includes(date) ) {
                        if($(dayElem).hasClass('flatpickr-disabled')) {
                            $(dayElem).addClass('low_availability').removeClass('flatpickr-disabled');
                        }
                        dayElem.innerHTML += "<span class='day-status low'></span>";
                    }
                })

                let a = [];

                // onChange
                cal.config.onChange.push(function(dObj, dStr, fp, dayElem) {
                    // show alert when they try to click on a calendar date that is blocked out (due to other bookings),
                    if([...data['low_availability'], ..._disabled_dates].includes(dStr)) {
                        $("#bookingDetailsTab").collapse('hide');
                        $("button[name='complete_booking']").attr('disabled', true);
                        sc.alert.show('alert-danger', 'This day is booked out. Please choose a different day.', 5000);
                        return false;
                    }

                    // check if select is outside business hours, don't allow the booking
                    const _today = moment();
                    const _select_date = moment(dStr);
                    const _office_hours_dates = data['disabled_date_by_office_hours'];
                    const today_date_is_outside_office_hours = _office_hours_dates.includes(String(_today.day())) // If today's date is outside office hours
                    const is_outside_hours = _office_hours_dates.map(v => Number(v)).includes(dObj[0].getDay()); // if selected booking date is outside booking hours
                    const duration_today_and_select_date = moment.duration(_select_date.diff(_today)).asHours() < 120; // if selected booking date is less than 120hrs from today's date
                    if (today_date_is_outside_office_hours && is_outside_hours && duration_today_and_select_date) {
                        $("#bookingDetailsTab").collapse('hide');
                        $("button[name='complete_booking']").attr('disabled', true);
                        sc.alert.show('alert-danger', 'Unable to proceed with booking. Please book during office hours.', 5000);
                        return false;
                    }

                    $("#bookingDetailsTab").collapse('show');
                
                    if(cal.config.mode !== 'range') return 

                    if(a.length < 2) {

                        if(a.length == 0) {

                            if(allow_multiday && max_length_of_booking) {

                                const a_selectable_range = moment(dStr).add((max_length_of_booking + 24), 'hours').format('YYYY-MM-DD')
                                const b_selectable_range = moment(dStr).add(-(max_length_of_booking + 24), 'hours').format('YYYY-MM-DD')
                                
                                cal.config.disable = [..._disabled_dates_with_range, a_selectable_range, b_selectable_range, disable_office_date]
                            }
                            else {
                                cal.config.disable = [..._disabled_dates_with_range, disable_office_date]
                            }
                        }
                        else {
                            cal.config.disable = [..._disabled_dates, disable_office_date]
                        }
                        a.push(dStr)
                    }
                    else {
                        a = [dStr]
                        if( ! _disabled_dates_with_range.includes(dStr) ) {

                            if(allow_multiday && max_length_of_booking) {
                                
                                const a_selectable_range = moment(dStr).add(Number(max_length_of_booking) + 24, 'hours').format('YYYY-MM-DD')
                                const b_selectable_range = moment(dStr).add( - Number(max_length_of_booking) +24, 'hours').format('YYYY-MM-DD')
                                
                                cal.config.disable = [..._disabled_dates_with_range, a_selectable_range, b_selectable_range, disable_office_date]
                            }
                            else {
                                cal.config.disable = [..._disabled_dates_with_range, disable_office_date]
                            }
                        }
                    }
                    cal.redraw()
                })

                cal.redraw()
                return
            })
            .catch(e => _errorResponse(e))



    }


}

myapp.ResidentBooking.init()