(function () {
    const footer = document.querySelector("[data-site-footer]");
    if (!footer) return;

    // Base URL resolution
    const home = new URL("../../index.html", document.currentScript.src);
    const route = path => new URL(path, home).href;

    footer.innerHTML = `
        <div class="footer-1 footer-wrap">
            <div class="footer-widgets-wrapper text-white bg-cover" style="background-image: url('${route('assets/img/footer-widgets-bg.png')}')">
                <div class="container">
                    <div class="row">
                        <!-- Col 1: About Jobarn -->
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="about-jobarn pe-md-4">
                                <a href="${route('index.html')}">
                                    <img src="${route('assets/img/jobarn-logo.jpeg')}" alt="Jobarn General Trading Company LTD" style="height: 54px; width: auto; max-width: 220px; object-fit: contain; display: block; margin-bottom: 18px;">
                                </a>
                                <p>Jobarn General Trading Company Ltd is Tanzania's premier destination for enterprise technology, ergonomic workspaces, custom computers, and professional audiovisual gear.</p>
                            </div>
                        </div>

                        <!-- Col 2: Shop Categories -->
                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="single-footer-wid">
                                <div class="wid-title">
                                    <h3>Shop Gear</h3>
                                </div>
                                <ul>
                                    <li><a href="${route('collections/all.html')}">All Products</a></li>
                                    <li><a href="${route('collections/computers.html')}">Computers &amp; Laptops</a></li>
                                    <li><a href="${route('collections/monitors.html')}">Monitors &amp; Displays</a></li>
                                    <li><a href="${route('collections/pro-video.html')}">Pro Video &amp; Photo</a></li>
                                    <li><a href="${route('collections/audio-equipments.html')}">Pro Audio &amp; Mics</a></li>
                                    <li><a href="${route('collections/standing-desks.html')}">Desks &amp; Chairs</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Col 3: Company & Information -->
                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="single-footer-wid ps-lg-2">
                                <div class="wid-title">
                                    <h3>Company</h3>
                                </div>
                                <ul>
                                    <li><a href="${route('about.html')}">About Us</a></li>
                                    <li><a href="${route('projects.html')}">Our Projects</a></li>
                                    <li><a href="${route('news.html')}">News &amp; Updates</a></li>
                                    <li><a href="${route('faq.html')}">Help &amp; FAQs</a></li>
                                    <li><a href="${route('contact.html')}">Contact Us</a></li>
                                    <li><a href="${route('cart.html')}">My Cart</a></li>
                                    <li><a href="${route('admin/index.php')}">Admin Portal</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Col 4: Get In Touch & Location -->
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="single-footer-wid site-info-widget ps-lg-3">
                                <div class="wid-title">
                                    <h3>Get In Touch</h3>
                                </div>
                                <div class="get-in-touch">
                                    <div class="single-contact-info">
                                        <div class="icon id1">
                                            <i class="fal fa-map-marker-alt"></i>
                                        </div>
                                        <div class="contact-info">
                                            <span data-footer-address>SkyCity Mall, 1st Floor<br>Off Mlimani City, University Road<br>Dar es Salaam, Tanzania</span>
                                        </div>
                                    </div>
                                    <div class="single-contact-info">
                                        <div class="icon id2">
                                            <i class="fal fa-phone"></i>
                                        </div>
                                        <div class="contact-info">
                                            <a data-footer-phone href="tel:+255716026781" style="color:inherit; text-decoration:none;">0716 026 781</a>
                                        </div>
                                    </div>
                                    <div class="single-contact-info">
                                        <div class="icon id3">
                                            <i class="fal fa-envelope"></i>
                                        </div>
                                        <div class="contact-info">
                                            <a data-footer-email href="mailto:info@jobarn.co.tz" style="color:inherit; text-decoration:none;">info@jobarn.co.tz</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="newsletter_widget">
                                    <div class="newsletter_box">
                                        <form action="${route('contact.html')}" method="GET">
                                            <input type="email" placeholder="Enter email address" required>
                                            <button class="submit-btn" type="submit"><i class="fas fa-envelope-open"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom Bar -->
            <div class="footer-bottom">
                <div class="container align-items-center">
                    <div class="bottom-content-wrapper">
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="copy-rights">
                                    <p data-footer-copy>&copy; ${new Date().getFullYear()} <strong>Jobarn General Trading Company LTD</strong>, All Rights Reserved</p>
                                </div>
                            </div>
                            <div class="col-md-6 mt-2 mt-md-0 col-12 text-md-end">
                                <div class="social-links">
                                    <a href="#" data-social="facebook" aria-label="Facebook" style="display:none;"><i class="fab fa-facebook-f"></i></a>
                                    <a href="https://wa.me/255716026781" data-social="whatsapp" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                    <a href="#" data-social="instagram" aria-label="Instagram" style="display:none;"><i class="fab fa-instagram"></i></a>
                                    <a href="#" data-social="twitter" aria-label="Twitter" style="display:none;"><i class="fab fa-twitter"></i></a>
                                    <a href="#" data-social="youtube" aria-label="YouTube" style="display:none;"><i class="fab fa-youtube"></i></a>
                                    <a href="#" data-social="linkedin" aria-label="LinkedIn" style="display:none;"><i class="fab fa-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clean Original "Need Help? Chat with us!" Widget -->
        <a class="njwa-clean-widget" href="https://wa.me/255716026781" target="_blank" rel="noopener" aria-label="Chat with Jobarn on WhatsApp">
            <span class="njwa-clean-bubble">Need Help? Chat with us!</span>
            <span class="njwa-clean-icon">
                <i class="fa-brands fa-whatsapp"></i>
            </span>
        </a>
    `;

    // Sync footer details & social channels from admin settings
    fetch(route("assets/ecommerce/settings.json"), { cache: "no-store" })
        .then(res => res.ok ? res.json() : null)
        .then(settings => {
            if (!settings || typeof settings !== "object") return;

            const safeUrl = (val) => {
                const raw = String(val || "").trim();
                if (!raw) return "";
                if (/^https?:\/\//i.test(raw)) return raw;
                return "https://" + raw.replace(/^\/+/, "");
            };

            if (settings.contactAddress) {
                const addrEl = footer.querySelector("[data-footer-address]");
                if (addrEl) {
                    addrEl.textContent = "";
                    String(settings.contactAddress).split(/\r?\n/).forEach((line, idx) => {
                        if (idx > 0) addrEl.appendChild(document.createElement("br"));
                        addrEl.appendChild(document.createTextNode(line));
                    });
                }
            }

            if (settings.contactPhone) {
                const telDigits = String(settings.contactPhone).replace(/[^\d+]/g, "");
                const phoneEl = footer.querySelector("[data-footer-phone]");
                if (phoneEl) {
                    phoneEl.setAttribute("href", "tel:" + telDigits);
                    phoneEl.textContent = settings.contactPhone;
                }
            }

            if (settings.contactEmail) {
                const emailEl = footer.querySelector("[data-footer-email]");
                if (emailEl) {
                    emailEl.setAttribute("href", "mailto:" + settings.contactEmail);
                    emailEl.textContent = settings.contactEmail;
                }
            }

            if (settings.footerText) {
                const copyEl = footer.querySelector("[data-footer-copy]");
                if (copyEl) copyEl.textContent = settings.footerText;
            }

            const waDigits = String(settings.whatsapp || "").replace(/[^\d]/g, "");
            if (waDigits) {
                const waUrl = "https://wa.me/" + waDigits;
                const waSocial = footer.querySelector('[data-social="whatsapp"]');
                const waWidget = footer.querySelector(".njwa-clean-widget");
                if (waSocial) waSocial.setAttribute("href", waUrl);
                if (waWidget) waWidget.setAttribute("href", waUrl);
            }

            ["facebook", "instagram", "twitter", "youtube", "linkedin"].forEach(key => {
                const el = footer.querySelector(`[data-social="${key}"]`);
                if (!el) return;
                const url = safeUrl(settings[key]);
                if (url) {
                    el.setAttribute("href", url);
                    el.setAttribute("target", "_blank");
                    el.setAttribute("rel", "noopener");
                    el.style.display = "";
                } else {
                    el.style.display = "none";
                }
            });
        })
        .catch(() => {});
})();
