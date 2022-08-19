/*
|--------------------------------------------------------------------------
| Init / GlobalSearch.js
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}

myapp.globalSearch = {

    /**
     * Init GlobalSearch.js 
     */
    init(el = null) {
        let globalSearch = myapp.globalSearch;

        $('document').ready(function () {

            /**
             * Search
             */
            $('#global-search').on('keyup', globalSearch.delay(function (e) {
                globalSearch.getSearchData(this.value)
            }))

        })
    },

    /**
     * Get Search data 
     */
    getSearchData(data) {
        $.ajax({
            url: `/items-search?search=${data}`,
            type: "GET",
            success: function (data) {
                $('#items-content').html(data);
                if (myapp.carousel) {
                    myapp.carousel.init()
                    // myapp.carousel.initBookingGallerySlider()
                }
            },
            fail: function (e) {
                _errorResponse(e)
            }
        });
    },

    /**
     * Delay input 
     */
    delay(callback, ms = 500) {
        var timer = 0;
        return function () {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(context, args);
            }, ms || 0);
        };
    }
}


myapp.globalSearch.init()