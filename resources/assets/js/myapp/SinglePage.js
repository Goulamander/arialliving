/*
|--------------------------------------------------------------------------
| Single/Page Methods (Booking, Bookable items)
|--------------------------------------------------------------------------
*/


if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.singlePage = {

    init() {

        let Scope = myapp.singlePage

        Scope.initGallery()
        Scope.initPDFList()
        Scope.initPDFPreviewer()

        Scope.publishItem()


        const is_signature_required = document.querySelector('#is_signature_required')

        if(is_signature_required) {
            is_signature_required.addEventListener('change', function() {
                
                SubmitForm($(this).parents('form'))
            })
        }

    },


    /**
     * Init the gallery
     */
    initGallery: function(el = '._image_gallery ._img') {

        let images = document.querySelectorAll(el)
        if(!images) return
            
        images.forEach(image => {
            image.querySelector('._delete').addEventListener('click', () => {
                myapp.singlePage.deleteImage(image, image.dataset.file)
            })
        })
    },


    /**
     * Init the PDF list 
     */
    initPDFList: function(el = '._pdf_list ._pdf') {

        let items = document.querySelectorAll(el)
        if(!items) return
            
        items.forEach(item => {
            // edit name button
            
            // delete button
            item.querySelector('._delete').addEventListener('click', () => {
                myapp.singlePage.deletePDF(item, item.dataset.file)
            })
            item.querySelector('._rename').addEventListener('click', () => {
                myapp.singlePage.renamePDF(item, item.dataset.file)
            })

        })
    },


    /**
     * Rename a file
     * 
     * @param {*} el 
     * @param {*} file_thumb_path 
     */
    renamePDF: function(el, file_thumb_path) {

        const modal = document.getElementById('mod-rename-file')

        if(!modal) return

        let clean_filename = getCleanPDFNameFromPath(file_thumb_path) 
   
        modal.querySelector('[name="new_name"]').value = clean_filename
        modal.querySelector('[name="file_path"]').value = file_thumb_path

        $(modal).modal('show')
        return
    },


    /**
     * Delete an image from gallery
     *  - remove from server
     *  - remove from interface
     * @param {*} file_thumb_path 
     */
    deleteImage: function(el, file_thumb_path) {

        if(!file_thumb_path)  return false

        axios.post('/files/remove-gallery-image', {
                thumb_path: file_thumb_path
            })
            .then(function (response) {

                if(!response.data) {
                    return false
                }

                $(el).hide(250, function() { 
                    $(el).remove() 
                })

                sc.alert.show('alert-success', response.data.message || 'Successful update')
                return
            
            })
            .catch(e => _errorResponse(e))
    },


    /**
     * Delete a pdf from the pdf list
     *  - remove from server
     *  - remove from interface
     * @param {*} file_path
     */
    deletePDF: function(el, file_path) {

        if(!file_path) return false

        axios.post('/files/remove-pdf-attachment', {
                file_path: file_path
            })
            .then(function (response) {

                if(!response.data) {
                    return false
                }

                $(el).hide(250, function() { 
                    $(el).remove() 
                })

                sc.alert.show('alert-success', response.data.message || 'Successful update')
                return
            
            })
            .catch(e => _errorResponse(e))
    },


    /**
     * 
     */
    moveFile(old_path, new_path) {

        if(!old_path || !new_path) return false

        axios.post('/files/move', {
                old_path: old_path,
                new_path: new_path
            })
            .then(function (response) {

                if(!response.data) {
                    return false
                }

                sc.alert.show('alert-success', response.data.message || 'Successful update')
                return
            
            })
            .catch(e => _errorResponse(e))
    },


    /**
	 * Init PDF Previewer
     * 
     * @return el - pdfList
	 */
	initPDFPreviewer() {

        let checked_terms = []

        const pdfList = document.getElementById('terms-list')
                   
        if(!pdfList) return
            

        if(pdfList.getAttribute('lg-uid') != 'undefined' && pdfList.getAttribute('lg-uid')) {
            window.lgData[pdfList.getAttribute('lg-uid')].destroy(true)
        }

        lightGallery(pdfList, {
            selector: '.term-link',
            thumbnail: false,
            download: false,
            loop: true,
            controls: true,
            enableDrag: true,
            keyPress: true,
            closable: true
        })

        return pdfList
    },


    /**
     * Publish an item
     * 
     */
    publishItem() {

        const publishItem = document.getElementById('publishItem'),
              statusLabel = document.querySelector('#itemTitle .label')

        if(!publishItem) return

        publishItem.addEventListener('click', function() {

            publishItem.classList.add('loading')

            axios.post(`/admin/item/publish/${this.dataset.id}`)
                .then(function (response) {

                    if( !response.data ) {
                        return false
                    }

                    // update the status label in DOM
                    statusLabel.classList.remove('l-gray')
                    statusLabel.classList.add('l-green')
                    statusLabel.innerHTML = 'Active'

                    publishItem.remove()

                    sc.alert.show('alert-success', response.data.message || 'Successful update')
              
                    return

                })
                .catch(e => {
                    publishItem.classList.remove('loading')
                    _errorResponse(e)

                })
        })
    }


}

myapp.singlePage.init()