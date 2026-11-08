(function () {
    const host = document.querySelector("[data-store-overlays]");
    if (!host) return;

    host.innerHTML = `
    <dialog class="bh-modal-overlay" id="productModalOverlay" aria-labelledby="productModalTitle">
        <div class="bh-modal-card">
            <button class="bh-modal-close" id="productModalCloseBtn" type="button" aria-label="Close product details" autofocus></button>
            <div class="bh-modal-content" id="productModalContent">
                <div class="bh-modal-grid">
                    <div class="bh-modal-gallery">
                        <div class="bh-modal-thumbs" role="group" aria-label="Product photos" hidden></div>
                        <div class="bh-modal-media">
                            <i class="fas fa-box" aria-hidden="true"></i>
                            <img class="bh-modal-img" alt="" hidden>
                        </div>
                    </div>
                    <div class="bh-modal-info">
                        <p class="bh-modal-brand">Jobarn</p>
                        <h2 id="productModalTitle"></h2>
                        <p class="bh-modal-price"></p>
                        <p class="bh-modal-desc" hidden></p>
                        <section class="bh-modal-features" aria-labelledby="productModalFeatures" hidden>
                            <h3 id="productModalFeatures">Features</h3>
                            <ul class="bh-modal-specs"></ul>
                        </section>
                        <div class="bh-modal-actions">
                            <button class="bh-btn-add" type="button"></button>
                            <a class="bh-btn-checkout" target="_blank" rel="noopener">Inquire on WhatsApp</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="jobarn-site-footer bh-modal-chat"></div>
    </dialog>
    `;
})();
