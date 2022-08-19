/*
|--------------------------------------------------------------------------
| Markup inits
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}

myapp.markup = {

    /**
     * Init
     */
    init(el = $('body')) {

        let scope = myapp.markup

        // AutoSize
        // if(typeof autosize !== 'undefined') autosize(el.find('textarea'))

        // el.find('.modal').on('shown.bs.modal', function () {
        //     $(this).find('textarea').each(function () {       
        //         autosize.update(this)
        //     })
        // })

        // init Simple MDE
        el.find("textarea.comment").each(function() {
            scope.initSimpleMDE(this)
        })
        
        $('.convert-to-markdown').find('a').each(function() {
            $(this).attr('target', '_blank')
        })

    },



    /**
     * Init markup editor
     * 
     * @param {*} el 
     * @param {bool} is_simplified
     */
    initSimpleMDE(el, $is_simplified = false) {

        if(!el) return false

        let $toolbar = ["bold", "italic", "heading", "|", "unordered-list", "ordered-list", "|", "code", "link", "image", "preview", "|"];
        
        if($is_simplified) {
            $toolbar = ["bold", "italic", "heading", "|", "unordered-list", "ordered-list", "|"];
        }

        return new SimpleMDE({
            element: el,
            forceSync: true,
            toolbar: $toolbar,
            tabSize: 4,
            status: false,
            autofocus: false,
        })

    }
}


myapp.markup.init()