/*
|--------------------------------------------------------------------------
| Retail Deals
|--------------------------------------------------------------------------
*/
'use strict'


if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.retailDeals = {

	Deals: document.getElementById('retailDeals'),


	init: function() {
		this.registerEvents()
	},


	/**
     * Retail Deals (click functions)
     *  - Open the More info/redeem modal
     *  - Redeem button click event
     */
    registerEvents: function() {

        let self = this

        $(document).on('click', '[data-open-deal]', function() {
            self.open($(this).attr('data-open-deal'))
        })

        if(!self.Deals) return
        
        // Front-End: Expand, Redeem
        self.Deals.querySelectorAll('.col-deal').forEach(el => {
    
            el.addEventListener("click", function() {
                self.expand(this)
            })

            el.querySelector('[name="redeem"]').addEventListener("click", function() {
                self.redeem(this.value)
            })
        })
    },


    /**
     * Close the deal popup window
     *  - close with close button and escape key
     *  - Add arrow navigation
     * @param {*} el 
     */
    registerCloseEvt: function(el) {
        
        let self = this
        let close_btn = el.querySelector('._close')

        close_btn.addEventListener("click", function(e) {
            e.stopPropagation() 
            el.classList.remove('expanded')
        }, {once : true})

        document.addEventListener("keydown", function(evt) {
            evt = evt || window.event

            if (evt.key == 'Escape') {
                el.classList.remove('expanded')
            }
            if (evt.key == 'ArrowRight') {
                self.expand(el.nextElementSibling)
            }
            if (evt.key == 'ArrowLeft') {
                self.expand(el.previousElementSibling)
            }
        }, {once : true})

       return
    },
    

    /**
     * Open (Expand) the deal
     * @param {*} el - deal to expand
     */
    expand: function(el) {

        this.Deals.querySelectorAll('.col-deal').forEach(el => {
            el.classList.remove('expanded')
        })

        el.classList.add('expanded')

        // Scroll to the deal
        setTimeout(function() {
            el.querySelector('.deal').scrollIntoView({behavior: "smooth", block: "center"})
        }, 100)

        // Register the close event
        this.registerCloseEvt(el)
    },

	

    /**
     * Open the deal popup window
     */
    open: function(deal_id) {

        if(!deal_id)
            return
        
        let modal = $('#mod-retail-deal')

        axios.get(`/admin/retail-deal/get/${deal_id}`)
            .then(function(response) {

                if(!response.data)
                    return false

                myapp.modal.fillThenOpen(modal, response.data.data)
                return
            })
            .catch(e => _errorResponse(e))
    },


    /**
     * Redeem a deal by deal id
     */
    redeem: function(deal_id) {

        let self = this
        let deal = document.querySelector(`.col-deal[data-id="${deal_id}"]`)

        if(!deal_id)
            return

        axios.post(`retail-deals/redeem`, {
            deal_id: deal_id
        })
        .then(function(response) {

            if(!response.data)
                return false

            let msg = `
                <div class="success-resp row">
                    <div class="col align-middle">
                        <i class="icon-check icon-lg"></i>
                        <div>
                            <h3>Congratulations</h3>
                            <p>${response.data.message}</p>
                            <p>Here is your redeem code: <strong>${response.data.data.code}</strong></p>
                        </div>
                    </div>
                </div>`

            // store the original content for restoring it after closing the popup.
            let content_to_restore = deal.querySelector(`.deal_redeem`).innerHTML

            // Show the success response
            deal.querySelector(`.deal_redeem`).innerHTML = msg.allTrim()

            // Case when no more redeems left
            if(response.data.data.remaining_redeem === 0) {

                // on close: 
                deal.querySelector('._close').addEventListener('click', function() {
                    // remove item from DOM
                    deal.remove()
                }, {once : true})
            }

            // Case when we have more redeems
            else {

                // on close:
                deal.querySelector('._close').addEventListener('click', function() {
                    // Restore the deal body to the before submission state.
                    deal.querySelector(`.deal_redeem`).innerHTML = content_to_restore
                    // re-register the redeem click event
                    deal.querySelector('[name="redeem"]').addEventListener("click", function() {
                        self.redeem(this.value)
                    }, {once : true})
                    // Refresh the counter
                    if(response.data.data.label) {
                        deal.querySelector('._counter').innerHTML = response.data.data.label
                    }
                }, {once : true})

            }
            return
        })
        .catch(e => _errorResponse(e))
    }
}

myapp.retailDeals.init()