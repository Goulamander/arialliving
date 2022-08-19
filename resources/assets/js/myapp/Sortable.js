/*
|--------------------------------------------------------------------------
| Sortable init
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.sortable = {

    /**
     * Init
     */
    init() {

        // Gallery
        const galleryList = document.getElementsByClassName('_image_gallery')

        Array.prototype.forEach.call(galleryList, list => {
            
            new Sortable(list, {
                // group: 'shared',
                animation: 150,
                onUpdate: function (/**Event*/evt) {

                    let order = [],
                        elements = []

                    this.el.querySelectorAll('._img').forEach(el => {
                        order.push(el.dataset.file)
                        elements.push(el)
                    })

                    // Store the order
                    myapp.sortable.storeGalleryOrder(elements, order)
                },
            })
        })

        // PDF Terms re-ordering
        const pdfList = document.getElementById('terms-list')

        if(pdfList) {

            new Sortable(pdfList, {
                animation: 150,
                onUpdate: function(evt) {

                    let order = [],
                        elements = []

                    this.el.querySelectorAll('._pdf').forEach(el => {
                        order.push(el.dataset.file)
                        elements.push(el)
                    })

                    myapp.sortable.storePDFOrder(elements, order)
                },
            }) 
        }

        // Settings/Category re-ordering
        const categoryList = document.getElementById('categoryList')

        if(categoryList) {

            new Sortable(categoryList, {
                handle: ".drag-handle",
                animation: 150,
                onUpdate: function(evt) {

                    let order = []

                    this.el.querySelectorAll('tr').forEach(el => {
                        order.push(el.dataset.id)
                    })

                    myapp.sortable.storeCategoryOrder(order)
                },
            }) 
        }
    },



    /**
     * Store the new Order
     * @param {*} order 
     */
    storeGalleryOrder(elements, order) {

        axios.post(`/files/order-gallery-images`, {
            order: order
        })
        .then(function (response) {

            if( !response.data.data ) {
                return false
            }

            elements.forEach(el => {
                el.dataset.file = response.data.data[el.dataset.file]
            })

            sc.alert.show('alert-success', 'Order updated')
            return
        })
        .catch(e => _errorResponse(e))
            
    },



    /**
     * Store the PDF attachments order
     */
    storePDFOrder(elements, order) {


        axios.post(`/files/order-pdf-terms`, {
            order: order
        })
        .then(function (response) {

            if( !response.data.data ) {
                return false
            }

            elements.forEach(el => {
                let new_file_path = response.data.data[el.dataset.file]

                el.dataset.file = new_file_path
                el.querySelector('.term-link').href = '/storage/' + new_file_path
            })

            sc.alert.show('alert-success', 'Order updated')
            return
        })
        .catch(e => _errorResponse(e))
    },



    /**
     * Store the Category Order
     */
    storeCategoryOrder(order) {

        axios.post(`/admin/settings/category/store-order`, {
            order: order
        })
        .then(function (response) {
            sc.alert.show('alert-success', response.data.message)
            return
        })
        .catch(e => _errorResponse(e))
    }


}
myapp.sortable.init()