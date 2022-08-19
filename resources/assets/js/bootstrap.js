
    window._ = require('lodash');

    /**
     * We'll load jQuery and the Bootstrap jQuery plugin which provides support
     * for JavaScript based Bootstrap features such as modals and tabs. This
     * code may be modified to fit the specific needs of your application.
     */

    window.$ = window.jQuery = require('jquery')
    require('bootstrap')


    /**
     * Moment
     */
    window.moment = require('moment')
    
    
    /**
     * ES modules are recommended, if available, especially for typescript
     * 
     * https://flatpickr.js.org/options/
     */
    import flatpickr from "flatpickr"
    

    /**
     * FullCalendar
     * 
     */
    import { Calendar } from '@fullcalendar/core'
    import dayGridPlugin from '@fullcalendar/daygrid'
    import timeGridPlugin from '@fullcalendar/timegrid'
    import listPlugin from '@fullcalendar/list'
    import interactionPlugin from '@fullcalendar/interaction'
    import momentPlugin from '@fullcalendar/moment'

    window.Calendar = Calendar
    window.dayGridPlugin = dayGridPlugin
    window.timeGridPlugin = timeGridPlugin
    window.listPlugin = listPlugin
    window.interactionPlugin = interactionPlugin
    window.momentPlugin = momentPlugin
    


    /**
     * Promise based HTTP client for the browser and node.js It lets you make HTTP requests from both the browser and the server.
     * https://github.com/axios/axios
     */

    import axios from 'axios'
    window.axios = axios
    
    axios.defaults.headers.common = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }

    /**
     * S E L E C T  2
     * 
     * 
     */
    import select2 from 'select2'
    window.select2 = select2



    /**
     * Quill - HTML editor
     * https://quilljs.com/docs/download/
     */
    import Quill from 'quill'
    window.Quill = Quill


    /**
     * FilePond
     * https://github.com/pqina/filepond/
     */
    import * as FilePond from 'filepond'
    import FilePondPluginImageCrop from 'filepond-plugin-image-crop'
    import FilePondPluginImagePreview from 'filepond-plugin-image-preview'
    import FilePondPluginImageResize from 'filepond-plugin-image-resize'
    import FilePondPluginImageTransform from 'filepond-plugin-image-transform'
    import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'
    import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size'
    import FilePondPluginFileEncode from 'filepond-plugin-file-encode'

    window.FilePond = FilePond

    FilePond.registerPlugin(
        // image modification
        FilePondPluginImageCrop,
        FilePondPluginImagePreview,
        FilePondPluginImageResize,
        FilePondPluginImageTransform,
        //
        FilePondPluginFileValidateType,
        FilePondPluginFileValidateSize,
        FilePondPluginFileEncode
    )

    
    /**
     * Embla - Extensible bare bones carousels for the web
     * https://github.com/davidcetinkaya/embla-carousel
     */
    import EmblaCarousel from 'embla-carousel'
    window.EmblaCarousel = EmblaCarousel


    /**
     * Resize Sensor
     * https://www.npmjs.com/package/resize-sensor
     */
    import ResizeSensor from 'resize-sensor'
    window.ResizeSensor = ResizeSensor


    /**
     * StickySidebar
     * https://abouolia.github.io/sticky-sidebar/
     * https://github.com/abouolia/sticky-sidebar
     */
    import StickySidebar from 'sticky-sidebar'
    window.StickySidebar = StickySidebar


    /**
     * Slick -  Image gallery slider
     * https://github.com/kenwheeler/slick/
     */
    import slick from 'slick-carousel'
    window.slick = slick


    /**
     * lightgallery.js
     */
    import lightgallery from 'lightgallery.js'
    import 'lg-thumbnail.js'



    import * as SimpleMDE from "simplemde";
    window.SimpleMDE = SimpleMDE

    /**
     * signature_pad.js
     * 
     */
    import SignaturePad from 'signature_pad'
    window.SignaturePad = SignaturePad

    /**
     * body-scroll-lock: for Disabling scrolling while user uses the signature box.
     * 
     * https://www.npmjs.com/package/body-scroll-lock
     * 
     */
    window.bodyScrollLock = require('body-scroll-lock')

 


    /**
     * Vue is a modern JavaScript library for building interactive web interfaces
     * using reactive data binding and reusable components. Vue's API is clean
     * and simple, leaving you to focus on building your next great project.
     */
    // window.Vue = require('vue');
    // require('vue-resource');

    // /**
    //  * We'll register a HTTP interceptor to attach the "CSRF" header to each of
    //  * the outgoing requests issued by this application. The CSRF middleware
    //  * included with Laravel will automatically verify the header's value.
    //  */
    // Vue.http.interceptors.push((request, next) => {
    //     request.headers.set('X-CSRF-TOKEN', Laravel.csrfToken);
    //     next();
    // });



    /**
     * Laravel Echo (Event Listener)
     * 
     * Echo exposes an expressive API for subscribing to channels and listening
     * for events that are broadcast by Laravel. Echo and event broadcasting
     * allows your team to easily build robust real-time web applications.
     */


    // import Echo from "laravel-echo"

    // window.io = require('socket.io-client');

    // if (typeof io !== 'undefined') {
    //     window.Echo = new Echo({
    //         broadcaster: 'socket.io',
    //         host: window.location.hostname + ':6001',
    //         transports: ['websocket'],
    //         auth: {
    //             headers: {
    //                 'Authorization': 'Bearer ' + '01b24a813f250c6bce48fa861ddad7cf'
    //             }
    //         }
    //     });
    // }



    /**
     * Sortable
     * 
     */
    import Sortable from 'sortablejs';
    window.Sortable = Sortable;

    //import 'jquery-sortablejs';




    /**
     * Tippy JS
     * 
     */
    import tippy from 'tippy.js';
    import {hideAll} from 'tippy.js';
    
    window.tippy = tippy
    window.hideAllTippy = hideAll

    tippy('.tippy', {
        arrow: true,
        hideOnClick: true,
        trigger: 'mouseenter',
        theme: 'light'
    })

    tippy('.tippy-confirm-delete', {
        interactive: true,
        delay: [0, 500],
        arrow: true,
        trigger: 'click',
        theme: 'light'
    })


    /**
     * Popper JS
     * 
     */
    window.Popper = require('popper.js').default


    /**
     * 
     */
    // window.autosize = require('autosize')
