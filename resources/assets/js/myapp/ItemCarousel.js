/*
|--------------------------------------------------------------------------
| Item Carousel
|--------------------------------------------------------------------------
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}


myapp.carousel = {

    /**
     * Init
     */
    init: function() {

        const slides = document.querySelectorAll(".item__slider")

        if(!slides.length) {
            return 
        }

        /** Dot navigation */

        /**
         * 
         * @param {*} dotsArray 
         * @param {*} embla 
         */
        const setupDotBtns = (dotsArray, embla) => {
            dotsArray.forEach((dotNode, i) => {
              dotNode.classList.add('embla__dot');
              dotNode.addEventListener('click', () => embla.scrollTo(i), false);
            });
        };
          

        /**
         * 
         * @param {*} dots 
         * @param {*} embla 
         */
        const generateDotBtns = (dots, embla) => {
            const scrollSnaps = embla.scrollSnapList();
            const dotsFrag = document.createDocumentFragment();
            const dotsArray = scrollSnaps.map(() => document.createElement('button'));
            dotsArray.forEach(dotNode => dotsFrag.appendChild(dotNode));
            dots.appendChild(dotsFrag);
            return dotsArray;
        };
          

        /**
         * 
         * @param {*} dotsArray 
         * @param {*} embla 
         */
        const selectDotBtn = (dotsArray, embla) => () => {
            const previous = embla.previousScrollSnap();
            const selected = embla.selectedScrollSnap();
            dotsArray[previous].classList.remove('is-selected');
            dotsArray[selected].classList.add('is-selected');
        };


        /** Prev / Next buttons */

        /**
         * 
         * @param {*} prevBtn 
         * @param {*} nextBtn 
         * @param {*} embla 
         */
        const setupPrevNextBtns = (prevBtn, nextBtn, embla) => {
            prevBtn.addEventListener('click', embla.scrollPrev, false);
            nextBtn.addEventListener('click', embla.scrollNext, false);
        };
          

        /**
         * 
         * @param {*} prevBtn 
         * @param {*} nextBtn 
         * @param {*} embla 
         */
        const disablePrevNextBtns = (prevBtn, nextBtn, embla) => {
            return () => {
                if (embla.canScrollPrev()) prevBtn.removeAttribute('disabled');
                else prevBtn.setAttribute('disabled', 'disabled');
            
                if (embla.canScrollNext()) nextBtn.removeAttribute('disabled');
                else nextBtn.setAttribute('disabled', 'disabled');
            };
        };
          

        /** Start init */
        let scrollItemsNum = 3

        if(window.innerWidth <= 992) {
            scrollItemsNum = 2
        }

        if(window.innerWidth <= 767) {
            scrollItemsNum = 1
        }
        
        slides.forEach(slide => {

            let viewPort = slide.querySelector(".embla__viewport"),
                prevBtn = slide.querySelector(".embla__button--prev"),
                nextBtn = slide.querySelector(".embla__button--next"),
                dots = slide.querySelector(".embla__dots") 

            let embla = EmblaCarousel(viewPort, { 
                dragFree: false,
                slidesToScroll: scrollItemsNum,
                align: 0,
                containScroll: true,
                loop: false,
                startIndex: 0,
            })

            let dotsArray = generateDotBtns(dots, embla)
            let setSelectedDotBtn = selectDotBtn(dotsArray, embla)
            let disablePrevAndNextBtns = disablePrevNextBtns(prevBtn, nextBtn, embla)
            
            setupPrevNextBtns(prevBtn, nextBtn, embla)
            setupDotBtns(dotsArray, embla)
            
            embla.on("select", setSelectedDotBtn)
            embla.on("select", disablePrevAndNextBtns)
            embla.on("init", setSelectedDotBtn)
            embla.on("init", disablePrevAndNextBtns)
    
            embla.on("resize", () => {

                let scrollItemsNum = 3

                if(window.innerWidth <= 992) 
                    scrollItemsNum = 2
                if(window.innerWidth <= 767)
                    scrollItemsNum = 1
                
                embla.changeOptions({slidesToScroll: scrollItemsNum})
            })
            
        }) // end foreach



    },

    initBookingGallerySlider() {

        const slider = $('.booking-single--slider .__slider')

        if(slider.length == 0) {
            return
        }


        slider.slick({
            dots: true,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            autoplay: true,
            pauseOnFocus: false,
            pauseOnHover: false
        })

        
    }

}


myapp.carousel.init()
myapp.carousel.initBookingGallerySlider()