

document.addEventListener(
    "DOMContentLoaded",
    function () {

        /* =====================================================
           RESPONSIVE MORE DROPDOWN
        ===================================================== */

        const moreDropdown = document.querySelector(".has-dropdown");
        const moreLink = moreDropdown?.querySelector(":scope > a");
        const dropdownMenu = moreDropdown?.querySelector(".editorial-dropdown");


        if (moreDropdown && moreLink && dropdownMenu) {


            moreLink.addEventListener("click", function (event) {

                /*
                 * Desktop:
                 * Keep normal hover dropdown behavior.
                 */

                if (window.innerWidth > 991) {
                    return;
                }


                event.preventDefault();
                event.stopPropagation();


                const isOpen =
                    moreDropdown.classList.contains("dropdown-open");


                /*
                 * Close if already open
                 */

                if (isOpen) {

                    moreDropdown.classList.remove(
                        "dropdown-open"
                    );

                    return;

                }


                /*
                 * Get More button position
                 */

                const rect =
                    moreLink.getBoundingClientRect();


                /*
                 * Position dropdown relative
                 * to the More button.
                 */

                dropdownMenu.style.top =
                    (rect.bottom + 5) + "px";


                dropdownMenu.style.left =
                    Math.max(
                        10,
                        rect.left
                    ) + "px";


                /*
                 * Open dropdown
                 */

                moreDropdown.classList.add(
                    "dropdown-open"
                );

            });



            /* =====================================================
               CLOSE WHEN CLICKING OUTSIDE
            ====================================================== */

            document.addEventListener(
                "click",
                function (event) {

                    if (window.innerWidth > 991) {
                        return;
                    }


                    if (
                        !moreDropdown.contains(event.target)
                    ) {

                        moreDropdown.classList.remove(
                            "dropdown-open"
                        );

                    }

                }
            );



            /* =====================================================
               CLOSE ON ESC
            ====================================================== */

            document.addEventListener(
                "keydown",
                function (event) {

                    if (
                        event.key === "Escape" &&
                        window.innerWidth <= 991
                    ) {

                        moreDropdown.classList.remove(
                            "dropdown-open"
                        );

                    }

                }
            );



            /* =====================================================
               CLOSE ON RESIZE
            ====================================================== */

            window.addEventListener(
                "resize",
                function () {

                    if (window.innerWidth > 991) {

                        moreDropdown.classList.remove(
                            "dropdown-open"
                        );

                    }

                }
            );

        }
        /* =====================================================
           NAVBAR LOAD ANIMATION
        ====================================================== */

        gsap.from(".gsap-logo", {

            y: -15,

            opacity: 0,

            duration: .6,

            ease: "power3.out"

        });


        gsap.from(".main-menu > li", {

            y: -10,

            opacity: 0,

            duration: .45,

            stagger: .06,

            delay: .1,

            ease: "power3.out"

        });


        gsap.from(".navbar-actions > *", {

            y: -10,

            opacity: 0,

            duration: .45,

            stagger: .07,

            delay: .15,

            ease: "power3.out"

        });



        /* =====================================================
           SEARCH
        ====================================================== */

        const searchOverlay =
            document.getElementById("searchOverlay");

        const searchBox =
            document.querySelector(".search-box");

        const searchInput =
            document.getElementById("searchInput");

        const openSearch =
            document.getElementById("openSearch");

        const closeSearch =
            document.getElementById("closeSearch");

        const searchForm =
            document.getElementById("searchForm");


        let searchIsOpen = false;



        /* =====================================================
           OPEN SEARCH
        ====================================================== */

        function openSearchModal() {

            if (searchIsOpen) {
                return;
            }


            searchIsOpen = true;


            /*
             * CSS controls the overlay.
             */

            searchOverlay.classList.add("active");


            /*
             * GSAP only animates the content.
             */

            gsap.fromTo(

                searchBox,

                {
                    y: 35,
                    opacity: 0
                },

                {
                    y: 0,
                    opacity: 1,

                    duration: .45,

                    ease: "power3.out",

                    onComplete: function () {

                        searchInput.focus();

                    }

                }

            );

        }



        /* =====================================================
           CLOSE SEARCH
        ====================================================== */

        function closeSearchModal() {

            if (!searchIsOpen) {
                return;
            }


            searchIsOpen = false;


            /*
             * Animate content out first.
             */

            gsap.to(

                searchBox,

                {
                    y: 25,

                    opacity: 0,

                    duration: .25,

                    ease: "power2.in",

                    onComplete: function () {


                        /*
                         * THIS closes the actual
                         * search overlay.
                         */

                        searchOverlay.classList.remove(
                            "active"
                        );


                        /*
                         * Reset search box for
                         * the next opening.
                         */

                        gsap.set(

                            searchBox,

                            {
                                y: 0,
                                opacity: 1

                            }

                        );

                    }

                }

            );

        }



        /* =====================================================
           OPEN BUTTON
        ====================================================== */

        openSearch.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                openSearchModal();

            }
        );



        /* =====================================================
           CLOSE BUTTON
        ====================================================== */

        closeSearch.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                event.stopPropagation();

                closeSearchModal();

            }
        );



        /* =====================================================
           CLICK OUTSIDE SEARCH
        ====================================================== */

        searchOverlay.addEventListener(
            "click",
            function (event) {

                if (
                    event.target === searchOverlay
                ) {

                    closeSearchModal();

                }

            }
        );



        /* =====================================================
           ESC KEY
        ====================================================== */

        document.addEventListener(
            "keydown",
            function (event) {

                if (
                    event.key === "Escape" &&
                    searchIsOpen
                ) {

                    closeSearchModal();

                }

            }
        );



        /* =====================================================
           SEARCH FORM
        ====================================================== */

        searchForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();


                const query =
                    searchInput.value.trim();


                if (!query) {

                    searchInput.focus();

                    return;

                }


                var searchUrl =
                    searchForm.getAttribute("action") || "/search";

                window.location.href =
                    searchUrl +
                    (searchUrl.indexOf("?") === -1 ? "?" : "&") +
                    "q=" +
                    encodeURIComponent(query);


                /*
                 * Connect your real search here.
                 *
                 * Example:
                 *
                 * window.location.href =
                 * "/search?q=" +
                 * encodeURIComponent(query);
                 */

            }
        );



        /* =====================================================
           OFFCANVAS GSAP ANIMATION
        ====================================================== */

        const offcanvas =
            document.getElementById(
                "mainOffcanvas"
            );


        offcanvas.addEventListener(
            "shown.bs.offcanvas",
            function () {


                gsap.from(
                    "#mainOffcanvas .canvas-content",
                    {

                        x: 30,

                        opacity: 0,

                        duration: .5,

                        ease: "power3.out"

                    }
                );


                gsap.from(
                    "#mainOffcanvas .canvas-links li",
                    {

                        x: 25,

                        opacity: 0,

                        duration: .4,

                        stagger: .06,

                        delay: .15,

                        ease: "power3.out"

                    }
                );

            }
        );


    }
);

document.addEventListener("DOMContentLoaded", function () {

    const backToTop = document.getElementById("backToTop");

    if (!backToTop) return;

    backToTop.addEventListener("click", function () {

        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });

    });

});