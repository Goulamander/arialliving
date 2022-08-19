/*
|--------------------------------------------------------------------------
| Init / Tippy.js
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}

myapp.tippy = {

    /**
     * Init tippy.js 
     */
    init(el = null) {

        if (typeof tippy == "undefined") {
            return
        }

        const elements = el ? el.querySelectorAll('[data-tippy-content]') : document.querySelectorAll('[data-tippy-content]'),
              newElements = Array.from(elements).filter(el => typeof el._tippy == 'undefined')

        tippy(newElements, {
            appendTo: 'parent',
            hideOnClick: true,
            trigger: 'mouseenter',
            theme: 'translucent',
            animation: 'shift-toward-subtle',
        })
    },


    /**
     * Init tippy.js fot Files
     */
    initFiles() {

        if (typeof tippy == "undefined") {
            return
        }

        tippy('.tippy-confirm-delete', {
            allowHTML: true,
            interactive: true,
            appendTo: 'parent',
            delay: [0, 500],
            trigger: 'click',
            theme: 'light',
            placement: 'left-end',
            animation: 'shift-toward-subtle'
        })

        tippy('.file-name', {
            appendTo: () => this.parentNode.parentNode,
            hideOnClick: true,
            trigger: 'mouseenter',
            theme: 'light',
            animation: 'shift-toward-subtle',
            delay: 400,
        })

    },



}


myapp.tippy.init()