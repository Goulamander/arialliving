
    var placeSearch, autocomplete;
    var componentForm = {
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'short_name',
        country: 'short_name',
        postal_code: 'short_name'
    };


    /*
        __functions
    ---------------------------------------------- */

    $(document).on('focus', '.autocompleteAddress', function() {
        if($(this).next('span.inited').length == 0) {
            console.log('init G')
            Autocomplete(this)
        }

    })

    function Autocomplete(el)
    {
        var options = {
            types: ['address'],
            componentRestrictions: {country: "AU"}
        };

        $(el).after('<span class="inited"></span>');
        autocomplete = new google.maps.places.Autocomplete(el, options);

        //autocomplete.group = el.parentNode.parentNode.parentNode;
        autocomplete.group = el.parentNode.parentNode;

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            fillInAddress(this.group)
        })
    }

    function fillInAddress(group)
    {
        
        var place = autocomplete.getPlace();

        for (var i = 0; i < place.address_components.length; i++)
        {
            var addressType = place.address_components[i].types[0];

            if (componentForm[addressType])
            {
                // Fill in fields
                var val = place.address_components[i][componentForm[addressType]];
                $(group).find('input.'+addressType).val(val);

                // Fill in route field
                if(addressType == 'route') {
                    if(place.address_components[7]) {
                        $(group).find('input.'+addressType).val(place.address_components[0]['short_name']+' '+place.address_components[1]['long_name']+' '+place.address_components[2]['long_name']);
                    } else {
                        $(group).find('input.'+addressType).val(place.address_components[0]['short_name']+' '+place.address_components[1]['long_name']);
                    }   
                }
                $(group).find('input.lat').val(place.geometry.location.lat())
                $(group).find('input.lng').val(place.geometry.location.lng())
            }
        }
    }
