
/*
|--------------------------------------------------------------------------
| Init / DropZone
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}

myapp.fileManager = {

    init() {

        let scope = myapp.fileManager

        /** File uploader inits */
        scope.initThumbUploader()
        scope.initPDFUploader()
        scope.initGalleryUploader()
        scope.initInlineUploader()
    },


    /**
     * init the Thumbnail uploader (items, building)
     * @param {array} elements 
     */
    initInlineUploader(itemThumbUploader = null) {

        if(!itemThumbUploader) {
            itemThumbUploader = document.querySelectorAll('.inline-uploader')
        }

        if(!itemThumbUploader) return false

        itemThumbUploader.forEach(function(el) {
            FilePond.create(el, {       
                labelIdle: '<span class="filepond--label-action"> Browse </span>',
                acceptedFileTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'],
                allowFileEncode: true,
                allowImagePreview: true,
                imagePreviewHeight: 240,
                imagePreviewMaxHeight: 400,
                maxFileSize: '2MB',
            })
        })
    },



    /**
     * init the Thumbnail uploader (items, building)
     * @param {*} el 
     */
    initThumbUploader(el = '.image-thumb-uploader') {

        const inputElement = document.querySelector(el)

        if(!inputElement) return false
        
        let folder = inputElement.dataset.path || '',
            filename = inputElement.dataset.filename || ''

        const pond = FilePond.create(inputElement, {
            server: {
                url: '/api/filepond',
                revert: '/delete',
                process: {
                    url: '/process',
                    method: 'POST',
                    ondata: (formData) => {
                        formData.append('folder', folder)
                        formData.append('process_type', inputElement.dataset.processType || '')
                        return formData
                    },
                    onload: resp => {
                        resp = JSON.parse(resp)
                        console.log(resp)

                        // Update the data-filename
                        inputElement.dataset.filename = resp.data.file_name
                    },
                    onerror: e => {
                        _errorResponse(JSON.parse(e))
                    }
                },
                load: '/load/',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            },
            labelIdle: '<span class="filepond--label-action"> Browse </span>',
            acceptedFileTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'],
            allowImagePreview: true,
            imagePreviewHeight: 50,
            stylePanelLayout: 'compact circle',
            styleLoadIndicatorPosition: 'center bottom',
            styleProgressIndicatorPosition: 'right bottom',
            styleButtonRemoveItemPosition: 'center bottom',
            styleButtonProcessItemPosition: 'right bottom',
        })
        // remove a file
        pond.on('removefile', (error, file) => {

            if (!error) {

                let path = folder + inputElement.dataset.filename.trim()

                axios.post('/api/filepond/delete', {path: path, folder: folder})
                    .then(function (response) {
                        
                        if(!response.data) return false

                        sc.alert.show('alert-success', response.data.message || 'Successful update')
                        return
                    })
                    .catch(e => _errorResponse(e))
            }
        })
    },



    /**
     * init the Gallery image uploader (items, buildings)
     * @param {*} el 
     */
    initGalleryUploader(el = 'input[type="file"]._gallery') {

        const inputElement = document.querySelector(el)

        if(!inputElement) return false

        let folder = inputElement.dataset.path || ''
        
        const pond = FilePond.create(inputElement, {
            server: {
                url: '/api/filepond',
                process: {
                    url: '/process',
                    method: 'POST',
                    ondata: (formData) => {
                        formData.append('folder', folder)
                        formData.append('process_type', inputElement.dataset.processType || '')
                        return formData
                    },
                    onload: resp => {
                        resp = JSON.parse(resp)
                        // insert the new image into the gallery DOM
                        myapp.fileManager.attachImageToGallery(resp.data.url)
                    },
                    onerror: e => {
                        _errorResponse(JSON.parse(e))
                    }
                },
                revert: '/process',
                load: '/load/',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            },
            //
            labelIdle: '<h4>Upload images</h4><br>Drag & Drop or <span class="filepond--label-action"> Browse </span>',
            acceptedFileTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'],
            allowFileEncode: true,
            allowMultiple: true,
            allowImagePreview: false,
            // resize
            allowImageResize: true,
            imageResizeTargetWidth: 1440,
            imageResizeMode: 'cover',
            imageResizeUpscale: false,
            // validate image size
            allowImageValidateSize: true,
            imageValidateSizeMinWidth: 1440,
            imageValidateSizeMinHeight: 500     
        })
    },



    /**
     * init the Attachments uploader (items, buildings)
     * @param {*} el 
     */
    initPDFUploader(el = 'input[type="file"]._attachments') {

        const inputElement = document.querySelector(el)

        if(!inputElement) return false

        let folder = inputElement.dataset.path || ''

        const pond = FilePond.create(inputElement, {
            server: {
                url: '/api/filepond',
                process: {
                    url: '/process',
                    method: 'POST',
                    ondata: (formData) => {
                        formData.append('folder', folder)
                        formData.append('process_type', inputElement.dataset.processType || '')
                        return formData
                    },
                    onload: resp => {
                        resp = JSON.parse(resp)
                        // insert the new PDF into the list DOM
                        myapp.fileManager.attachPDFToList(resp.data.url)
                    },
                    onerror: e => {
                        _errorResponse(JSON.parse(e))
                    }
                },
                revert: '/process',
                load: '/load/',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            },
            labelIdle: `<h4>${inputElement.getAttribute('data-title') || ``}</h4><br> Drag & Drop or <span class="filepond--label-action"> Browse </span>`,
            allowImagePreview: false,
            allowFileSizeValidation: true,
            maxTotalFileSize: '100MB',
            acceptedFileTypes: ['application/pdf'],
        })
    },



    /**
     * Attach image to DOM
     * @param {str} image_path
     */
    attachImageToGallery(image_path) {

        if(!image_path) return false

        let el = document.querySelector('._image_gallery')
        if(!el) return 

        let empty_state = el.querySelector('.empty')
        if(empty_state) empty_state.remove()


        let new_item = `
        <div class="_img" style="background-image: url(/storage/${image_path})" data-file="${image_path}">
            <button type="button" class="no-btn _delete">
                <i class="material-icons">delete</i>
            </button>
        </div>`


        el.insertAdjacentHTML('beforeend', new_item)
        
        myapp.singlePage.initGallery()
    },



    /**
     * Attach the new PDF to the PDF list
     * @param {str} url
     */
    attachPDFToList(url) {

        if(!url) return false

        let el = document.querySelector('._pdf_list')
        if(!el) return 

        let empty_state = el.querySelector('.empty')
        if(empty_state) empty_state.remove()

        let clean_filename = getCleanPDFNameFromPath(url) 

        let new_item = `
        <div data-file="${url}" class="_pdf">
            <a href="/storage/${url}" data-iframe="true" class="term-link">${clean_filename}</a>
            <div class="dropdown dropleft _actions">
                <a class="no-btn" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="zmdi zmdi-more"></i>
                </a>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                    <button type="button" class="dropdown-item _rename">Rename</button>
                    <button type="button" class="dropdown-item _delete">Delete</button>
                </div>
            </div>
        </div>`

        el.insertAdjacentHTML('beforeend', new_item)
        
        myapp.singlePage.initPDFList()
        myapp.singlePage.initPDFPreviewer()
    },


    /**
     * 
     */
    updatePDFListAfterUpdate(data) {

        let list = document.querySelector('._pdf_list')

        if(!list) return

        let list_el = list.querySelector(`[data-file="${data.old_path}"]`),
            list_el_link = list_el.querySelector('.term-link')

        //
        list_el.dataset.file = data.new_path
        //
        list_el_link.href = `/storage/${data.new_path}`
        list_el_link.innerHTML = getCleanPDFNameFromPath(data.new_path)
        return
    }

}

myapp.fileManager.init()
