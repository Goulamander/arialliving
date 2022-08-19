/*
|--------------------------------------------------------------------------
| Init / Select2
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}

myapp.select2 = {


    init: ( el = $(document)) => {

        let scope = myapp.select2
        
        el.find('[data-s2]').each(function() {
            scope.initSelect2($(this))
        })

        return scope
    },


    /**
     * init Select2 (Autocomplete fields)
     * @param {el} el 
     * 
     */
    initSelect2: (el) => {

        const data_source = el.attr('data-source') || '',
              data_return = el.attr('data-return'),
              data_container = el.attr('data-container-id') || 'body'

        // Collect params
        const building_id = el.attr('data-building-id') || ''

        if( el.data('select2') ) {
            return
        }

        if(data_source && data_return) {

            const allowed_sources = [
                'resident',
                'user',
                'admin',
                'building',
                'item'
            ]

            if( data_source && !allowed_sources.includes(data_source) ) {
                console.log(`Select2: Data source [${data_source}] not allowed for field: ${el.attr('name')}.`)
                return
            }

            let prefetch_url = `/sources/${data_source}s`;

            el.select2({
                width: '100%',
                placeholder: el.attr('data-placeholder') || ``,
                templateResult: s2_result,
                templateSelection: s2_selection,
                // allowClear: true,
                // dropdownParent: document.querySelector(data_container),
                ajax: {
                    type: 'GET',
                    url: prefetch_url,
                    data: function(params) {

                        if(building_id) {
                            params.building_id = building_id
                        }
                        return params
                    },
                    dataType: 'json',
                    processResults: function (data) {
                        return {
                            results: data
                        }
                    }
                } 
            })

        }
        else {
            
            el.select2({
                width: '100%',
                placeholder: 'Select',
                templateResult: s2_result,
                templateSelection: s2_selection,
            })

        }


        /**
         * Result list template
         * 
         * @param {Result} data 
         * 
         */
        function s2_result(data) {

            switch(data_source) {

                // S2: Building
                case 'building':

                    if (!data.id) return data.name
                    
                    return $(`
                    <span class="thumb-option">
                        ${_initials(data)}
                        <strong class="name">${data.name}</strong>
                        <small>${_fullAddress(data)}</small>
                    </span>`)
                    break

                // S2: Item
                case 'item':

                    if (!data.id) return data.text
                    
                    return $(`
                    <span class="thumb-option">
                        ${_itemThumbnail(data)}
                        <strong class="name">${data.title}</strong>
                        <small>${data.category_name}</small>
                    </span>`)
                    break

                // S2: User|Resident|Admin
                case 'user':
                case 'resident':
                case 'admin':

                    if (!data.id) return data.text
                    
                    return $(`
                    <span class="thumb-option">
                        <strong class="name">${data.name}</strong>
                        <small>${data.email}</small>
                    </span>`)
                    break

            }
        }

        /**
         * Selection template
         * 
         * @param {Result} data 
         * 
         */
        function s2_selection(data) {

            // for pre-defined values read the data attributes
            if(data.element && data.element.dataset.selected) {

                const attribute_data = JSON.parse(atob(data.element.dataset.selected))

                Object.keys(attribute_data).forEach(k => {
                    data[k] = attribute_data[k];
                })
            }

            switch(data_source) {

                // S2: Building
                case 'building':

                    if (!data.id) return data.name

                    return $(`
                    <span class="thumb-selected">
                        ${_initials(data)}
                        <strong class="name">${data.name}</strong>
                        <small>${_fullAddress(data)}</small>
                    </span>`)
                    break

                // S2: Item
                case 'item':

                    if (!data.id) return data.name

                    return $(`
                    <span class="thumb-selected">
                        ${_itemThumbnail(data)}
                        <strong class="name">${data.title}</strong>
                        <small>${data.category_name}</small>
                    </span>`)
                    break

                // S2: User|Resident|Admin
                case 'user':
                case 'resident':
                case 'admin':

                    if (!data.id) return data.name

                    return $(`
                    <span class="thumb-selected">
                        <strong class="name">${data.name}</strong>
                        <small>${data.email}</small>
                    </span>`)
                    break

            }
        }


        // Create initial from Name
        function _initials(data) {

            if(data.is_thumb) {
                return `<span class="initials _bg" style="background-image: url(${data.is_thumb})"></span>`
            }

            if(!data.name) return '...';

            let words = data.name.split(" "),
            initials = ''

            for ( var i = 0, l = words.length; i < l; i++ ) {
                if(i==2) break;
                initials += words[i][0]
            }
            return `<span class="initials">${initials.toUpperCase()}</span>`
        }


        // Get the thumbnail if any
        function _itemThumbnail(data) {
            if(!data.is_thumb) return ''
            return `<span class="initials _bg" style="background-image: url(/storage/items/${data.id}/${data.is_thumb}_180x180.jpg)"></span>`
        }



        // Build full address
        function _fullAddress(obj) {
            return `${obj.suburb} ${obj.postcode}`
        }

        
    },



}

myapp.select2.init()