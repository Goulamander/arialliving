let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */

mix
    /**  -- CSS -- */

    // Aria App
    .sass('resources/assets/sass/app.scss', 'public/css/app-aria.css')
    .sass('resources/assets/sass/bootstrap.scss', 'public/css/app-bootstrap.css')
    
    // Libs (Node + Public)
    .combine([
            // flatpickr: Date/Time picker
            'node_modules/flatpickr/dist/flatpickr.min.css',
            'node_modules/flatpickr/dist/themes/airbnb.css',

            // Select2
            'node_modules/select2/dist/css/select2.min.css',
            'node_modules/tippy.js/dist/tippy.css',

            // Quill (HTML editor)
            'node_modules/quill/dist/quill.core.css',
            'node_modules/quill/dist/quill.bubble.css',
            'node_modules/quill/dist/quill.snow.css',

            // FilePond (File uploader)
            'node_modules/filepond/dist/filepond.css',
            'node_modules/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css',

            // Slick Carousel 
            'node_modules/slick-carousel/slick/slick.css',
            'node_modules/slick-carousel/slick/slick-theme.css',

            // Light Gallery
            'node_modules/lightgallery.js/dist/css/lightgallery.min.css',

            // SimpleMDE
            'node_modules/simplemde/dist/simplemde.min.css',
            
            // FullCalendar
            'node_modules/@fullcalendar/common/main.css',

            /** add in the public libs */

            // Date/Time picker
            'public/lib/datetimepicker/css/bootstrap-datetimepicker.min.css',
            
            // Gritter
            'public/lib/jquery.gritter/css/jquery.gritter.css',
            
            // DataTables
            'public/lib/datatables/css/jquery.dataTables.min.css',
            'public/lib/datatables/css/dataTables.bootstrap.min.css',
            
            // Bootstrap tokenfield 
            'public/css/bootstrap-tokenfield.min.css'
        ],
        'public/css/app-libs.css'
    )


    /** -- Scripts -- */

    // combine the old inheritance code (todo: put all these in the myapp. scope)
    .combine([
        'resources/assets/js/init.js',
        'resources/assets/js/alert.js',
        'resources/assets/js/colorpicker.js',
        'resources/assets/js/app-form.js',
        'resources/assets/js/app-list.js',
    ],
    'public/js/app-aria-0.js')

    // app-aria.js (main) 
    // .combine([
    //     'resources/assets/js/myapp/*.js',
    //     'resources/assets/js/myapp/Templates/*.js',
    // ],
    // 'public/js/app-aria.js')
    .combine('resources/assets/js/myapp', 'public/js/app-aria.js')

    // app-auth.js (auth page functions)
    .js('resources/assets/js/app-auth.js', 'public/js/app-auth.js')
    
    // events-listener.js
    .js('resources/assets/js/events-listener.js', 'public/js/events-listener.js')

    // app-libs.js (libs for the app)
    .js('resources/assets/js/app.js', 'public/js/app-libs.js').sourceMaps()

    // app-libs-2.js (compile libs from the public folder)
    .combine([
            // DataTables
            'public/lib/datatables/js/jquery.dataTables.js',
            'public/lib/datatables/plugins/buttons/js/dataTables.buttons.js',
            'public/lib/datatables/plugins/buttons/js/buttons.server-side.js',
            // parsley for validations
            'public/lib/parsley/parsley.min.js',
            // typeahead
            'public/lib/typeahead/typeahead.bundle.min.js',
            // dateFormat
            'public/lib/jquery-dateFormat.js',
            // input mask (from theme files)
            'public/assets/plugins/jquery-inputmask/jquery.inputmask.bundle.js',
        ],
        'public/js/app-libs-2.js').sourceMaps()


    // add Version in Production
    if (mix.inProduction()) {
        mix.version()
    }