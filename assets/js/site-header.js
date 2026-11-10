(function () {
    const headerHost = document.querySelector("[data-jobarn-header]");
    if (!headerHost) return;

    // Detect base URL relative to root
    const home = new URL("../../", document.currentScript.src);
    const route = path => new URL(path, home).href;
    const currentPath = window.location.pathname.toLowerCase();

    const navLinks = [
        { label: "Store", path: "collections/all.html", category: "all" },
        { label: "Desks", path: "collections/standing-desks.html", category: "desks" },
        { label: "Chairs", path: "collections/ergonomic-chairs.html", category: "chairs" },
        { label: "Displays", path: "collections/monitors.html", category: "monitors" },
        { label: "Mac & PC", path: "collections/computers.html", category: "computers" },
        { label: "Cameras", path: "collections/photography.html", category: "photography" },
        { label: "Audio", path: "collections/audio-equipments.html", category: "audio" },
        { label: "Deals", path: "collections/deals.html", category: "deals" }
    ];

    const navLinksHtml = navLinks.map(link => {
        const linkSlug = link.path.replace("collections/", "").replace(".html", "").toLowerCase();
        const isActive = currentPath.includes(linkSlug);
        const activeClass = isActive ? " active" : "";
        const dealsClass = link.category === "deals" ? " apple-nav-deals" : "";
        return `<li><a href="${route(link.path)}" class="apple-nav-link${activeClass}${dealsClass}" data-category="${link.category}">${link.label}</a></li>`;
    }).join("");

    headerHost.innerHTML = `
        <!-- Apple-Style Global Header -->
        <header class="apple-header" id="appleGlobalHeader">
            <div class="apple-header-inner">
                <!-- Left: Brand Logo -->
                <a href="${route("index.html")}" class="apple-nav-logo" title="Jobarn Store">
                    <img src="${route("assets/img/jobarn-logo.jpeg")}" alt="Jobarn Logo" class="apple-logo-img" fetchpriority="high" decoding="async">
                </a>

                <!-- Center: Navigation Links -->
                <nav class="apple-nav" aria-label="Main Navigation">
                    <ul class="apple-nav-list" id="bhNavCategories">
                        ${navLinksHtml}
                    </ul>
                </nav>

                <!-- Right: Search, Cart, Mobile Toggle -->
                <div class="apple-header-actions">
                    <!-- Search Trigger Form (Compact Apple Search) -->
                    <form class="apple-search-form" onsubmit="event.preventDefault(); window.JobarnStore?.doSearch();">
                        <button type="submit" class="apple-action-btn" aria-label="Search">
                            <svg class="apple-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </button>
                        <input type="text" id="bhSearchInput" class="apple-search-input" placeholder="Search Jobarn..." autocomplete="off">
                    </form>

                    <!-- Cart / Bag Button -->
                    <a class="apple-action-btn apple-bag-btn" id="cartOpenBtn" href="${route("cart.html")}" aria-label="Shopping Bag">
                        <svg class="apple-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <span class="apple-bag-count" id="cartBadge" style="display:none;">0</span>
                    </a>

                    <!-- Mobile Hamburger Toggle -->
                    <button type="button" class="apple-action-btn apple-mobile-toggle" id="bhMobileMenuToggle" aria-label="Menu" aria-expanded="false">
                        <svg class="apple-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Mobile Navigation Drawer -->
        <div class="bh-mobile-drawer-overlay" id="bhMobileDrawerOverlay"></div>
        <aside class="bh-mobile-drawer" id="bhMobileDrawer">
            <div class="bh-drawer-header">
                <a href="${route("index.html")}">
                    <img loading="lazy" decoding="async" src="${route("assets/img/jobarn-logo.jpeg")}" alt="Jobarn Logo" class="bh-drawer-logo-img">
                </a>
                <button type="button" class="bh-drawer-close-btn" id="bhMobileDrawerClose" aria-label="Close Menu">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="bh-drawer-body">
                <div class="bh-drawer-section-title">Shop Departments</div>
                <ul class="bh-drawer-nav-list">
                    <li><a href="${route("collections/all.html")}"><span>All Products</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/deals.html")}" style="color:#c00d0d; font-weight:700;"><span>🔥 Deals &amp; Offers</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/photography.html")}"><span>Photography &amp; Cameras</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/standing-desks.html")}"><span>Smart Standing Desks</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/ergonomic-chairs.html")}"><span>Ergonomic Chairs</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/monitors.html")}"><span>Monitors &amp; Displays</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/keyboards.html")}"><span>Keyboards &amp; Peripherals</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/top-products.html")}"><span>Workspace Upgrades</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/gaming.html")}"><span>Gaming Gear</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/computers.html")}"><span>Computers &amp; Laptops</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/pro-video.html")}"><span>Pro Video &amp; Cinema</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/audio-equipments.html")}"><span>Pro Audio &amp; Mics</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/cctv-security.html")}"><span>Surveillance &amp; CCTV</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/networking.html")}"><span>Networking &amp; Servers</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("collections/used.html")}"><span>Certified Used Gear</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                </ul>

                <div class="bh-drawer-section-title" style="margin-top:14px;">Information &amp; Services</div>
                <ul class="bh-drawer-nav-list">
                    <li><a href="${route("about.html")}"><span>About Jobarn</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("services.html")}"><span>ICT Services</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("projects.html")}"><span>Our Projects</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("faq.html")}"><span>FAQs &amp; Help</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="${route("contact.html")}"><span>Contact Us</span> <i class="fa-solid fa-chevron-right"></i></a></li>
                </ul>
            </div>
            <div class="bh-drawer-footer">
                <a href="tel:+255716026781" class="bh-drawer-phone">
                    <i class="fa-solid fa-phone"></i>
                    <span>+255 716-026-781</span>
                </a>
                <div class="bh-drawer-location">
                    SkyCity Mall, 1st Floor<br>Off Mlimani City, Dar es Salaam
                </div>
            </div>
        </aside>
    `;

    // Synchronize Cart Badge across all pages using localStorage
    function syncCartBadge() {
        try {
            const saved = JSON.parse(localStorage.getItem('jobarn_cart') || '[]');
            const count = Array.isArray(saved)
                ? saved.reduce((sum, item) => sum + (Number(item?.quantity) || 0), 0)
                : 0;
            const badge = document.getElementById('cartBadge');
            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'inline-block' : 'none';
            }
        } catch (e) {
            // ignore
        }
    }

    syncCartBadge();
    window.addEventListener('storage', (e) => {
        if (e.key === 'jobarn_cart') {
            syncCartBadge();
        }
    });

    // Drawer event listeners for shared header
    const toggleBtn = document.getElementById('bhMobileMenuToggle');
    const drawer = document.getElementById('bhMobileDrawer');
    const overlay = document.getElementById('bhMobileDrawerOverlay');
    const closeBtn = document.getElementById('bhMobileDrawerClose');

    function openDrawer() {
        drawer?.classList.add('active');
        overlay?.classList.add('active');
        toggleBtn?.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        drawer?.classList.remove('active');
        overlay?.classList.remove('active');
        toggleBtn?.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    toggleBtn?.addEventListener('click', openDrawer);
    closeBtn?.addEventListener('click', closeDrawer);
    overlay?.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer?.classList.contains('active')) {
            closeDrawer();
        }
    });

    // Apply live store settings dynamically
    fetch(route("assets/ecommerce/settings.json"), { cache: "no-cache" })
        .then(res => res.ok ? res.json() : null)
        .then(settings => {
            if (!settings || typeof settings !== "object") return;
            if (settings.contactPhone) {
                const telDigits = String(settings.contactPhone).replace(/[^\d+]/g, "");
                headerHost.querySelectorAll(".bh-drawer-phone").forEach(el => {
                    el.setAttribute("href", "tel:" + telDigits);
                    const span = el.querySelector("span");
                    if (span) span.textContent = settings.contactPhone;
                });
            }
            if (settings.contactAddress) {
                headerHost.querySelectorAll(".bh-drawer-location").forEach(el => {
                    el.textContent = "";
                    String(settings.contactAddress).split(/\r?\n/).forEach((line, idx) => {
                        if (idx > 0) el.appendChild(document.createElement("br"));
                        el.appendChild(document.createTextNode(line));
                    });
                });
            }
        })
        .catch(() => {});
})();
