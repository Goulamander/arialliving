/*
|--------------------------------------------------------------------------
| Booking Templates
|--------------------------------------------------------------------------
*/
if (typeof myapp.templates === 'undefined') {
    myapp.templates = {}
}


myapp.templates.booking = {

    /**
     * Admin
     * 
     * Render the View/Manage Modal content
     * 
     * @param {el} modal
     * @param {obj} data - booking obj
     * 
     * @return void
     * 
     */
    renderBooking(modal, data, hide_booking_button) {
 
        if(!data) {
            return
        }

        // Booking can be edited ?
        let can_edit = false;

        switch(data.type) {
            // room 
            case 1:
                can_edit = data.status == 1 ? true : false
                break
            // hire
            case 2:
                can_edit = data.status == 1 ? true : false
                break
            // event
            case 3:
                // can_edit = false
                can_edit = data.status == 1 ? true : false
                break
            // service
            case 4:
                can_edit = (data.bookable_item.service.is_date && data.status == 1) ? true : false
                break
        }


        let template = `
        <div class="manage__booking">

            <button type="button" class="_close"><i class="material-icons">close</i></button>
            
            <div class="head">
                ${data.thumb ? `<img src="${data.thumb}" alt="${data.bookable_item.title}"/>` : ``}
                <h5>${data.number}</h5>
                <h3>${data.bookable_item.title}</h3>
                <span>${data.bookable_item.category.name}</span>
            </div>

            <div class="body">
                <form method="POST" id="updateBooking" action="/booking/${data.id}" data-reload="true">
                
                    <div class="booking-detail">
                        <div class="row">
                            <div class="col-sm-6 _detail">
                                <small>Booking for</small>
                                <span>
                                    ${data.user.first_name} ${data.user.last_name} ${data.user.is_flagged ? data.user.flagged_label : ``} <small>(${data.user.role.display_name})</small>
                                </span>
                            </div>
                            <div class="col-sm-2 _detail">
                                <small>Unit No.</small>
                                <span>${data.user.unit_no || ``}</span>
                            </div>
                            <div class="col-sm-4 _detail">
                                <small>Booked on</small>
                                <span>${moment(data.created_at).format('DD MMM, YYYY')} <i class="icon-info" data-tippy-content="Last edited: ${moment(data.updated_at).format('DD MMM, YYYY')}"></i></span>
                            </div>
                        </div>

                        ${ // All types except Events or Services Orders that has no date field.
                            [1,2].includes(data.type) || (data.type == 4 && data.start) ? `
                        <div id="bookingDetailsTab">
                            <input type="hidden" name="date_start"/>
                            <input type="hidden" name="date_end"/>
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <small class="d-block">${data.bookable_item.service && data.bookable_item.service.date_field_name ? data.bookable_item.service.date_field_name : 'Booking dates'}</small>
                                    <div id="bookingCalendar" class="edit-booking ${can_edit ? `` : `disabled`}">
                                        <span id="booking_date">${data.date_formatted}</span>
                                        <i class="material-icons">keyboard_arrow_down</i>
                                    </div>
                                </div>
                                ${  // Service Orders has no Time fields
                                    data.type != 4 ? `
                                <div class="col-sm-4 col-xs-6">
                                    <small>${data.type == 2 ? `Pickup` : `From`}</small>
                                    <select class="form-control ${can_edit ? `` : `disabled`}" id="booking_from" name="time_start" tabindex="0">${data.start_time_option}</select>
                                </div>
                                <div class="col-sm-4 col-xs-6">
                                    <small>${data.type == 2 ? `Drop-off` : `To`}</small>
                                    <select class="form-control ${can_edit ? `` : `disabled`}" id="booking_to" name="time_end" tabindex="0">${data.end_time_option}</select>
                                </div>
                                <div class="col-sm-4 col-xs-12">
                                    <small>Length</small>
                                    <span id="booking_length" class="booking_length mt-2"></span>
                                    <input type="hidden" name="booking_length" tabindex="0" value="">
                                    <input type="hidden" name="booking_length_unit" tabindex="0" value="">
                                </div>
                                ` : ``}
                            </div>
                        </div>
                        ` : `
                        ${  // Event details
                            data.type == 3 ? `
                            <div class="row">
                                <div class="col-6">
                                    <small class="d-block">Event date & time</small>
                                    ${data.bookable_item.event.event_type == 1 ? `
                                        <span class="_date">${moment(data.bookable_item.event.event_date).format('DD MMM, YYYY')}</span>
                                    ` : `
                                        <span class="_date">${moment(data.bookable_item.recurring.repeat_next).format('DD MMM, YYYY')}</span>
                                    `}
                                    <span class="_time">${moment(data.bookable_item.event.event_date+' '+data.bookable_item.event.event_from).format('hh:mm a')}</span>
                                </div>
                                <div class="col-6">
                                    <small class="d-block">Location</small>
                                    ${data.bookable_item.event.location ? `
                                    <span class="_location">${data.bookable_item.event.location.title}</span>
                                    ` : `
                                    <span class="_location">${data.bookable_item.event.location_name}</span>
                                    `}
                                </div>
                            </div>` : ``}
                        `}`

                    template += `
                        <div class="row">
                            ${
                                data.type == 1 && data.user.is_admin ? `
                                    <div class="col-12 mt-4">
                                        <div class="checkbox text-left mb-0">
                                            <input type="checkbox" id="cleaning_required" name="cleaning_required" class="" value="1" data-parsley-multiple="cleaning_required" ${data.cleaning_required === 0 ? '' : 'checked'}>
                                            <label for="cleaning_required">Cleaning required</label>
                                        </div>
                                    </div>
                                ` : ''
                            }
                            ${
                                data.type == 1 && !data.user.is_admin && data.other_fee && data.other_fee.length > 0 ? `
                                    <div class="col-12 mt-4">
                                        <small class="text-muted">Cleaning Fee</small>
                                        ${data.other_fee.map(v => `<p>${v.name} - $${v.fee}</p>`)}
                                    </div>
                                ` : ''
                            }
                            <div class="col-12 mt-4">
                                <small class="text-muted">Resident Comments</small>
                                ${data.status === 1 ? `<textarea class="form-control" name="booking_comments" rows="3" style="height: auto !important">${data.booking_comments || ''}</textarea>` : `<p>${data.booking_comments}</p>`}
                            </div>
                        </div>
                    </div>
                    ${
                        window.isAdmin ? `
                            <div class="mt-3 booking-card-footer">
                                ${data.status == 1 ? `<button type="button" class="btn btn-sm btn-cancel" data-tippy-allowHTML="true" data-tippy-interactive="true" data-tippy-trigger="click" data-tippy-content="Are you cancelling this booking? <button type='button' data-cancel-booking='${data.id}' class='btn confirm_cancel_btn'>Yes, Cancel</button>">Cancel booking</button>` : ``}
                                ${can_edit ? `<button type="submit" name="store" class="btn btn-sm btn-primary float-right">Save changes</button>` : ``}
                                ${hide_booking_button ? `` : `<a href="/admin/booking/${data.id}" class="btn btn-sm btn-brand btn-arrow float-right">Booking <i class="material-icons">arrow_forward</i></a>`}
                            </div>
                        ` : ''
                    }

                </form>
            </div>
        </div>`

        if(modal) {
            modal.querySelector('.modal-body').innerHTML = template
            return
        }

        return template

    },



    /**
     * Resident
     * 
     * Render the View/Manage Modal content
     * 
     * @param {el} modal
     * @param {obj} data - booking obj
     * @param {bol} show_response - success response for the new bookings
     * 
     * @return void
     * 
     */
    renderResidentBooking(modal, data, show_response) {

        if(!data) {
            return
        }

        // Booking can be edited ?
        let can_edit = false;

        switch(data.type) {
            // room 
            case 1:
                can_edit = data.status == 1 ? true : false
                break
            // hire
            case 2:
                can_edit = data.status == 1 ? true : false
                break
            // event
            case 3:
                can_edit = false
                break
            // service
            case 4:
                can_edit = (data.bookable_item.service.is_date && data.status == 1) ? true : false
                break
        }


        let template = `
        <div class="manage__booking">
            
            ${show_response ? myapp.templates.booking.successResponse() : ``}
            <div class="head">
                ${data.thumb ? `<img src="${data.thumb}" alt="${data.bookable_item.title}"/>` : ``}
                <h5>${data.number}</h5>
                <h3>${data.bookable_item.title}</h3>
                <span>${data.type_label}</span>
            </div>

            <div class="body">
                <form method="POST" id="updateBooking" action="/booking/${data.id}">
                    <div class="booking-detail">
                        ${ // All types except Events or Services Orders that has no date field.
                            [1,2].includes(data.type) || (data.type == 4 && data.start) ? `
                        <div id="bookingDetailsTab">
                            <input type="hidden" name="date_start"/>
                            <input type="hidden" name="date_end"/>
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <small class="d-block">${data.bookable_item.service && data.bookable_item.service.date_field_name ? data.bookable_item.service.date_field_name : 'Booking dates'}</small>
                                    <div id="bookingCalendar" class="edit-booking ${can_edit ? `` : `disabled`}">
                                        <span id="booking_date">${data.date_formatted}</span>
                                        <i class="material-icons">keyboard_arrow_down</i>
                                    </div>
                                </div>
                                ${  // Service Orders has no Time fields
                                    data.type != 4 ? `
                                <div class="col-sm-4 col-xs-6">
                                    <small>${data.type == 2 ? `Pickup` : `From`}</small>
                                    <select class="form-control ${can_edit ? `` : `disabled`}" id="booking_from" name="time_start" tabindex="0">${data.start_time_option}</select>
                                </div>
                                <div class="col-sm-4 col-xs-6">
                                    <small>${data.type == 2 ? `Drop-off` : `To`}</small>
                                    <select class="form-control ${can_edit ? `` : `disabled`}" id="booking_to" name="time_end" tabindex="0">${data.end_time_option}</select>
                                </div>
                                <div class="col-sm-4 col-xs-12">
                                    <small>Length</small>
                                    <span id="booking_length" class="booking_length mt-2"></span>
                                    <input type="hidden" name="booking_length" tabindex="0" value="">
                                    <input type="hidden" name="booking_length_unit" tabindex="0" value="">
                                </div>
                                ` : ``}
                            </div>
                        </div>
                        ` : `
                        ${  // Event details
                            data.type == 3 ? `
                            <div class="row">
                                <div class="col-6">
                                    <small class="d-block">Event date & time</small>
                                    ${data.bookable_item.event.event_type == 1 ? `
                                        <span class="_date">${moment(data.bookable_item.event.event_date).format('DD MMM, YYYY')}</span>
                                    ` : `
                                        <span class="_date">${moment(data.bookable_item.recurring.repeat_next).format('DD MMM, YYYY')}</span>
                                    `}
                                    ${(data.bookable_item.event.event_type == 1 && data.bookable_item.event.event_from) ? `<small>@</small> <span class="_time">${moment(data.bookable_item.event.event_date+' '+data.bookable_item.event.event_from).format('hh:mm a')}</span>` : ''}
                                </div>
                                <div class="col-6">
                                    <small class="d-block">Location</small>
                                    ${data.bookable_item.event.location ? `
                                    <span class="_location">${data.bookable_item.event.location.title}</span>
                                    ` : `
                                    <span class="_location">${data.bookable_item.event.location_name}</span>
                                    `}
                                </div>
                            </div>` : ``}
                        `}`

                        // Pricing
                        switch(data.type) {

                            // Room
                            case 1:
                                // check whether this room booking has admin fee.
                                if(data.total && data.total > 0) {
                                    template += `
                                    <div id="bookingSum" class="booking-sum">
                                        <ul>
                                            <li>Admin fee <span>$${NumberFormat(data.bookable_item.admin_fee)}</span></li> 
                                            ${
                                                (data.other_fee && data.other_fee.length > 0) ? data.other_fee.map(v => {
                                                    return `<li>Cleaning fee (${v.name}) <span>$${NumberFormat(v.fee)}</span></li> `
                                                }) : ''
                                            }
                                        </ul>
                                        <h4>Your total: <span class="_total">$${NumberFormat(data.total)}</span></h4>
                                    </div>
                                    <div class="payment_notes">
                                        <span>Your payment will be charged on <b class="payment_date">${moment(data.cancellation_cutoff_date).format('DD MMM, YYYY')}</b></span><br>
                                        <small>${data.payment_note_sub}</small>
                                    </div>
                                    ${data.user.card ? `${myapp.templates.booking.creditCard(data.user.card)}` : ``}
                                    `
                                }
                                break

                            // Hire    
                            case 2:
                                let hired_item = JSON.parse(data.line_items)

                                // check whether this item has any fees
                                if(data.total && data.total > 0) {
                                    template += `
                                    <div id="bookingSum" class="booking-sum">
                                        <input type="hidden" name="_qty" value="${data.qty}"/>
                                        <input type="hidden" name="_price" value="${hired_item.price}"/>
                                        <input type="hidden" name="_price_unit" value="${hired_item.price_unit}"/>
                                        <input type="hidden" name="_subtotal" value="${data.subtotal}"/>
                                        <input type="hidden" name="_bond" value="${data.bond}"/>
                                        <ul>
                                            <li><strong class="_qty">${data.qty}</strong> x <b>${data.bookable_item.title}</b> <span>$${NumberFormat(hired_item.price)}<small>/${hired_item.price_unit}</small></span></li>
                                            <li>Your booking total (inc. GST) <span class="_subtotal">$${NumberFormat(data.subtotal)}</span></li>
                                        ${data.bond && data.bond > 0 ? `
                                            <li>Security deposit <span><i class="icon-info" data-tippy-content="${data.deposit_info}"></i> $${NumberFormat(data.bond)}</span></li>
                                        ` : ``}
                                        </ul>
                                        <h4>Charge amount: <span class="_total">$${NumberFormat(parseInt(data.total) + parseInt(data.bond))}</span></h4>
                                    </div>
                                    <div class="payment_notes">
                                        <span>Your payment will be charged on <b class="payment_date">${moment(data.cancellation_cutoff_date).format('DD MMM, YYYY')}</b></span><br>
                                        <small>${data.payment_note_sub}</small>
                                    </div>
                                    ${data.user.card ? `${myapp.templates.booking.creditCard(data.user.card)}` : ``}
                                    `
                                }
                                break   

                            // Event
                            case 3:
                                // Check whether this item has any fees
                                if(data.total && data.total > 0) {
                                    template += `
                                    <div id="bookingSum" class="booking-sum">
                                        <ul>
                                            <li>${data.event.attendees_num} attendee(s) x <span>Admin fee $${NumberFormat(data.bookable_item.admin_fee)}</span></li> 
                                        </ul>
                                        <h4>Your total: <span class="_total">$${NumberFormat(data.total)}</span></h4>
                                    </div>
                                    <div class="payment_notes">
                                        <span>Your payment will be charged on <b class="payment_date">${moment(data.cancellation_cutoff_date).format('DD MMM, YYYY')}</b></span><br>
                                        <small>${data.payment_note_sub}</small>
                                    </div>
                                    ${data.user.card ? `${myapp.templates.booking.creditCard(data.user.card)}` : ``}
                                    `
                                }
                                break
                                
                            // service    
                            case 4:
                                template += `
                                <div id="bookingSum" class="booking-sum">
                                    <h5 class="services-title">Ordered items</h5>
                                    <ul>
                                        ${Object.values(data.line_items).map(el => {
                                            return `<li><strong class="_qty">${el.qty}</strong> x <b>${el.name}</b> <span>$${NumberFormat(el.price)}</span></li>`
                                        }).join('')}
                                        <li>Subtotal (inc. GST) <span class="_subtotal">$${NumberFormat(data.subtotal)}</span></li>
                                        ${data.admin_fee ? `
                                        <li>Admin fee <span>$${NumberFormat(data.admin_fee)}</span></li>
                                        ` : ``}
                                        ${data.bond ? `
                                        <li>Security deposit <span>$${NumberFormat(data.bond)}</span></li>
                                        ` : ``}
                                    </ul>
                                    <h4>Your total: <span class="_total">$${NumberFormat(data.total)}</span></h4>
                                </div>`
                                break
                        }

                        // add the transactions
                        if(!can_edit && data.transactions) {
                            template += myapp.templates.booking.transactions(data)
                        }

        template += `
                    </div>
                    ${(can_edit && !show_response) ? `<button type="submit" name="store" class="btn btn-primary float-right">Save changes</button>` : ``}
                </form>
                ${(data.status == 1 && !show_response) ? `<button type="button" class="btn btn-small btn-cancel" data-tippy-allowHTML="true" data-tippy-interactive="true" data-tippy-trigger="click" data-tippy-content="Are you cancelling this booking? <button type='button' data-cancel-booking='${data.id}' class='btn confirm_cancel_btn'>Yes, Cancel</button>">Cancel booking</button>` : ``}
            </div>
        </div>`
        
        modal.querySelector('.modal-body').innerHTML = template
        return
    },



    /**
     * Render the credit card row
     */
    creditCard(card) {
        return template = `
            <div id="credit-card" class="credit-card">
                <div class="credit-card--row">
                    <img src="/img/${card.type_slug}-logo.jpg" alt="${card.type}"/>
                    <h3>${card.type} **** **** **** <strong>${card.end}</strong></h3>
                    <span class="card-exp">Expiry ${card.exp_month}/${card.exp_year} <span class="badge${card.exp_soon_class}">${card.expiry_in_str}</span></span>
                    <a href="/profile" class="btn btn-b btn-sm" data-tippy-content="Attached Credit Card can be changed in your profile settings. Click to proceed.">
                        Change Card
                    </a>
                </div>
            </div>`
    },



    /**
     * Render the transactions
     */
    transactions(data) {

        if(!data.transactions.length) {
            return ``
        }

        let template = `
        <h4>Payments</h4>
        <div class="transactions">
            ${data.transactions.map(tr => {
                return `
                <div class="_transaction">
                    <div class="row">
                        <div class="col-sm-3">
                            <small class="d-block">Transaction id</small> 
                            <span class="_id">${tr.transactionID}</span>
                        </div>
                        <div class="col-sm-3">
                            <small class="d-block">Status</small> 
                            <span class="_status">${tr.responseMessage}</span>
                        </div>
                        <div class="col-sm-3">
                            <small class="d-block">Amount</small> 
                            <span class="_amount">$${NumberFormat(tr.totalAmount)}</span>
                        </div>
                        <div class="col-sm-3">
                            <small class="d-block">Date</small> 
                            <span class="_amount">${moment(tr.created_at).format('DD MMM, YYYY')}</span>
                        </div>
                        <div class="col-sm-12">
                            <a href="#" class="d-block inline-link mt-2" data-get-pdf-receipt="${tr.id}"><i class="material-icons">file_download</i> Payment Receipt</a>
                        </div>
                    </div>
                </div>`
            }).join('')}
        </div>`
        return template
    },



    /**
     * Success template for the new bookings.
     * 
     * @param {int} type - booking type
     * @response {str} template
     */
    successResponse(type) {

        let response_msg = `Thank you for your Booking`
        if(type == 4) {
            response_msg = `Thank you for your Order`
        }

        return `
        <div class="booking-success">
            <i class="_icon icon-check"></i>
            <h3>${response_msg}</h3>
        </div>`
    }




}