/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.cart = {

    /**
     * Init
     */
    init() {

        let scope = myapp.cart

        $(function() {

            /**
             * Add new line item button
             */
            $('.line-items button[name="add_to_cart"]').on('click', function() {

                const item_id = parseInt(this.value),
                          qty = parseInt($(this).parents('._item').find('[name="add_to_cart_qty"]').val() || 0)

                if(qty == 0)
                    return
                
                // do update
                scope.sync_changes({
                    id: item_id, 
                    action: "add",
                    data: {
                        qty: qty
                    }
                })

                // Ui
                this.innerHTML = `<i class="icon-like"></i> Added`
                this.classList.add(`_highlight`)

                setTimeout(() => {
                    this.innerHTML = `Add`
                    this.classList.remove(`_highlight`)
                }, 700)
                return
            })



            /**
             * Remove a cart item
             */
            $(document).on('click', '.cart-items ._remove', function() {
                
                const item_el = $(this).parents('._item'),
                      item_id = item_el.data('id')

                item_el.remove()

                // do update
                scope.sync_changes({
                    id: item_id, 
                    action: "remove"
                })


                /** ui */

                // fix slick slider height
                myapp.booking.fixSliderHight()

                const emptyState = `<div class="cart_empty_sate">
                        <h4>Add items to your cart</h4>
                    </div>`

                /**  Remove the empty state */
                const continueButton = document.querySelector('[name="continue_order"]'),
                    cartTotalSection = document.querySelector('.cart-sum')

                    console.log(document.querySelector('.cart-items > ._item'))

                if( ! document.querySelector('.cart-items > ._item')) {
                    // add the empty state
                    injectElement(document.querySelector('.cart-items'), emptyState)

                    // Enable submit button
                    continueButton.disabled = true
                    // Show the Cart Costing section
                    cartTotalSection.classList.add('hidden')
                    cartTotalSection.classList.remove('add_in')
                   // cartTotalSection.classList.add('add_in')
                }


                // update the totals
                scope.update_totals()
                return
            })


            /**
             * increase qty button
             */
            $(document).on('click', '.cart-items ._add', function(e) {
                
                const item_el = $(this).parents('._item'),
                      qty_input = $(this).prev('.cart_qty'),
                      qty = parseInt(qty_input.val()),
                      item_id = item_el.data('id')
                
                // +
                qty_input.val(qty + 1)

                // do update
                scope.sync_changes({
                    id: item_id,
                    action: "update",
                    data: {
                        qty: qty + 1
                    }})

                // update the totals
                scope.update_totals()
                return
            })


            /**
             * decrease qty button
             */
            $(document).on('click', '.cart-items ._minus', function(e) {
                
                const item_el = $(this).parents('._item'),
                      qty_input = $(this).next('.cart_qty'),
                      qty = parseInt(qty_input.val()),
                      item_id = item_el.data('id')
                    
                if(qty <= 1) 
                    return
                
                // -
                qty_input.val(qty - 1)

                // do update
                scope.sync_changes({
                    id: item_id, 
                    action: "update", 
                    data: {
                        qty: qty - 1
                    }})

                // update the totals
                scope.update_totals()                      
                return
            })


            $('button[name="continue_order"]').on('click', function() {

                // update the booking summary UI
                if(this.dataset.summary) {
                    scope.updateOrderSummary()
                }

                // disable the add item to cart buttons
                if( $(this).parents('.slick-slide').data('slick-index') == 0 ) {
                    scope.setAddCartButtons('disable')
                }
   
                // go to next slide
                window.slider.slick('slickGoTo', this.dataset.page)
            })

        })

    },


    /**
     * Sync the changes to the cart table.
     * 
     * @param {obj} obj - Item object
     * @return {bool}
     */
    sync_changes(obj) {

        // run this one async with await
        axios.post(window.location.href + '/sync-cart', {
            item_id: obj.id, 
            action: obj.action,
            qty:  obj.data ? obj.data.qty : ''
        })
        .then(function (response) {

            if(!response.data.data) {
                return false
            }

            if(response.data.data.line_item) {
                const line_item = response.data.data.line_item
                // update cart & the totals
                myapp.cart.add_item_UI(line_item, obj.data.qty)
            }
            
        })
        .catch(e => _errorResponse(e))
        return
    },



    /**
     * Update the Subtotal and total after cart changes
     * 
     */
    update_totals() {

        // Total fields
        const subtotal_el = $('.cart_subtotal'),
              admin_fee_el = $('.cart_admin_fee'),
              admin_bond_el = $('.cart_admin_bond'),
              total_el = $('.cart_total')

        // Cart items
        const cart_items = $('.cart-items > ._item')

        let total = 0,
            subtotal = 0

        cart_items.each(function() {
            let _price = parseFloat(this.dataset.price),
                _qty = parseInt($(this).find('.cart_qty').val())
            subtotal += _price * _qty
        })

        // _add the admin fee, if any
        if(admin_fee_el.length) {
            const admin_fee = parseFloat(admin_fee_el[0].dataset.price)
            total += subtotal + admin_fee
        }
        // _add the bond, if any
        else if(admin_bond_el.length) {
            const admin_bond = parseFloat(admin_bond_el[0].dataset.price)
            total += subtotal + admin_bond
        }
        else {
            total = subtotal
        }

        // _update the UI
        subtotal_el.attr('data-price', subtotal)
        subtotal_el.html('$' + NumberFormat(subtotal))

        total_el.attr('data-price', total)
        total_el.html('$' + NumberFormat(total))

        return
    },



    /**
     * Add the item to the Cart (interface only)
     * 
     * @param {obj} obj 
     * @param {int} qty  
     */
    add_item_UI(obj, qty) {

        // Find out if this item is in the cart already
        const item = $('.cart-items > [data-id="'+obj.id+'"]');

        // item found: update the QTY         
        if(item.length) {
            const qty_input = $(item).find('.cart_qty')
            //
            qty_input.val(parseInt(qty_input.val()) + qty) 
            // update totals
            myapp.cart.update_totals()
            return
        }

        // item not found: add
        const item_template = `
            <div data-id="${obj.id}" data-price="${obj.price || 0}" class="_item">
                ${obj.is_thumb ? `
                <img class="thumb" width="50" height="50" src="/storage/items/${obj.item_id}/line-items/${obj.id}.jpg" alt="${obj.name}"/>
                ` : ``}
                <div class="item_body">
                    <span class="item_name">${obj.name}</span>
                    <span class="item_price">${obj.price && parseFloat(obj.price) > 0 ? '$'+  NumberFormat(obj.price) : 'Free'}</span>
                </div>
                <div class="item_controls">
                    <button type="button" class="_minus btn btn-sm">-</button>
                    <input type="number" class="cart_qty" value="${qty}" min="1" max="100"/>
                    <button type="button" class="_add btn btn-sm">+</button>
                    <button type="button" class="_remove no-btn"><i class="icon-close"></i></button>
                </div>
            </div>`

        injectElement(document.querySelector('.cart-items'), item_template)
        
        // fix slick slider height
        myapp.booking.fixSliderHight()

        /**  Remove the empty state */
        const emptyState = document.querySelector('.cart_empty_sate'),
              continueButton = document.querySelector('[name="continue_order"]'),
              cartTotalSection = document.querySelector('.cart-sum')

        if( emptyState ) {
            // Remove the empty state
            removeElement(emptyState)

            // Enable submit button
            continueButton.disabled = false
            // Show the Cart Costing section
            cartTotalSection.classList.remove('hidden')
            cartTotalSection.classList.add('add_in')
        }

        // update totals
        myapp.cart.update_totals()
        return     
    },






    /** Service Order Summary */



    /**
     * Update the Order Summary Ui
     * 
     */
    updateOrderSummary() {

        const BookingForm = document.getElementById('BookingForm'),
                lineItems = BookingForm.querySelectorAll('.cart-items > ._item'),
                 subtotal = BookingForm.querySelector('.cart_subtotal').innerHTML,
                 adminFee = BookingForm.querySelector('.cart_admin_fee') ? BookingForm.querySelector('.cart_admin_fee').innerHTML : '',
               cart_total = BookingForm.querySelector('.cart_total').getAttribute("data-price"),
                    total = BookingForm.querySelector('.cart_total').innerHTML

        let serviceDate = BookingForm.querySelector('input[name="date_start"]')
             
        const itemsForSummary = []

        lineItems.forEach(el => {
            let item = {
                name:  el.querySelector('.item_name').innerHTML,
                price: el.querySelector('.item_price').innerHTML,
                qty:   el.querySelector('.cart_qty').value
            }
            itemsForSummary.push(item)
        })

     
        // inject content
        const bs_date = BookingForm.querySelector('.booking-summary ._date'),
              ServiceCosting = document.getElementById('booking_service_costing')

        let lineItemsHtml = itemsForSummary.map(el => {
            return `<li><strong class="_qty">${el.qty}</strong> x <b>${el.name}</b> <span>${el.price}</span></li>`
        }).join('')

        lineItemsHtml += `
        <li>Subtotal (inc. GST) <span class="_subtotal">${subtotal}</span></li>
            ${adminFee ? `<li>Admin fee <span>${adminFee}</span></li>` : ``}`
        
        // if service has date
        if(serviceDate) {
            serviceDate = moment(serviceDate.value, 'YYYY-MM-DD').format('MMMM DD, YYYY')
            bs_date.innerHTML = serviceDate
        }
     
        // 
        ServiceCosting.querySelector('ul.line-items').innerHTML = lineItemsHtml
        //
        ServiceCosting.querySelector('._total').innerHTML = total

        // show/hide _creditCard
        const credit_card_input = $('#credit-card ').find('input, select');
        if(Number(cart_total) > 0) {
            $('#credit-card').show();
            credit_card_input.each((k, e) => {
                $(e).attr('required', true);
            })
        } else {
            $('#credit-card').hide();
            credit_card_input.each((k, e) => {
                $(e).removeAttr('required');
            })
        }
        return
    },

    /**
     * Enable/Disable the Add Cart buttons 
     * 
     * @param {string} status 
     */
    setAddCartButtons(status) { 

        const lineItems = document.getElementById('cart_line_items')

        if(!lineItems)
            return
            
        let addToCartGroup = lineItems.querySelectorAll('.add_to_cart')

        if(!addToCartGroup)
            return
       
        addToCartGroup.forEach(el => {

            let btn = el.querySelector('button')

            if(status == 'disable') {
                btn.disabled = true
                btn.innerHTML = `<i class="icon-lock"></i> Add`
            }
            else {
                btn.disabled = false
                btn.innerHTML = `Add`
            }
        })
        return
        

      
        
    }





}

myapp.cart.init()