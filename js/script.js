// ======================================
// Hinatuan Southern College - Homepage JS
// ======================================

document.addEventListener("DOMContentLoaded", () => {

    /* ===============================
       Mobile Menu Toggle
    =============================== */

    const menuToggle = document.getElementById("menu-toggle");
    const mainNav = document.getElementById("main-nav");

    if (menuToggle && mainNav) {

        menuToggle.addEventListener("click", () => {

            const isOpen = mainNav.classList.toggle("open");

            menuToggle.classList.toggle("active", isOpen);
            menuToggle.setAttribute("aria-expanded", isOpen);

        });

        mainNav.querySelectorAll("a").forEach(link => {

            link.addEventListener("click", () => {

                mainNav.classList.remove("open");
                menuToggle.classList.remove("active");
                menuToggle.setAttribute("aria-expanded", "false");

            });

        });

        window.addEventListener("resize", () => {

            if (window.innerWidth > 900) {

                mainNav.classList.remove("open");
                menuToggle.classList.remove("active");
                menuToggle.setAttribute("aria-expanded", "false");

            }

        });

    }


    /* ===============================
       Smooth Scrolling
    =============================== */

    document.querySelectorAll('nav a[href^="#"]').forEach(anchor => {

        anchor.addEventListener("click", function (e) {

            e.preventDefault();

            const target = document.querySelector(this.getAttribute("href"));

            if (target) {

                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

            }

        });

    });


    /* ===============================
       Sticky Header Shadow
    =============================== */

    const header = document.querySelector("header");

    window.addEventListener("scroll", () => {

        if (window.scrollY > 30) {

            header.classList.add("header-shadow");

        } else {

            header.classList.remove("header-shadow");

        }

    });


    /* ===============================
       Scroll Reveal Animation
    =============================== */

    const revealElements = document.querySelectorAll(
        ".hero-emblem,.hero-search,.welcome-badge,.welcome-text,.info-programs,.info-news,.footer-column"
    );

    const observer = new IntersectionObserver((entries) => {

        entries.forEach(entry => {

            if (entry.isIntersecting) {

                entry.target.classList.add("show");

            }

        });

    }, {

        threshold: .15

    });

    revealElements.forEach(el => observer.observe(el));


    /* ===============================
       Typing Effect
    =============================== */

    const title = document.getElementById("typing-title");

    if (title) {

        const text = title.textContent.trim();

        title.textContent = "";

        let index = 0;

        function type() {

            if (index < text.length) {

                title.textContent += text.charAt(index);

                index++;

                setTimeout(type, 30);

            }

        }

        type();

    }


    /* ===============================
       Button Ripple
    =============================== */

    document.querySelectorAll(".learn-btn, .btn-primary").forEach(btn => {

        btn.addEventListener("click", (e) => {

            const ripple = document.createElement("span");

            ripple.classList.add("ripple");

            const rect = btn.getBoundingClientRect();

            ripple.style.left = e.clientX - rect.left + "px";
            ripple.style.top = e.clientY - rect.top + "px";

            btn.appendChild(ripple);

            setTimeout(() => {

                ripple.remove();

            }, 600);

        });

    });


    /* ===============================
       Active Navigation
    =============================== */

    const sections = document.querySelectorAll("section[id]");
    const navLinks = document.querySelectorAll("nav a");

    window.addEventListener("scroll", () => {

        let current = "";

        sections.forEach(section => {

            const top = section.offsetTop - 120;

            if (window.scrollY >= top) {

                current = section.getAttribute("id");

            }

        });

        navLinks.forEach(link => {

            link.classList.remove("active");

            if (link.getAttribute("href") === "#" + current) {

                link.classList.add("active");

            }

        });

    });


    /* ===============================
       Program Finder Validation
    =============================== */

    const form = document.querySelector(".search-box");

    if (form) {

        form.addEventListener("submit", (e) => {

            e.preventDefault();

            const input = form.querySelector("input");

            if (input.value.trim() === "") {

                input.focus();
                input.style.borderColor = "#E8452C";

                return;

            }

            alert("Searching for: " + input.value);

        });

    }


    /* ===============================
       Footer Fade
    =============================== */

    const footer = document.querySelector("footer");

    if (footer) {

        const footerObserver = new IntersectionObserver(entries => {

            entries.forEach(entry => {

                if (entry.isIntersecting) {

                    footer.classList.add("show");

                }

            });

        });

        footerObserver.observe(footer);

    }

});
