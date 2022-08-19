/*
|--------------------------------------------------------------------------
| Autocomplete
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.autocomplete = {

    /**
     * Init
     */
    init: ( el = $(document) ) => {

        let scope = myapp.autocomplete
        
        el.find('[data-autocomplete="1"]').each(function() {
            scope.initAutocomplete($(this))
        })

        return scope
    },


    /**
     * typeahead (Autocomplete fields)
     * @param {Element} el 
     */
    initAutocomplete: (el) => {

        let scope = myapp.autocomplete
        
        el.typeahead('destroy')

        const data_source = el.attr('data-source') || '',
              data_return = el.attr('data-return')
        
        if( ! data_source || ! data_return) {
            return
        }

        const allowed_sources = [
            'resident',
            'user',
            'building',
            'item'
        ];

        if( ! allowed_sources.includes(data_source) ) {
            console.log(`Autocomplete: Data source [${data_source}] not allowed for field: ${el.attr('name')}.`)
            return
        }

        let query_url = `/sources/${data_source}s/%QUERY`,
            prefetch_url = `/sources/${data_source}s`;
        

        let dataSource = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            prefetch: prefetch_url,
            remote: {
                url: query_url,
                wildcard: '%QUERY'
            }
        })

        dataSource.initialize()
        dataSource.clearPrefetchCache()

        $(el).typeahead({
            hint: true,
            highlight: true,
            suggestion: true,
            minLength: 1
        },
        {
            source: dataSource.ttAdapter(),
            name: 'name',
            displayKey: 'name',
            templates: {
                notFound: function(data) {
                    return ''
                },
                suggestion: function(opt) {
                       
                    switch(data_source) 
                    {
                        // User template
                        case "user":
                            return `<p>${opt.first_name} ${opt.last_name}<br><small>${opt.email}</small></p>`
                            break;

                        // Resident template
                        case "resident":
                            return `<p>${opt.first_name} ${opt.last_name}<br><small>${opt.email}</small></p>`
                            break;

                        // Building
                        case "building":
                            return `<p>${opt.name}<br><small>${App.helpers.formatAddress(opt)}</small></p>`
                            break;

                        // Item 
                        case "item":
                            console.log(opt)
                            return `<p>${opt.name}<br><small>${App.helpers.formatAddress(opt)}</small></p>`
                            break;

                        // general
                        default:
                            return `<p>${opt.name}<br><small>${opt.name}</small></p>`
                            break;
                    }

                }
            }
        })
        .on('typeahead:selected typeahead:autocomplete', function(event, opt) {

            // append hidden field with the desired return value
            $(this).after(`<input type="hidden" name="${data_source}_${data_return}" value="${opt[data_return]}" required>`)
            // Add selected class
            $(this).parent('.twitter-typeahead').addClass('selected')
        })
        .on('typeahead:selected typeahead:change', function(event, opt) {

            // var customer_id = opt ? opt.id : ''
    
            // switch(source)
            // {
            //     case 'customers':
            //         if( $('input[data-customer-id]').length ) {
            //             $('input[data-customer-id]').attr('data-customer-id', customer_id)
            //             scope.initAutocomplete($('input[data-customer-id]'))
            //         }
            //         break;
            // }

        })
        .blur( function() {
            
            // if(validate == 'false') {
            //     return
            // }

            // $(el).parents('form').parsley()

            // var validateEl = __fill.first()

            // if( validateEl.prop('required') && !validateEl.val().length ) {
            //     $(this).val('').parsley().validate()
            // }
        })

        // __autocomplete: select on enter
        el.keydown(function (e)
        {
            if (e.which == 13)
            {
                const e = $.Event("keydown")
                      e.keyCode = 39

                $(this).trigger(e)
                e.preventDefault()

                return false
            }
            else {
                // remove the added input
                $(this).next(`input[name="${data_source}_${data_return}"]`).remove()
                // remove selected class
                $(this).parent('.twitter-typeahead').removeClass('selected')
                // todo: is this working?
                $(this).next('.parsley-errors-list').remove()
            }
        })
 
    }

}


myapp.autocomplete.init()