document.addEventListener("DOMContentLoaded", function () {
    var toggle = document.querySelector(".nav-toggle");
    if (toggle) {
        toggle.addEventListener("click", function () {
            document.body.classList.toggle("nav-open");
            var expanded = document.body.classList.contains("nav-open");
            toggle.setAttribute("aria-expanded", expanded ? "true" : "false");
        });
    }

    // On touch/small screens, tapping a dropdown parent link toggles its submenu
    document.querySelectorAll(".has-dropdown > a").forEach(function (link) {
        link.addEventListener("click", function (e) {
            if (window.innerWidth <= 1240) {
                var parent = link.parentElement;
                var isOpen = parent.classList.contains("open");
                document.querySelectorAll(".has-dropdown.open").forEach(function (el) {
                    el.classList.remove("open");
                });
                if (!isOpen) {
                    e.preventDefault();
                    parent.classList.add("open");
                }
            }
        });
    });

    initHeader();
    initHero();
    initHeroSlides();
    initHeroDiagram();
    initDavos();
    initVoices();
    initCookieBanner();
});

function initHeroDiagram() {
    var diagram = document.querySelector(".hero-diagram");
    if (!diagram) return;
    var nodes = Array.prototype.slice.call(diagram.querySelectorAll(".hero-diagram__node"));
    var lines = {};
    diagram.querySelectorAll(".hero-diagram__line").forEach(function (line) {
        lines[line.getAttribute("data-line")] = line;
    });

    var userInteracting = false;
    var pauseTimeout = null;

    function clearAll() {
        nodes.forEach(function (n) { n.classList.remove("is-highlighted"); });
        Object.keys(lines).forEach(function (k) { lines[k].classList.remove("is-active"); });
    }
    function activateById(id) {
        clearAll();
        var node = nodes.find(function (n) { return n.getAttribute("data-node") === String(id); });
        if (node) node.classList.add("is-highlighted");
        if (lines[id]) lines[id].classList.add("is-active");
    }

    nodes.forEach(function (node) {
        var id = node.getAttribute("data-node");
        if (!id) return;
        var onEnter = function () {
            userInteracting = true;
            clearTimeout(pauseTimeout);
            activateById(id);
        };
        var onLeave = function () {
            pauseTimeout = setTimeout(function () { userInteracting = false; }, 1200);
        };
        node.addEventListener("mouseenter", onEnter);
        node.addEventListener("mouseleave", onLeave);
        node.addEventListener("focusin", onEnter);
        node.addEventListener("focusout", onLeave);
    });

    // Auto-cycle activation for continuous motion
    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (!reduced) {
        var cursor = 1;
        setInterval(function () {
            if (userInteracting) return;
            activateById(cursor);
            cursor = cursor >= nodes.length ? 1 : cursor + 1;
        }, 1800);
    }

    // Parallax tilt toward pointer
    var fine = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
    if (fine && !reduced) {
        var rect = null;
        diagram.addEventListener("pointerenter", function () { rect = diagram.getBoundingClientRect(); });
        diagram.addEventListener("pointermove", function (e) {
            if (!rect) rect = diagram.getBoundingClientRect();
            var x = (e.clientX - rect.left) / rect.width - 0.5;
            var y = (e.clientY - rect.top) / rect.height - 0.5;
            diagram.style.transform = "perspective(900px) rotateY(" + (x * 6) + "deg) rotateX(" + (-y * 6) + "deg)";
        });
        diagram.addEventListener("pointerleave", function () {
            diagram.style.transform = "";
            rect = null;
        });
    }
}

function initCookieBanner() {
    var STORAGE_KEY = "sov_cookie_consent";
    var COOKIE_ICON_SVG =
        '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">' +
            '<path fill="currentColor" d="M21.6 11.1a4.2 4.2 0 0 1-4.7-4.7 4.2 4.2 0 0 1-4.6-4.3A10 10 0 1 0 22 12c0-.3 0-.6-.4-.9zM8 15.5a1.3 1.3 0 1 1 1.3-1.3A1.3 1.3 0 0 1 8 15.5zm-.5-5a1 1 0 1 1 1-1 1 1 0 0 1-1 1zm4.5 6a1 1 0 1 1 1-1 1 1 0 0 1-1 1zm3-3a1.3 1.3 0 1 1 1.3-1.3 1.3 1.3 0 0 1-1.3 1.3z"/>' +
        '</svg>';

    var banner = null;
    var toggle = null;

    function readConsent() {
        try { return localStorage.getItem(STORAGE_KEY); } catch (e) { return null; }
    }
    function writeConsent(value) {
        try { localStorage.setItem(STORAGE_KEY, value); } catch (e) { /* ignore */ }
    }

    function buildBanner() {
        var el = document.createElement("div");
        el.className = "cookie-banner";
        el.setAttribute("role", "dialog");
        el.setAttribute("aria-live", "polite");
        el.setAttribute("aria-label", "Cookie consent");
        el.innerHTML =
            '<button type="button" class="cookie-banner__close" data-cookie-action="close" aria-label="Close">&times;</button>' +
            '<div class="cookie-banner__icon" aria-hidden="true">' + COOKIE_ICON_SVG + '</div>' +
            '<p class="cookie-banner__title">We value your privacy</p>' +
            '<p class="cookie-banner__text">This site uses cookies to keep the essentials working and, with your consent, to help us understand how the site is used. See our <a href="/cookie-policy/">Cookie Policy</a> and <a href="/privacy-policy/">Privacy Policy</a>.</p>' +
            '<div class="cookie-banner__actions">' +
                '<button type="button" class="cookie-banner__btn cookie-banner__btn--ghost" data-cookie-action="decline">Decline</button>' +
                '<button type="button" class="cookie-banner__btn cookie-banner__btn--primary" data-cookie-action="accept">Accept</button>' +
            '</div>';
        el.addEventListener("click", function (e) {
            var target = e.target.closest("[data-cookie-action]");
            if (!target) return;
            var action = target.getAttribute("data-cookie-action");
            if (action === "accept" || action === "decline") writeConsent(action);
            hideBanner();
            showToggle();
        });
        return el;
    }

    function buildToggle() {
        var btn = document.createElement("button");
        btn.type = "button";
        btn.className = "cookie-toggle";
        btn.setAttribute("aria-label", "Cookie preferences");
        btn.innerHTML = COOKIE_ICON_SVG;
        btn.addEventListener("click", function () {
            hideToggle();
            showBanner();
        });
        return btn;
    }

    function showBanner() {
        if (!banner) banner = buildBanner();
        if (!banner.parentNode) document.body.appendChild(banner);
        requestAnimationFrame(function () { banner.classList.add("is-visible"); });
    }
    function hideBanner() {
        if (!banner) return;
        banner.classList.remove("is-visible");
        setTimeout(function () { if (banner && banner.parentNode) banner.parentNode.removeChild(banner); }, 320);
    }
    function showToggle() {
        if (!toggle) toggle = buildToggle();
        if (!toggle.parentNode) document.body.appendChild(toggle);
        requestAnimationFrame(function () { toggle.classList.add("is-visible"); });
    }
    function hideToggle() {
        if (!toggle) return;
        toggle.classList.remove("is-visible");
        setTimeout(function () { if (toggle && toggle.parentNode) toggle.parentNode.removeChild(toggle); }, 240);
    }

    if (readConsent()) {
        showToggle();
    } else {
        showBanner();
    }
}

function initHeader() {
    var header = document.querySelector(".site-header");
    if (!header) return;

    if (document.querySelector(".hero")) {
        document.body.classList.add("has-hero");
    }

    var scrolled = false;
    function onScroll() {
        var isScrolled = window.scrollY > 24;
        if (isScrolled !== scrolled) {
            scrolled = isScrolled;
            header.classList.toggle("is-scrolled", scrolled);
        }
    }
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
}

function initHero() {
    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    // Word-by-word reveal for hero headline
    var titles = document.querySelectorAll("[data-reveal-words]");
    titles.forEach(function (el) {
        // Walk child nodes so we preserve <em> and other inline wrappers
        var frag = document.createDocumentFragment();
        var wordIndex = 0;
        function processNode(node, parentWrapper) {
            if (node.nodeType === Node.TEXT_NODE) {
                var parts = node.textContent.split(/(\s+)/);
                parts.forEach(function (part) {
                    if (part === "") return;
                    if (/^\s+$/.test(part)) {
                        (parentWrapper || frag).appendChild(document.createTextNode(part));
                    } else {
                        var mask = document.createElement("span");
                        mask.className = "word";
                        var inner = document.createElement("span");
                        inner.textContent = part;
                        inner.style.animationDelay = (0.08 * wordIndex + 0.15) + "s";
                        wordIndex++;
                        mask.appendChild(inner);
                        (parentWrapper || frag).appendChild(mask);
                    }
                });
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                var clone = node.cloneNode(false);
                Array.from(node.childNodes).forEach(function (child) {
                    processNode(child, clone);
                });
                (parentWrapper || frag).appendChild(clone);
            }
        }
        Array.from(el.childNodes).forEach(function (n) { processNode(n, null); });
        el.textContent = "";
        el.appendChild(frag);
    });

    // Generic reveal-on-load (aside, brandline, etc.)
    document.querySelectorAll(".reveal").forEach(function (el) {
        var delay = parseInt(el.getAttribute("data-reveal-delay") || "0", 10);
        setTimeout(function () { el.classList.add("is-in"); }, delay + 120);
    });

    if (reduced) return;

    // Cursor-tracked aura on the hero
    var hero = document.getElementById("hero");
    var aura = hero && hero.querySelector(".hero-aura");
    if (hero && aura) {
        var rafId = null;
        var target = { x: 60, y: 30 };
        var current = { x: 60, y: 30 };
        hero.addEventListener("pointermove", function (e) {
            var rect = hero.getBoundingClientRect();
            target.x = ((e.clientX - rect.left) / rect.width) * 100;
            target.y = ((e.clientY - rect.top) / rect.height) * 100;
            if (!rafId) tick();
        });
        hero.addEventListener("pointerleave", function () {
            target.x = 60; target.y = 30;
            if (!rafId) tick();
        });
        function tick() {
            current.x += (target.x - current.x) * 0.08;
            current.y += (target.y - current.y) * 0.08;
            aura.style.setProperty("--aura-x", current.x + "%");
            aura.style.setProperty("--aura-y", current.y + "%");
            if (Math.abs(target.x - current.x) > 0.1 || Math.abs(target.y - current.y) > 0.1) {
                rafId = requestAnimationFrame(tick);
            } else {
                rafId = null;
            }
        }
    }

    // Magnetic buttons
    var fine = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
    if (fine) {
        document.querySelectorAll(".btn-magnetic").forEach(function (btn) {
            var strength = 14;
            btn.addEventListener("pointermove", function (e) {
                var rect = btn.getBoundingClientRect();
                var x = e.clientX - rect.left - rect.width / 2;
                var y = e.clientY - rect.top - rect.height / 2;
                btn.style.transform = "translate(" + (x / rect.width) * strength + "px, " + (y / rect.height) * strength + "px)";
            });
            btn.addEventListener("pointerleave", function () {
                btn.style.transform = "";
            });
        });
    }
}

/**
 * Davos featured section: scroll reveal, parallax, live countdown, magnetic CTA.
 */
function initDavos() {
    var section = document.querySelector("[data-davos]");
    if (!section) return;
    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    // Reveal when the section enters the viewport
    if ("IntersectionObserver" in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    section.classList.add("is-visible");
                    io.disconnect();
                }
            });
        }, { threshold: 0.18 });
        io.observe(section);
    } else {
        section.classList.add("is-visible");
    }

    // Parallax background
    var image = section.querySelector("[data-davos-parallax]");
    if (image && !reduced) {
        var ticking = false;
        var update = function () {
            var rect = section.getBoundingClientRect();
            var progress = (rect.top + rect.height / 2 - window.innerHeight / 2) / window.innerHeight;
            image.style.setProperty("--davos-shift", (progress * -70).toFixed(1) + "px");
            ticking = false;
        };
        window.addEventListener("scroll", function () {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(update);
            }
        }, { passive: true });
        update();
    }

    // Live countdown
    var countdown = section.querySelector("[data-davos-countdown]");
    if (countdown) {
        var target = new Date(countdown.getAttribute("data-davos-countdown")).getTime();
        var units = {};
        countdown.querySelectorAll("[data-unit]").forEach(function (el) {
            units[el.getAttribute("data-unit")] = el;
        });
        var pad = function (n, len) {
            n = String(n);
            while (n.length < len) { n = "0" + n; }
            return n;
        };
        var setUnit = function (name, value) {
            var el = units[name];
            if (!el || el.textContent === value) return;
            el.textContent = value;
            if (!reduced) {
                el.classList.remove("is-tick");
                void el.offsetWidth;
                el.classList.add("is-tick");
            }
        };
        var timer = null;
        var tick = function () {
            var diff = Math.max(0, target - Date.now());
            setUnit("days", pad(Math.floor(diff / 86400000), 2));
            setUnit("hours", pad(Math.floor(diff / 3600000) % 24, 2));
            setUnit("minutes", pad(Math.floor(diff / 60000) % 60, 2));
            setUnit("seconds", pad(Math.floor(diff / 1000) % 60, 2));
            if (diff === 0) {
                clearInterval(timer);
                countdown.classList.add("is-live");
                var note = countdown.querySelector(".davos-x__countdown-note");
                if (note) { note.textContent = "The Annual Meeting is under way"; }
            }
        };
        tick();
        timer = setInterval(tick, 1000);
    }

    // Magnetic orb
    var orb = section.querySelector("[data-davos-magnetic]");
    var fine = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
    if (orb && fine && !reduced) {
        orb.addEventListener("pointermove", function (e) {
            var rect = orb.getBoundingClientRect();
            var x = e.clientX - rect.left - rect.width / 2;
            var y = e.clientY - rect.top - rect.height / 2;
            orb.style.transform = "translate(" + (x * 0.25).toFixed(1) + "px, " + (y * 0.25).toFixed(1) + "px)";
        });
        orb.addEventListener("pointerleave", function () {
            orb.style.transform = "";
        });
    }
}

/**
 * Client voices: auto-rotating testimonial slider with arrows, keyboard, swipe and pause on hover.
 */
function initVoices() {
    var section = document.querySelector("[data-voices]");
    if (!section) return;
    var slides = Array.prototype.slice.call(section.querySelectorAll("[data-voices-slide]"));
    if (slides.length === 0) return;
    var bar = section.querySelector("[data-voices-bar]");
    var current = section.querySelector("[data-voices-current]");
    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var duration = 7000;
    var index = 0;
    var timer = null;
    var remaining = duration;
    var startedAt = 0;

    section.style.setProperty("--voices-duration", duration / 1000 + "s");

    function show(next) {
        index = (next + slides.length) % slides.length;
        slides.forEach(function (slide, i) {
            var active = i === index;
            slide.classList.toggle("is-active", active);
            slide.setAttribute("aria-hidden", active ? "false" : "true");
        });
        if (current) { current.textContent = (index < 9 ? "0" : "") + (index + 1); }
        restart();
    }

    function restart() {
        clearTimeout(timer);
        remaining = duration;
        if (bar) {
            bar.classList.remove("is-running");
            void bar.offsetWidth;
        }
        if (reduced || slides.length < 2) return;
        if (bar) { bar.classList.add("is-running"); }
        run();
    }

    function run() {
        startedAt = Date.now();
        timer = setTimeout(function () { show(index + 1); }, remaining);
    }

    section.querySelector("[data-voices-prev]").addEventListener("click", function () { show(index - 1); });
    section.querySelector("[data-voices-next]").addEventListener("click", function () { show(index + 1); });

    section.addEventListener("keydown", function (e) {
        if (e.key === "ArrowLeft") { show(index - 1); }
        if (e.key === "ArrowRight") { show(index + 1); }
    });

    section.addEventListener("mouseenter", function () {
        if (reduced) return;
        clearTimeout(timer);
        remaining -= Date.now() - startedAt;
        section.setAttribute("data-paused", "");
    });
    section.addEventListener("mouseleave", function () {
        if (reduced) return;
        section.removeAttribute("data-paused");
        run();
    });

    var touchX = null;
    var stage = section.querySelector(".voices__stage");
    stage.addEventListener("touchstart", function (e) { touchX = e.touches[0].clientX; }, { passive: true });
    stage.addEventListener("touchend", function (e) {
        if (touchX === null) return;
        var dx = e.changedTouches[0].clientX - touchX;
        if (Math.abs(dx) > 40) { show(index + (dx < 0 ? 1 : -1)); }
        touchX = null;
    });

    show(0);
}

/**
 * Hero background: cross-fade slowly to the next photo every 10 seconds.
 * The next photo is fully loaded before the fade starts, so there is no flash.
 */
function initHeroSlides() {
    var container = document.querySelector("[data-hero-slides]");
    if (!container) return;
    var slides = Array.prototype.slice.call(container.querySelectorAll(".hero-slide"));
    if (slides.length < 2) return;
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    var interval = 10000;
    var small = window.matchMedia("(max-width: 720px)").matches;
    var index = 0;

    function load(slide, done) {
        var large = slide.getAttribute("data-image");
        if (!large) { done(); return; }
        var compact = slide.getAttribute("data-image-sm") || large;
        var img = new Image();
        img.onload = img.onerror = function () {
            if (slide.hasAttribute("data-image")) {
                slide.style.setProperty("--slide-image", "url('" + large + "')");
                slide.style.setProperty("--slide-image-sm", "url('" + compact + "')");
                slide.removeAttribute("data-image");
                slide.removeAttribute("data-image-sm");
            }
            done();
        };
        img.src = small ? compact : large;
    }

    function next() {
        if (document.hidden) {
            setTimeout(next, interval);
            return;
        }
        var nextIndex = (index + 1) % slides.length;
        load(slides[nextIndex], function () {
            slides[index].classList.remove("is-active");
            slides[nextIndex].classList.add("is-active");
            index = nextIndex;
            setTimeout(next, interval);
        });
    }

    // Start loading the second photo once the page has finished loading.
    window.addEventListener("load", function () {
        load(slides[1], function () {});
    });

    setTimeout(next, interval);
}

