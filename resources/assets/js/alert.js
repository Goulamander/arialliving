sc = {
    alert: {
        show: function(className, message, dismiss = 2000) {
            
            // close any previous alerts
            let current_alerts = document.querySelectorAll('.alert[role="alert"]')
           
            if(current_alerts) {
                current_alerts.forEach(el => $(el).alert('close'))
            }

            let alertMessage = `
                <div class="alert alert-icon alert-dismissible ${className}" role="alert">
                    <div class="message">
                        <button type="button" data-dismiss="alert" aria-label="Close" class="close">
                            <i class="material-icons" aria-hidden="true">close</i>
                        </button>
                        ${message}
                    </div>
                </div>`;
                
            let el = $.parseHTML(alertMessage.allTrim())

            setTimeout(function () {
                $(el).fadeOut(500, function() {
                    $(this).alert('close')
                })
            }, dismiss)

            $('body').append(el)

        }
    }
}
