
    window.Echo.channel(`location`)
        .listen('LocationUpdated', (e) => {
            console.log('hello');
            /**
            * Refresh the dataTables
            */
            if(typeof DataTable_LocationList !== 'undefined' ) DataTable_LocationList.DataTable().ajax.reload()

            // setTimeout(function () {
            //     console.log( $('#locationsOrder').find('tr#'+e.location.id) )
            //     $('#locationsOrder').find('tr#'+e.location.id).addClass('updated')
            // }, 150)

        })
    