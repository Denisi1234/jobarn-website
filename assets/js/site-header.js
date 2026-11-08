(function () {
    const headerHost = document.querySelector("[data-jobarn-header]");
    if (!headerHost) return;

    // Detect base URL relative to root
    const home = new URL("../../", document.currentScript.src);
    const route = path => new URL(path, home).href;
    const currentPath = window.location.pathname.toLowerCase();

    const navLinks = [
        { label: "All Products", path: "collections/all.html", category: "all" },
        { label: "Photography", path: "collections/photography.html", category: "photography" },
        { label: "Standing Desks", path: "collections/standing-desks.html", category: "desks" },
        { label: "Ergonomic Chairs", path: "collections/ergonomic-chairs.html", category: "chairs" },
        { label: "Monitors & Displays", path: "collections/monitors.html", category: "monitors" },
        { label: "Keyboards", path: "collections/keyboards.html", category: "keyboards" },
        { label: "Upgrades", path: "collections/top-products.html", category: "upgrades" },
        { label: "Gaming", path: "collections/gaming.html", category: "gaming" },
        { label: "Computers", path: "collections/computers.html", category: "computers" },
        { label: "Pro Video", path: "collections/pro-video.html", category: "pro-video" },
        { label: "Pro Audio", path: "collections/audio-equipments.html", category: "audio" },
        { label: "Accessories", path: "collections/gadgets.html", category: "accessories" },
        { label: "Surveillance & CCTV", path: "collections/cctv-security.html", category: "security" },
        { label: "Networking", path: "collections/networking.html", category: "networking" },
        { label: "Used", path: "collections/used.html", category: "used" }
    ];

    const navLinksHtml = navLinks.map(link => {
        const linkSlug = link.path.replace("collections/", "").replace(".html", "").toLowerCase();
        const isActive = currentPath.includes(linkSlug);
        const activeClass = isActive ? " active" : "";
        const usedClass = link.category === "used" ? " bh-nav-used" : "";
        return `<li><a href="${route(link.path)}" class="bh-nav-link${activeClass}${usedClass}" data-category="${link.category}">${link.label}</a></li>`;
    }).join("");

    headerHost.innerHTML = `
        <!-- Top Utility Header -->
        <header class="bh-top-header">
            <div class="bh-container bh-top-row">
                <!-- Left: Mobile Menu Toggle & Logo & Expert Phone -->
                <div class="bh-logo-section">
                    <button type="button" class="bh-mobile-menu-toggle" id="bhSharedMobileMenuToggle" aria-label="Open Navigation Menu" aria-expanded="false" aria-controls="bhSharedMobileDrawer">
                        <svg class="bh-svg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18v2.2H3V6zm0 4.9h18v2.2H3v-2.2zm0 4.9h18V18H3v-2.2z"/></svg>
                    </button>
                    <a href="${route("index.html")}" class="bh-brand-logo" title="Jobarn Store Tanzania">
                        <div class="bh-brand-box">
                            <img src="${route("assets/img/jobarn-logo.jpeg")}" alt="Jobarn Logo" class="bh-logo-img" decoding="async" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\\'bh-logo-fallback\\'>JOBARN</span>';">
                        </div>
                    </a>
                    
                    <div class="bh-expert-phone">
                        <i class="fa-solid fa-headset bh-phone-icon"></i>
                        <div class="bh-expert-text">
                            <span class="bh-expert-sub">Ask Our Experts</span>
                            <a href="tel:+255716026781" class="bh-phone-number">+255 716-026-781 <i class="fa-solid fa-chevron-down" style="font-size:10px; margin-left:2px;"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Center: Search Bar -->
                <div class="bh-search-section">
                    <form class="bh-search-form" onsubmit="event.preventDefault(); window.location.href='${route("collections/all.html")}?search=' + encodeURIComponent(document.getElementById('bhSharedSearchInput').value.trim());">
                        <input type="text" id="bhSharedSearchInput" placeholder="Search desks, chairs, monitors, cameras, audio, CCTV..." autocomplete="off">
                        <button type="submit" class="bh-search-btn" aria-label="Search">
                            <svg class="bh-svg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10.5 3a7.5 7.5 0 0 1 5.95 12.07l4.24 4.24-1.42 1.42-4.24-4.24A7.5 7.5 0 1 1 10.5 3zm0 2a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11z"/></svg>
                        </button>
                    </form>
                </div>

                <!-- Right: Deal Zone, Account, Cart -->
                <div class="bh-user-actions">
                    <!-- Deal Zone -->
                    <a href="${route("collections/deals.html")}" class="bh-action-item bh-deal-zone" title="Deal Zone">
                        <div class="bh-deal-icon-wrap">
                            <svg class="bh-svg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v3a7 7 0 0 1 7 7h3v2h-3a7 7 0 0 1-7 7v3h-2v-3a7 7 0 0 1-7-7H0v-2h3a7 7 0 0 1 7-7V2h2zm0 5a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 3a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/></svg>
                        </div>
                        <div class="bh-action-text">
                            <span class="bh-deal-timer">Ends In <b id="countdownTimer">08:31:24</b></span>
                            <span class="bh-action-label">Deal Zone</span>
                        </div>
                    </a>

                    <!-- Cart Button -->
                    <a class="bh-action-item bh-cart-btn" id="cartOpenBtn" href="${route("cart.html")}" title="View Cart">
                        <div class="bh-cart-icon-wrap">
                            <svg class="bh-svg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm10 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM3 2h2.2l.7 3H21l-1.9 8.2a2 2 0 0 1-2 1.6H8.3l-.5 2H19v2H6.5a1.5 1.5 0 0 1-1.46-1.85L6.1 14 4.2 5H2V3h1V2zm3.3 5l1.3 6h9.3l1.4-6H6.3z"/></svg>
                            <span class="bh-cart-count" id="cartBadge" style="display:none;">0</span>
                        </div>
                        <div class="bh-action-text">
                            <span class="bh-action-label">My Cart</span>
                        </div>
                    </a>
                </div>
            </div>
        </header>

        <!-- Navigation Bar Strip -->
        <nav class="bh-nav-bar">
            <div class="bh-container bh-nav-inner">
                <ul class="bh-nav-links" id="bhNavCategories">
                    ${navLinksHtml}
                </ul>
            </div>
        </nav>

        <!-- Mobile Navigation Drawer -->
        <div class="bh-mobile-drawer-overlay" id="bhSharedMobileDrawerOverlay"></div>
        <aside class="bh-mobile-drawer" id="bhSharedMobileDrawer">
            <div class="bh-drawer-header">
                <a href="${route("index.html")}">
                    <img src="${route("assets/img/jobarn-logo.jpeg")}" alt="Jobarn Logo" class="bh-drawer-logo-img">
                </a>
                <button type="button" class="bh-drawer-close-btn" id="bhSharedMobileDrawerClose" aria-label="Close Menu">
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
    const toggleBtn = document.getElementById('bhSharedMobileMenuToggle');
    const drawer = document.getElementById('bhSharedMobileDrawer');
    const overlay = document.getElementById('bhSharedMobileDrawerOverlay');
    const closeBtn = document.getElementById('bhSharedMobileDrawerClose');

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
                headerHost.querySelectorAll(".bh-phone-number").forEach(el => {
                    el.setAttribute("href", "tel:" + telDigits);
                    const icon = el.querySelector("i");
                    el.textContent = settings.contactPhone + " ";
                    if (icon) el.appendChild(icon);
                });
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
