/*
|--------------------------------------------------------------------------
| HTML Editors
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}

myapp.htmlEditor = {

    /**
     * Init
     */
    init() {

        $(function() {

            /**
             * Init HTML editors with simplified toolbar
             */ 
            const editors = document.getElementsByClassName('_html_editor')

            Array.prototype.forEach.call(editors, function(editor) {
                editor.quill = new Quill(editor, {
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{'header': 1}, {'header': 2}],
                            [{'list': 'bullet'}, {'list': 'ordered'}],
                            ['link'],
                            [{'align': [] }],
                            ['clean']
                        ]
                    },
                    handlers: {
                        'link': function(value) {
                            if (value) {
                                var href = prompt('Enter the URL');
                                this.quill.format('link', href);
                            } 
                            else {
                                this.quill.format('link', false);
                            }
                        }
                    },
                    theme: 'snow'
                })
                editor.quill.theme.tooltip.root.querySelector("input[data-link]").dataset.link = 'Enter URL or use the [link] shortcode';
            })

            

            /**
             * Init HTML editors with full toolbar
             */
            const page_editors = document.getElementsByClassName('_full_html_editor')

            Array.prototype.forEach.call(page_editors, editor => {
                editor.quill = new Quill(editor, {
                    // syntax: true,
                    modules: {
                        toolbar: [
                            [{ 'size': [] }],
                            [ 'bold', 'italic', 'underline', 'strike' ],
                            [{ 'color': [] }],
                            [{ 'header': '2' }, { 'header': '3' }, 'blockquote', 'code-block' ],
                            [{ 'list': 'ordered' }, { 'list': 'bullet'}, { 'indent': '-1' }, { 'indent': '+1' }],
                            [ 'direction', { 'align': [] }],
                            [ 'link', 'image', 'video' ],
                            [ 'clean' ]
                        ]
                    },
                    handlers: {
                        'link': function(value) {
                            if (value) {
                                var href = prompt('Enter the URL');
                                this.quill.format('link', href);
                            } 
                            else {
                                this.quill.format('link', false);
                            }
                        }
                    },
                    theme: 'snow'
                })
                editor.quill.theme.tooltip.root.querySelector("input[data-link]").dataset.link = 'Enter URL';
            })

        })


    }
}
myapp.htmlEditor.init()