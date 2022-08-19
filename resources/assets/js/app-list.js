        
    // Sortable inits
    if (typeof Sortable != "undefined") {

        var el = document.getElementById('locationsOrder');

        if(el) {
            // Item List
            Sortable.create(el, {
                sort: true,  // sorting inside list
                group: "words",
                animation: 150,
                handle: ".drag-handle",
                onUpdate: function (evt) {
                    
                    // Add action to event
                    // $('#locationsOrder').find('#'+evt.item.id).addClass('pinned');
                    // evt.action = 'set';
                    __saveOrder(evt)
                },
            })
        }
    }


    var __saveOrder = function(evt) {

        let Rows = new Array()

        $('#locationsOrder > tr').each(function() {
            Rows[$(this).index()] = $(this).attr("id")
        })

        $.ajax({
            url: "/location/order",
            type: "POST",
            data: {
                order: Rows,
                //action: evt.action,
                location_id: evt.item.id,
                location_index: evt.newIndex
            },
            cache: false,
            success: function($resp, status) 
            {
                /**
                 * Show the Error Response
                 */
                if($resp.error) {
                    sc.alert.show('alert-danger', 'Something went wrong. ' + $resp.error)
                    return
                }
                
                /**
                 * Refresh the dataTables
                 */
                if(typeof DataTable_LocationList !== 'undefined' ) DataTable_LocationList.DataTable().ajax.reload()

                sc.alert.show('alert-success', $resp.message || 'Successful update')
                return

            },
            error: function(xhr, status, error) {

                var err = eval("(" + xhr.responseText + ")");

                /**
                 * Show the Errors.
                 */

                if(err.errors)
                {
                    var ul = "<ul>"
                    $.each(err.errors, function (index, value) {
                        if(index != 'error') ul += "<li>"+ value +"</li>";
                    })
                    ul += "</ul>";

                    sc.alert.show('alert-danger', ul)
                    if($form) _releaseSubmitBtn($form)
                    return
                }
            }
        })
    }