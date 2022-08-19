/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.form = {

    init: () => {


        const scope = myapp.form

        $(function() {

            if( $('.alert[data-auto-dismiss]').length ) {

                $('.alert[data-auto-dismiss]').each(function (i, el) {
                    const timeout = $(el).data('auto-dismiss') || 2000
                    setTimeout(function () {
                        $(el).fadeOut(500, function() {
                            $(this).alert('close')
                        })
                    }, timeout)
                })
            }

            

            /**
             * onClick Handle form conditions
             */
            $(document).on('change', '[data-conditions]', function(e) {
                myapp.form.doFieldConditions(this)
            })



            /**
             * onLoad: Handle form conditions
             */
            $('[data-conditions]').each( function(e) {
                myapp.form.doFieldConditions(this)
            })



            /**
             * Office Hours Open/Close switch
             */
            $('.office-hours-switch').on('change', function() {

                const from = $(this).parents('li').find('.from'),
                        to = $(this).parents('li').find('.to')

                if(this.checked == true) {
                    from.removeClass('hidden')
                    to.removeClass('hidden')
                }
                else {
                    from.addClass('hidden')
                    to.addClass('hidden')
                }
            })

            /**
             * Add more fee handle
             */
            let ADD_MORE_FEE_COUNT = $('#clearing_fee_count').val() || 0;
            $(document).on('click', '.add-more-fee .add-more', function(e) {
                e.preventDefault();
                $('.add-more-fee .add-more-fee__items').append(`
                    <div class="row mb-2 add-more-fee__item">
                        <div class="col">
                            <input type="text" name="clearing_fee[${ADD_MORE_FEE_COUNT}].name" class="form-control" placeholder="Name">
                        </div>
                        <div class="col">
                            <input type="number" name="clearing_fee[${ADD_MORE_FEE_COUNT}].fee" min="0" class="form-control" placeholder="Fee">
                        </div>
                        <div class="col-1">
				            <button type="button" class="btn btn-primary p-1 remove"><i class="material-icons">remove</i></button>
                        </div>
                    </div>
                `);
                ADD_MORE_FEE_COUNT++;
                $('#clearing_fee_count').val(ADD_MORE_FEE_COUNT)
            })
            $(document).on('click', '.add-more-fee .remove', function(e) {
                e.preventDefault();
                $(this).closest('.add-more-fee__item').remove();
                ADD_MORE_FEE_COUNT--;
                $('#clearing_fee_count').val(ADD_MORE_FEE_COUNT)
            })
            /**
             * End Add more fee handle
             */
        })

        scope.registerCustomParsleyValidators()

    },



    /**
     * Collect input fields
     * 
     * @param {Element} $form
     * @return {Object} 
     */
    collectInputs: ($form) => {

        if(!$form) {
            return false;
        }

        var $inputs = $form.find("input, select, button, textarea, checkbox:checked")

        var $obj = {}
            
        $inputs.each( function() {

            // multiple selection
            if(this.multiple) {

                var selectedArray = new Array()
                var i
                var count = 0

                if(this.options) {
                    for (i=0; i < this.options.length; i++)
                    {
                        if (this.options[i].selected)
                        {
                            selectedArray[count] = this.options[i].value
                            count++
                        }
                    }
                }
                $obj[this.name] = selectedArray
                return
            }

            // checkbox
            else if(this.type == 'checkbox') {

                let options_num = $form.find("[name='"+this.name+"']").length

                if( ! $obj[this.name] ) {
                    $obj[this.name] = options_num > 1 ? [] : 0;
                }
                
                if(this.checked) {
                    if(options_num > 1) {
                        $obj[this.name].push(this.value || 1)
                    }
                    else {
                        $obj[this.name] = this.value || 1
                    }
                   
                }
                return 
            }

            // radio
            else if(this.type == 'radio') {

                if(this.checked) {
                    $obj[this.name] = this.value
                }
                return
            }

            // input
            else {
                // 
                if (this.name.includes('clearing_fee')) {
                    window._.set($obj, this.name, this.value);
                    $obj['clearing_fee'] = $obj['clearing_fee'].filter(v => !!v && (v && !!v.name))
                    return;
                }

                // collect the multiple single inputs
                if( this.name.includes('[]') ) {

                    let _name = this.name.replace('[]', '')

                    if( ! $obj[_name] ) {
                        $obj[_name] = [];
                    }
                    
                    $obj[_name].push(this.value)
                    return
                }

                // normal fields
                if(this.name) $obj[this.name] = this.value
                return
            }
            
        })

        // add the encrypted (eWay) fields
        if($form.data("encrypt") && window.Laravel.config.encrypt_key) {

            const card_number = $('#card_number'),
                  card_cvn = $('#card_cvn') 

            if(card_number.length) {
                $obj['card_number'] = encryptValue(card_number.val().replace(/ /g,''), window.Laravel.config.encrypt_key)
                $obj['card_cvn'] = encryptValue(card_cvn.val(), window.Laravel.config.encrypt_key)
            }
        }

        // Add the Signature
        if( window.BookingSignature ) {
            // signature validation
            const canvas = $('.signature_box canvas'),
                   label = $('.signature_box label')

            if( window.BookingSignature.isEmpty() ) {
                canvas.addClass('validation-error')
                label.addClass('validation-error')
                return false
            }

            canvas.removeClass('validation-error')
            label.removeClass('validation-error')
            $obj['signature'] = window.BookingSignature.toDataURL("image/svg+xml")
        }

        return $obj
    },



    /**
     * Do Field Conditions
     * 
     * @param {*} field 
     * @return Void
     */
    doFieldConditions(field) {


        if(!field.dataset.conditions) {
            return 
        }

        const conditions = JSON.parse(field.dataset.conditions)

        if(!conditions) {
            return
        }
    

        for (var key in conditions) {

            const condition = conditions[key]

            let value = ''
            switch(field.type)
            {
                case "checkbox":
                case "radio":
                    value = field.checked ? field.value : ''
                    break;
                default: 
                    value = field.value
            }
    
            // Get the fields
            const fields = condition.fields.split("|")
    
            if(!fields) {
                return
            }
    
            // Get the condition
            const a = condition.if_value.split(":")
    
            // has operator
            if(a.length == 2) {
    
                const operator = a[0];
                let expected_value = a[1]

                switch(operator) 
                {
                    case "in":
                        expected_value = expected_value.split(',')
                        fields.forEach(field => 
                            expected_value.indexOf.value ? $(`._${field}`).removeClass('hidden') : $(`._${field}`).addClass('hidden')
                        )
                        break
    
                    case "is":
                        fields.forEach(field => 
                            expected_value == value ? $(`._${field}`).removeClass('hidden') : $(`._${field}`).addClass('hidden')
                        )
                        break
    
                    case "not":
                        fields.forEach(field => 
                            expected_value != value ? $(`._${field}`).removeClass('hidden') : $(`._${field}`).addClass('hidden')
                        )
                        break
                        
                    case "or":
                        expected_value = expected_value.split(',')
                        fields.forEach(field => 
                            expected_value.includes(value) ? $(`._${field}`).removeClass('hidden') : $(`._${field}`).addClass('hidden')
                        )
                        break
                }

            }
            // Null
            else {
                if(a[0] == 'null') {
                    fields.forEach(field => 
                        value != '' ? $(`._${field}`).addClass('hidden') : $(`._${field}`).removeClass('hidden')
                    )
                }
            }
            
        }
        //
        return
    },


    /**
     * 
     */
    registerCustomParsleyValidators() {

        
        //has number
        window.Parsley.addValidator('number', {
            requirementType: 'number',
            validateString: function(value, requirement) {
                var numbers = value.match(/[0-9]/g) || [];
                return numbers.length >= requirement;
            },
            messages: {
                en: 'Your password must contain at least (%s) number.'
            }
        })
        
        //has special char
        window.Parsley.addValidator('special', {
            requirementType: 'number',
            validateString: function(value, requirement) {
                var specials = value.match(/[^a-zA-Z0-9]/g) || [];
                return specials.length >= requirement;
            },
            messages: {
                en: 'Your password must contain at least (%s) special characters.'
            }
        })
  
    }
}


myapp.form.init()