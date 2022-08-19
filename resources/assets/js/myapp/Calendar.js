/*
|--------------------------------------------------------------------------
| Calendar
|--------------------------------------------------------------------------
*/

if (typeof myapp === 'undefined') {
    myapp = {}
}

/** 
 * Define Initial State for the calendar
 * 
 */
let CalendarFilters = {
    category: ['all'], // Room Booking / Event / Hire   
    building: ['all'], // list of buildings
    item: ['all'] // list of items
}


myapp.calendar = {

    /**
     * Init
     */
    init: function() {

        const scope = myapp.calendar

        $('.modal').on('shown.bs.modal', function () {
            hideAllTippy()
        })

        scope.registerEvents()


        /* Read cookies, Set Filters
        ----------------------------------------------------------------- */
        let filter_settings = getCookie("CalendarSettings") ? JSON.parse(atob(getCookie("CalendarSettings"))) : ''
            CalendarFilters = filter_settings ? filter_settings : CalendarFilters
    
        let CalendarSource = scope.createSourceURL()
    
        

        /* initialize the calendar
        -----------------------------------------------------------------*/
        let jsonEventSource


        const BusinessOpen = '05:00:00',
              BusinessClose = '22:00:00'


            
        document.addEventListener('DOMContentLoaded', function() {

            const calendarEl = document.getElementById('calendar')

            // add booking click
            $(document).on('click', '#add_booking', e => {
                scope.addManualBooking({
                    dateStr: moment().format('YYYY-MM-DD'),
                    dayEl: $('#add_booking')[0],
                });
            })
          
            if(!calendarEl) {
                return
            }

            const calContentHeight = document.documentElement.clientHeight - 140

            window.AdminCalendar = new Calendar(calendarEl, {
                dayMaxEventRows: true, // for all non-TimeGrid views
                plugins: [ 
                    dayGridPlugin, 
                    timeGridPlugin, 
                    listPlugin, 
                    interactionPlugin,
                    momentPlugin
                ],
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'dayGridMonth,timeGridWeek,listWeek prev,next'
                },

                views: {
                    week: { 
                    //   headerFormat: 'ddd D MMM',
                    //   titleFormat: { year: 'numeric', month: '2-digit', day: '2-digit' }
                    },
                    timeGrid: {
                        dayMaxEventRows: 6, // adjust to 6 only for timeGridWeek/timeGridDay
                    }
                },
                firstDay: 1,
                selectable: true,
                editable: false,
                droppable: false,
                expandRows: true,
                handleWindowResize: true,
                fixedWeekCount: false,
                contentHeight: calContentHeight,
                nowIndicator: true,
                displayEventEnd: true,
                eventTimeFormat: { 
                    hour12: true, 
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short',
                    omitZeroMinute: true,
                },

                // Get the events
                events: function (date, successCb, failureCb) {
                
                    const start = moment(date.startStr).format('YYYY-MM-DD'),
                            end = moment(date.endStr).format('YYYY-MM-DD')
                
                    axios.get(CalendarSource, {
                            params: {
                                start: start, 
                                end: end
                            }
                        })
                        .then(function (response) {
     
                            if(!response.data.events) {
                                return false
                            }
                            
                            if(response.data.events.building) {    
                                // todo
                                AdminCalendar.setOption('businessHours', [
                                    {
                                        daysOfWeek: [ 1 ], // Monday, Tuesday, Wednesday
                                        startTime: '08:00', // 8am
                                        endTime: '18:00' // 6pm
                                    },
                                    {
                                        daysOfWeek: [ 2, 3,  4, 5 ], // Thursday, Friday
                                        startTime: '10:00', // 10am
                                        endTime: '16:00' // 4pm
                                    }
                                ])   
                            }
                            successCb(response.data.events.events)        
                        })
                        .catch(function (error) {
                            failureCb(console.log(error))
                        })
                },
            
                // Open booking
                eventClick: function(data) { 
                    // Open the booking Card
                    myapp.booking.open(data.event.id, data.el)
                },

                eventDidMount:function(info){
                    let fc_event_title = $(info.el).find('.fc-event-title');
                    fc_event_title && fc_event_title.length > 0 && $(fc_event_title[0]).html($(fc_event_title[0]).text());
                    // $(info.el).children('.fc-event-title').html($(info.el).children('.fc-event-title').text())
                },

                // Open new booking
                dateClick: function(data) {
                    window.isAdmin && scope.addManualBooking(data); // only admin to show add manual

                    //
                    // const date_formatted = moment(data.dateStr).format('MMM DD, YYYY')

                    // const tippy_content = `
                    //     <div id="new-calendar-item" class="new-calendar-item">
                    //         <strong>${date_formatted}</strong>
                    //         <h3>Add booking to <b>${Laravel.calendar.default_building.name}</b></h3>
                    //         <div class="form-group">
                    //             <label class="control-label">Select item to make the booking for</label>
                    //             <select class="form-control" data-s2="1" data-container-id=".tippy-content" data-source="item" data-return="id" data-building-id="${Laravel.calendar.default_building.id}">
                    //                 <option value="">Select</option>
                    //             </select>
                    //         </div>
                    //         <div class="form-group">
                    //             <label class="control-label">Select resident or admin</label>
                    //             <select class="form-control" data-s2="1" data-container-id=".tippy-content" data-source="user" data-return="id" data-building-id="${Laravel.calendar.default_building.id}">
                    //                 <option value="">Select</option>
                    //             </select>
                    //         </div>
                    //         <button type="button" class="btn btn-primary btn-sm btn-round btn-arrow float-right">Next <i class="material-icons">arrow_forward</i></button>
                    //     </div>`
                        
                    // tippy(data.dayEl, {
                    //     appendTo: () => document.body,
                    //     interactive: true,
                    //     maxWidth: 'none',
                    //     content: tippy_content,
                    //     allowHTML: true,
                    //     trigger: 'click',
                    //     theme: 'light',
                    //     showOnCreate: true ,
                    //     onShow(instance) {

                    //         // init the s2 pickers
                    //         myapp.select2.init($(instance.popper))

                    //         hideAllTippy({ exclude: instance })
                        
                    //     },
                    // })
                },

                viewSkeletonRender: function() {
                    console.log('hello')
                }

            })
        
            AdminCalendar.render()
        })


    },


    addManualBooking: data => {
        const date_formatted = moment(data.dateStr).format('DD MMM, YYYY')
        let booking_type = 0;

        $(document).on('change', '#booking_type', e => {
            booking_type = Number($('#booking_type').val() || 0);
        })

        const tippy_content = `
            <div id="new-calendar-item" class="new-calendar-item">
                <form method="POST" id="updateBooking" action="/admin/booking/add-manual" data-reload="true">
                    <button type="button" class="_close"><i class="material-icons">close</i></button>
                    <!--<strong class="booking_date">${date_formatted}</strong>-->
                    <!--<h3>Add booking to <b>${Laravel.calendar.default_building.name}</b></h3>-->
                    <h3>Add booking</h3>
                    <div class="form-group">
                        <label class="control-label">Select item to make the booking for</label>
                        <select class="form-control" name="bookable_item_id" id="bookable_item_id" data-s2="1" data-container-id=".tippy-content" data-source="item" data-return="id" data-building-id="all" required>
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Select resident or admin</label>
                        <select class="form-control" name="user_id" id="user_id" data-s2="1" data-container-id=".tippy-content" data-source="user" data-return="id" data-building-id="${Laravel.calendar.default_building.id}">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div id="bookingDetailsTab">
                        <div class="row">
                            <div class="col">
                                <small class="d-block">Booking dates</small>
                                <input type="hidden" class="form-control" id="date_start" name="date_start" value="${data.dateStr}">
                                <div id="bookingCalendar" class="edit-booking d-flex mt-3 mb-2">
                                    <strong class="booking_date">${date_formatted}</strong>
                                    <i class="material-icons">keyboard_arrow_down</i>
                                </div>
                            </div>
                            <div class="col">
                                <small>From</small>
                                <input type="time" class="form-control" id="time_start" name="time_start" required>
                            </div>
                            <div class="col">
                                <small>To</small>
                                <input type="time" class="form-control" id="time_end" name="time_end" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="checkbox text-left mb-0">
                            <input type="checkbox" id="cleaning_required" name="cleaning_required" class="" value="1" data-parsley-multiple="cleaning_required" checked>
                            <label for="cleaning_required">Cleaning required</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-round btn-arrow float-right">Next <i class="material-icons">arrow_forward</i></button>
                </form>
            </div>`
            
        window.booking_tippy = tippy(data.dayEl, {
            content: $(tippy_content)[0],
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
                instance.destroy()
            },
            onShow(instance) {
                myapp.select2.init($(instance.popper))
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
            }
        });

        window.ResidentBooking = flatpickr("#bookingCalendar", {
            altFormat: "M j, Y",
            dateFormat: "Y-m-d",
            altInputClass: "edit-booking",
            defaultDate: data.dateStr,
            // mode: calendarOpts.mode,
            minDate: "today",
            shorthandCurrentMonth: true,
            static: true,
            position: "right",
            locale: {
                "firstDayOfWeek": 1 // start week on Monday
            },
            onChange: function(selectedDates, dateStr, instance) {
    
                // single date
                let start = moment(selectedDates[0]),
                        end = start

                // date-range 
                if( typeof selectedDates[1] !== 'undefined' ) {
                    end = moment(selectedDates[1])
                }

                const _start = start.format('YYYY-MM-DD');
                
                $('#date_start').val(_start)
    
                //_set date
                $('.booking_date').html(myapp.ResidentBooking._formatDate(start, end));
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
    },


    /**
     * Calendar Filter (click functions)
     * 
     */
    registerEvents: () => {

        const filters = $('#calendar_filters'),
              building_filter = filters.find('[name="building_filter"]'),
              category_filter = filters.find('[name="category_filter"]'),
              item_filter = filters.find('[name="item_filter"]')


        // Set the initial state for the filters from the cookie
        let filter_state = getCookie("CalendarSettings") ? JSON.parse(atob(getCookie("CalendarSettings"))) : ''
        
        if(filter_state) {

            if( filter_state['category'] ) {
                category_filter.find('option').each(function() {
                    if(filter_state['category'].includes(this.value)) {
                        this.selected = 'selected'
                    }
                })
            }

            if( filter_state['building'] ) {
                building_filter.find('option').each(function() {
                    if(filter_state['building'].includes(this.value)) {
                        this.selected = 'selected'
                    }
                })
            }

            if( filter_state['item'] ) {
                item_filter.find('option').each(function() {
                    if(filter_state['item'].includes(this.value)) {
                        this.selected = 'selected'
                    }
                })
            }
        }


        building_filter.on('change', function() {
            _filterCalendar()
            _updateNewBookingModal(this)
            _updateItemFilter(this, item_filter)
        })

        category_filter.on('change', function() {
            _filterCalendar()
        }) 

        item_filter.on('change', function() {
            _filterCalendar()
        }) 
       

        $(document).on('click', '.fc-button-group > button', function(e) {

            hideAllTippy({duration: 0})
        })

        /** --  */

        /**
         * Update the Building inside the New Booking modal 
         */
        function _updateNewBookingModal(filter) {

            let building_id   = filter.value,
                building_name = filter.options[filter.selectedIndex].innerHTML
                  
            // Update the Laravel Obj
            Laravel.calendar.default_building.id = building_id
            Laravel.calendar.default_building.name = building_name
        }


        /**
         * Update the item filter's dropdown options when the building is changing
         * 
         * @param {el} building_filter
         * @param {el} item_filter
         */
        function _updateItemFilter(building_filter, item_filter) {

            axios.get('/sources/items', {
                    params: {
                        building_id: building_filter.value == 'all' ? '' : building_filter.value
                    }
                })
                .then(function (response) {

                    if( !response.data ) return

                    let options = response.data.map(function(group) {

                            let opts = `<option value="all">All items</option>`

                            if(group.children.length) {

                                opts += `<optgroup label="${group.text}">`
                                opts += group.children.map(function(item) {
                                        return `<option value="${item.id}">${item.title}</option>`
                                    }).join('')
                                opts += `</optgroup>`
                            }
                            return opts

                        }).join('')

                    if(options) {
                        item_filter.empty().append(options)
                        return
                    }
                    
                    item_filter.empty().append(`<option value="all">All items</option>`)
                    return

                })
                .catch(e => _errorResponse(e))
        }


        /**
         * Filter the Calendar
         */
        function _filterCalendar() {

            let filters = []

            let buildings = building_filter.val() || 'all',
                categories = category_filter.val() || 'all',
                items = item_filter.val() || 'all'

            // push filter update into Filters array
            CalendarFilters['category'] = [categories]
            CalendarFilters['building'] = [buildings]
            CalendarFilters['item'] = [items]
 
            // set cookie
            setCookie('CalendarSettings', btoa(JSON.stringify(CalendarFilters)), 1)

            // update calendar
            myapp.calendar.CalendarRefetch()
        }


    },




    /** Helper */
    _encodeQueryData: function(data) {
        const ret = [];
        for (let d in data)
          ret.push(encodeURIComponent(d) + '=' + encodeURIComponent(data[d]));
        return ret.join('&');
    },


    /**
     *  Create Source URL
     * 
     */
    createSourceURL: function() {

        let Route = ''

        if(CalendarFilters) {
            Route = this._encodeQueryData(CalendarFilters)
        }

        Route = Route ? Route + '&isLocal' : ''

        return '/sources/calendar?' + Route
    },



    /**
     * Update Calendar
     */
    CalendarUpdate: () => { 
        const NewCalendarSource = myapp.calendar.createSourceURL()
        Calendar.fullCalendar('refetchEvents', NewCalendarSource)
    },


    
    /**
     * ReFetch the Calendar
     */
    CalendarRefetch: () => {
        const NewCalendarSource = myapp.calendar.createSourceURL()

        let allEvent = AdminCalendar.getEventSources()

        allEvent.forEach((el) => {
           el.remove()
        })

        AdminCalendar.addEventSource(function (date, successCb, failureCb) {
                
            const start = moment(date.startStr).format('YYYY-MM-DD'),
                    end = moment(date.endStr).format('YYYY-MM-DD')
        
            axios.get(NewCalendarSource, {
                    params: {
                        start: start, 
                        end: end
                    }
                })
                .then(function (response) {
            
                    if(!response.data.events) {
                        return false
                    }

                    successCb(response.data.events.events)            
                })
                .catch(function (error) {
                    failureCb(console.log(error))
                })
        })
     
    }
        
}

myapp.calendar.init()