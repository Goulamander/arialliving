/*
|--------------------------------------------------------------------------
| Resident's interface 
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.residentInterface = {

    /**
     * Init
     */
    init: () => {

        const scope = myapp.residentInterface

        // init top navigation onScroll events
        scope.initTopNavScroll()

        // init the Card sticky scrolling
        const cardWrap = document.getElementById('booking-card--wrap')
        
        if(!cardWrap)
            return
            
        window.StickyBookingCard = new StickySidebar(cardWrap, {
                containerSelector: '.booking-single--body',
                innerWrapperSelector: '.card-inner-wrap',
                topSpacing: 90,
                bottomSpacing: 20,
                resizeSensor: true,
                stickyClass: 'is-affixed',
                minWidth: 991
            })

    },




    /**
     *   Sticky Navigation Header
     */
    initTopNavScroll() {

        const hero = document.querySelector(".aria-hero")
        const header = document.querySelector(".top_navbar._resident")

        if( !hero ) {
            return
        }

        const add_class_on_scroll = () => header.classList.add("scrolled")
        const remove_class_on_scroll = () => header.classList.remove("scrolled")

        document.addEventListener('scroll', function() {
            if (window.pageYOffset >= 340) {
                add_class_on_scroll() 
            }
            else { 
                remove_class_on_scroll() 
            }
        }) // --

        window.addEventListener('load', function() {
            if (window.pageYOffset >= 340) {
                add_class_on_scroll() 
            }
            else { 
                remove_class_on_scroll() 
            }
        }) // --
        
    },


}

myapp.residentInterface.init()