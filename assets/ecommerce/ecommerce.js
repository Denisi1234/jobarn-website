// Jobarn Store E-Commerce Engine (B&H Photo Video Style)
(function() {
  'use strict';

  const collection = document.querySelector('[data-product-collection]');
  const collectionCategories = collection?.dataset.collectionCategories?.split(' ');
  const catalogUrl = new URL('products.json', document.currentScript.src);
  const slidesUrl = new URL('slides.json', catalogUrl);
  const settingsUrl = new URL('settings.json', catalogUrl);
  const rootUrl = new URL('../../', catalogUrl);
  const cartUrl = new URL('../../cart.html', catalogUrl);
  const cartPage = document.querySelector('[data-cart-page]');
  const cartKey = 'jobarn_cart';
  const cartNoteKey = 'jobarn_cart_note';
  const featuredProducts = new Map();
  let products = [];
  let cart = readCart();
  let currentCategory = collection?.dataset.productCollection || 'all';
  let searchQuery = '';
  let sortBy = collection ? 'title-asc' : 'featured';
  let currentSlide = 0;
  let slideInterval = null;

  function getCollectionFilters() {
    return document.getElementById('collectionFilters');
  }

  let WHATSAPP_NUMBER = '255716026781';

  function resolveAssetUrl(source) {
    if (typeof source !== 'string' || !source.trim()) return '';
    try {
      return new URL(source.trim(), rootUrl).href;
    } catch {
      return '';
    }
  }

  function safeLinkUrl(href) {
    const raw = String(href || '').trim();
    if (!raw || raw === '#') return '';
    if (/^(javascript|data|vbscript):/i.test(raw)) return '';
    if (raw.startsWith('#')) return raw;
    try {
      return new URL(raw, rootUrl).href;
    } catch {
      return '';
    }
  }

  // Fallback catalog in case files are missing or offline
  const fallbackProducts = [
    {
      "id": "jobarn-desk-01",
      "title": "Jobarn Dual-Motor Electric Height Adjustable Standing Desk",
      "category": "desks",
      "categoryName": "Standing Desks",
      "price": 1450000,
      "comparePrice": 1750000,
      "rating": 4.9,
      "reviewsCount": 28,
      "badge": "Best Seller",
      "image": "https://images.unsplash.com/photo-1595515106969-1ce29566ff1c?auto=format&fit=crop&w=800&q=80",
      "description": "Ergonomic dual-motor sit-stand desk engineered for smooth elevation, premium load capacity (up to 125kg), memory presets, and anti-collision system.",
      "specs": ["Dual quiet motors (<45dB)", "4 programmable height presets", "Tabletop: 140cm x 70cm scratch-resistant", "3 Years Warranty"]
    },
    {
      "id": "jobarn-chair-01",
      "title": "Ergonomic Mesh High-Back Executive Office Chair",
      "category": "chairs",
      "categoryName": "Ergonomic Chairs",
      "price": 680000,
      "comparePrice": 850000,
      "rating": 4.8,
      "reviewsCount": 42,
      "badge": "Top Rated",
      "image": "https://images.unsplash.com/photo-1580481077195-c22ae24991df?auto=format&fit=crop&w=800&q=80",
      "description": "Breathable high-density mesh with dynamic lumbar support, 3D adjustable armrests, and 135-degree recline.",
      "specs": ["Adaptive dynamic lumbar support", "3D multi-directional armrests", "Heavy-duty class 4 gas lift"]
    },
    {
      "id": "jobarn-mon-01",
      "title": "34-inch UltraWide Curved 165Hz Productivity & Gaming Monitor",
      "category": "monitors",
      "categoryName": "Monitors & Displays",
      "price": 1850000,
      "comparePrice": 2200000,
      "rating": 5.0,
      "reviewsCount": 19,
      "badge": "Hot",
      "image": "https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=800&q=80",
      "description": "WQHD 3440x1440 curved immersive monitor with 99% sRGB color accuracy, USB-C 65W power delivery charging, and ultra-smooth 165Hz refresh rate.",
      "specs": ["Resolution: 3440 x 1440 WQHD", "Curvature: 1500R ergonomic", "Connectivity: USB-C 65W PD, 2x HDMI 2.1"]
    },
    {
      "id": "jobarn-cam-01",
      "title": "Canon EOS R5 Mark II Mirrorless Camera (Body Only)",
      "category": "photography",
      "categoryName": "Photography",
      "price": 8900000,
      "comparePrice": 9500000,
      "rating": 5.0,
      "reviewsCount": 14,
      "badge": "New Arrival",
      "image": "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80",
      "description": "45MP full-frame sensor, 8K 60p RAW video, Dual Pixel Intelligent AF, Accelerated Capture engine for elite photographers and filmmakers.",
      "specs": ["45MP Full-Frame Back-Illuminated Stacked Sensor", "8K 60p RAW internal recording", "Up to 30 fps electronic shutter", "Dual CFexpress type B & SD slots"]
    },
    {
      "id": "jobarn-lens-01",
      "title": "Canon RF 24-70mm f/2.8L IS USM Professional Lens",
      "category": "photography",
      "categoryName": "Photography",
      "price": 5400000,
      "comparePrice": 5800000,
      "rating": 4.9,
      "reviewsCount": 21,
      "badge": "Pro Choice",
      "image": "https://images.unsplash.com/photo-1617005082133-548c4dd27f35?auto=format&fit=crop&w=800&q=80",
      "description": "The quintessential professional workhorse zoom lens with constant f/2.8 aperture, 5 stops of Image Stabilization and Nano USM.",
      "specs": ["Constant f/2.8 maximum aperture", "5-stop Optical Image Stabilizer", "Three Aspherical & three UD elements", "Weather-sealed construction"]
    },
    {
      "id": "jobarn-mic-01",
      "title": "Studio Broadcast USB/XLR Dynamic Podcast & Stream Microphone",
      "category": "audio",
      "categoryName": "Pro Audio",
      "price": 420000,
      "comparePrice": 490000,
      "rating": 4.9,
      "reviewsCount": 31,
      "badge": "Creator Pick",
      "image": "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=800&q=80",
      "description": "Dual USB/XLR output with voice isolation technology, real-time headphone monitoring, and integrated touch panel.",
      "specs": ["Outputs: USB-C and XLR", "All-metal construction", "DSP presets & EQ via app", "Zero-latency monitoring"]
    },
    {
      "id": "jobarn-cctv-01",
      "title": "4K Ultra-HD Smart PoE Security Camera Kit (4 Cam + NVR)",
      "category": "security",
      "categoryName": "Surveillance & CCTV",
      "price": 1650000,
      "comparePrice": 1950000,
      "rating": 4.8,
      "reviewsCount": 16,
      "badge": "Security",
      "image": "https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=800&q=80",
      "description": "Complete 4K Ultra-HD commercial & residential surveillance kit with AI human/vehicle detection, color night vision, and mobile live-view.",
      "specs": ["4x 4K 8MP PoE IP Cameras", "8-Channel NVR with 2TB Surveillance HDD", "AI human and vehicle motion detection", "IP67 weatherproof metal housings"]
    },
    {
      "id": "jobarn-net-01",
      "title": "Enterprise WiFi 6 Dual-Band Mesh Access Point",
      "category": "networking",
      "categoryName": "Networking",
      "price": 480000,
      "comparePrice": 560000,
      "rating": 4.9,
      "reviewsCount": 27,
      "badge": "Enterprise",
      "image": "https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=800&q=80",
      "description": "High-density enterprise ceiling-mount WiFi 6 AP with 5.3 Gbps aggregate throughput and support for 300+ concurrent clients.",
      "specs": ["WiFi 6 (802.11ax) technology", "5.3 Gbps aggregate over-the-air throughput", "Powered with 802.3at PoE+", "Centrally managed"]
    },
    {
      "id": "jobarn-laptop-01",
      "title": "Business Pro Ultrabook 14-inch (Core i7 / 16GB RAM / 1TB SSD)",
      "category": "computers",
      "categoryName": "Computers",
      "price": 2850000,
      "comparePrice": 3200000,
      "rating": 4.7,
      "reviewsCount": 12,
      "badge": "Hot",
      "image": "https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=800&q=80",
      "description": "Ultra-slim magnesium-alloy lightweight chassis with vibrant 14-inch IPS display, all-day battery life, and rapid Thunderbolt 4 ports.",
      "specs": ["Intel Core i7 13th Gen", "16GB DDR5 5200MHz RAM", "1TB NVMe PCIe 4.0 SSD", "Thunderbolt 4 & backlit keyboard"]
    },
    {
      "id": "jobarn-peg-01",
      "title": "Jobarn Modular Workspace Pegboard Desk Organizer Kit",
      "category": "accessories",
      "categoryName": "Accessories",
      "price": 185000,
      "comparePrice": 220000,
      "rating": 4.7,
      "reviewsCount": 35,
      "badge": "Popular",
      "image": "https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=800&q=80",
      "description": "Heavy-duty steel desk-clamped pegboard with modular shelves, hooks, and cable managers.",
      "specs": ["No-drill desk clamp mount", "Includes 2 shelves, 5 hooks, 2 containers", "Powder-coated scratch resistant finish"]
    },
    {
      "id": "jobarn-light-01",
      "title": "ScreenBar Pro Monitor Light Bar with Wireless Controller",
      "category": "accessories",
      "monitorType": "accessories",
      "categoryName": "Accessories",
      "price": 240000,
      "comparePrice": 290000,
      "rating": 4.9,
      "reviewsCount": 23,
      "badge": "Top Rated",
      "image": "https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80",
      "description": "Asymmetric optical design illuminates only the desk space without screen reflection or glare.",
      "specs": ["Zero screen reflection asymmetric optical design", "Wireless rotary desktop dial", "Auto-dimming ambient sensor", "Adjustable 2700K - 6500K color temp"]
    },
    {
      "id": "jobarn-cam-02",
      "title": "Sony Cinema Line FX3 Full-Frame Cinema Camera",
      "category": "pro-video",
      "categoryName": "Pro Video",
      "price": 9800000,
      "comparePrice": 10500000,
      "rating": 5.0,
      "reviewsCount": 18,
      "badge": "Cinema",
      "image": "https://images.unsplash.com/photo-1502982720700-bfff97f2ecac?auto=format&fit=crop&w=800&q=80",
      "description": "Compact full-frame camera designed for solo creators with active cooling for uninterrupted 4K 120p recording and S-Cinetone.",
      "specs": ["10.2MP Full-Frame Back-Illuminated Exmor R CMOS Sensor", "4K 120p internal 10-bit 4:2:2", "S-Cinetone, S-Log3 and HLG", "Compact cage-free design"]
    },
    {
      "id": "jobarn-drone-01",
      "title": "DJI Mavic 3 Pro Cine Drone Combo with RC Pro Controller",
      "category": "drones",
      "categoryName": "Drones",
      "price": 7800000,
      "comparePrice": 8500000,
      "rating": 4.9,
      "reviewsCount": 15,
      "badge": "Top Drone",
      "image": "https://images.unsplash.com/photo-1508614589041-895b88991e3e?auto=format&fit=crop&w=800&q=80",
      "description": "Triple-camera system with Hasselblad 4/3 CMOS sensor, Apple ProRes encoding, and 43 minutes max flight time.",
      "specs": ["Hasselblad 4/3 CMOS Camera", "Tri-Camera Optical Zoom System", "Omnidirectional Obstacle Sensing", "15km HD Video Transmission"]
    },
    {
      "id": "jobarn-light-02",
      "title": "Aputure Amaran 200d S Daylight LED Video Monolight",
      "category": "lighting",
      "categoryName": "Lighting",
      "price": 950000,
      "comparePrice": 1150000,
      "rating": 4.8,
      "reviewsCount": 20,
      "badge": "Studio Pro",
      "image": "https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=800&q=80",
      "description": "Ultra-high color quality 200W daylight point-source LED with Bowens mount, Sidus Link app control, and silent fan.",
      "specs": ["5600K Color Temperature", "CRI 96, TLCI 99, SSI (D56) 87", "Universal Bowens Mount", "Bluetooth wireless app control"]
    },
    {
      "id": "jobarn-optics-01",
      "title": "Celestron NexStar 8SE Computerized Schmidt-Cassegrain Telescope",
      "category": "optics",
      "categoryName": "Optics",
      "price": 4200000,
      "comparePrice": 4600000,
      "rating": 5.0,
      "reviewsCount": 9,
      "badge": "Optics",
      "image": "https://images.unsplash.com/photo-1506703719100-a0f3a48c0f86?auto=format&fit=crop&w=800&q=80",
      "description": "Iconic orange tube telescope with 8-inch aperture, StarBright XLT optical coatings, and SkyAlign computerized database of 40,000+ celestial objects.",
      "specs": ["8-inch Schmidt-Cassegrain aperture", "Fully automated GoTo mount with 40,000 object database", "StarBright XLT high transmission optical coatings", "Steel tripod with accessory tray"]
    }
  ];

  async function loadSettings() {
    try {
      const res = await fetch(settingsUrl, { cache: 'no-store' });
      if (!res.ok) return;
      const settings = await res.json();
      if (!settings || typeof settings !== 'object') return;

      const waDigits = String(settings.whatsapp || '').replace(/[^\d]/g, '');
      if (waDigits) {
        WHATSAPP_NUMBER = waDigits;
      }

      if (settings.contactPhone) {
        const telDigits = String(settings.contactPhone).replace(/[^\d+]/g, '');
        document.querySelectorAll('.bh-phone-number').forEach(el => {
          el.setAttribute('href', `tel:${telDigits}`);
          const icon = el.querySelector('i');
          el.textContent = settings.contactPhone + ' ';
          if (icon) el.appendChild(icon);
        });
        document.querySelectorAll('.bh-drawer-phone').forEach(el => {
          el.setAttribute('href', `tel:${telDigits}`);
          const span = el.querySelector('span');
          if (span) span.textContent = settings.contactPhone;
        });
      }

      if (settings.contactAddress) {
        document.querySelectorAll('.bh-drawer-location').forEach(el => {
          el.textContent = '';
          String(settings.contactAddress).split(/\r?\n/).forEach((line, idx) => {
            if (idx > 0) el.appendChild(document.createElement('br'));
            el.appendChild(document.createTextNode(line));
          });
        });
      }
    } catch {
      // Keep default settings if settings.json is unavailable
    }
  }

  async function loadSlides() {
    const heroContainer = document.querySelector('.bh-hero-slider-section .bh-container');
    if (!heroContainer) return;
    try {
      const res = await fetch(slidesUrl, { cache: 'no-store' });
      if (!res.ok) return;
      const data = await res.json();
      if (!Array.isArray(data)) return;

      const activeSlides = data
        .filter(s => s && s.active !== false && String(s.image || '').trim() !== '')
        .sort((a, b) => (Number(a.order) || 0) - (Number(b.order) || 0));

      if (activeSlides.length === 0) return;

      const carousel = document.createElement('div');
      carousel.className = 'bh-hero-carousel';
      const track = document.createElement('div');
      track.className = 'bh-slides-track';

      activeSlides.forEach((slide, idx) => {
        const slideEl = document.createElement('div');
        slideEl.className = 'bh-slide' + (idx === 0 ? ' active' : '');

        const img = document.createElement('img');
        img.className = 'bh-slide-img';
        img.src = resolveAssetUrl(slide.image);
        img.alt = slide.title || 'Jobarn Store Promotion';
        slideEl.appendChild(img);

        const overlay = document.createElement('div');
        overlay.className = 'bh-slide-overlay';
        slideEl.appendChild(overlay);

        const contentBox = document.createElement('div');
        contentBox.className = 'bh-slide-content';
        let hasTextContent = false;

        if (slide.subtitle && String(slide.subtitle).trim()) {
          const sub = document.createElement('p');
          sub.className = 'bh-slide-subtitle';
          sub.textContent = String(slide.subtitle).trim();
          contentBox.appendChild(sub);
          hasTextContent = true;
        }
        if (slide.title && String(slide.title).trim()) {
          const title = document.createElement('h2');
          title.className = 'bh-slide-title';
          title.textContent = String(slide.title).trim();
          contentBox.appendChild(title);
          hasTextContent = true;
        }
        const rawHref = slide.buttonLink || slide.link || '';
        const href = safeLinkUrl(rawHref);
        const ctaLabel = String(slide.buttonText || slide.ctaText || '').trim();
        if (ctaLabel && href) {
          const btn = document.createElement('a');
          btn.className = 'bh-slide-btn';
          btn.href = href;
          btn.textContent = ctaLabel + ' ';
          const icon = document.createElement('i');
          icon.className = 'fa-solid fa-arrow-right';
          btn.appendChild(icon);
          contentBox.appendChild(btn);
          hasTextContent = true;
        }
        if (hasTextContent) slideEl.appendChild(contentBox);

        track.appendChild(slideEl);
      });

      carousel.appendChild(track);

      if (activeSlides.length > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.id = 'sliderPrevBtn';
        prevBtn.className = 'bh-slider-nav bh-slider-prev';
        prevBtn.setAttribute('aria-label', 'Previous slide');
        prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';

        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.id = 'sliderNextBtn';
        nextBtn.className = 'bh-slider-nav bh-slider-next';
        nextBtn.setAttribute('aria-label', 'Next slide');
        nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';

        const dotsWrap = document.createElement('div');
        dotsWrap.className = 'bh-slider-dots';
        activeSlides.forEach((_, idx) => {
          const dot = document.createElement('button');
          dot.type = 'button';
          dot.className = 'bh-dot' + (idx === 0 ? ' active' : '');
          dot.setAttribute('data-index', String(idx));
          dot.setAttribute('aria-label', `Slide ${idx + 1}`);
          dotsWrap.appendChild(dot);
        });

        carousel.append(prevBtn, nextBtn, dotsWrap);
      }

      const heroVideo = document.getElementById('heroPlainVideo');
      if (heroVideo && typeof heroVideo.pause === 'function') heroVideo.pause();
      const videoBanner = document.getElementById('heroVideoBanner');
      if (videoBanner) videoBanner.style.display = 'none';
      heroContainer.querySelector('.bh-hero-carousel')?.remove();
      heroContainer.appendChild(carousel);

      currentSlide = 0;
      initSlider();
    } catch {
      // Keep default hero video banner if slides.json is unavailable
    }
  }

  async function loadProducts() {
    try {
      const res = await fetch(catalogUrl, { cache: 'no-store' });
      if (res.ok) {
        const data = await res.json();
        if (Array.isArray(data)) {
          products = data;
        } else {
          products = fallbackProducts;
        }
      } else {
        products = fallbackProducts;
      }
    } catch (e) {
      products = fallbackProducts;
    }

    const filters = getCollectionFilters();
    if (filters) setupCollectionFilters();
    renderProducts();
  }

  const collectionPrice = new Intl.NumberFormat('en-TZ', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  function collectionGroup(product) {
    if (currentCategory === 'monitors') {
      return product.monitorType || (product.category === 'monitors' ? 'desktop' : '');
    }
    if (collectionCategories && !collectionCategories.includes(product.category)) return '';
    if (currentCategory === 'keyboards') {
      const isKeyboardProduct = product.category === 'keyboards'
        || (product.category === 'accessories' && (
          /\bkeyboards?\b|\bmouse\b|\bmice\b|\btrackpad\b/i.test(product.title || '')
          || /^jobarn-kbd-/i.test(product.id || '')
          || /keyboards?/i.test(product.categoryName || '')
        ));
      if (!isKeyboardProduct) return '';
    }
    if (currentCategory === 'gaming' && product.category !== 'gaming' && !/\bgaming\b/i.test(product.title)) return '';
    if (currentCategory === 'upgrades' && !product.isFeatured) return '';
    if (currentCategory === 'deals' && !(Number(product.comparePrice) > Number(product.price))) return '';
    if (currentCategory === 'used' && product.category !== 'used' && product.condition !== 'used') return '';
    return product.category;
  }

  function setupCollectionFilters() {
    const filters = getCollectionFilters();
    if (!filters) return;
    const controls = filters.elements;
    const categoryCounts = new Map();
    const categoryLabels = new Map();
    if (currentCategory === 'monitors') {
      categoryLabels.set('accessories', 'Computer Monitor Accessories');
      categoryLabels.set('desktop', 'Computer Monitors');
      categoryLabels.set('portable', 'Portable Monitors');
    } else if (currentCategory === 'chairs') {
      categoryLabels.set('chairs', 'Chairs');
    } else if (currentCategory === 'keyboards') {
      categoryLabels.set('accessories', 'Keyboards');
    }
    let highest = 0;
    for (const product of products) {
      const group = collectionGroup(product);
      if (!group) continue;
      highest = Math.max(highest, Number(product.price) || 0);
      categoryCounts.set(group, (categoryCounts.get(group) || 0) + 1);
      if (!categoryLabels.has(group)) categoryLabels.set(group, product.categoryName || group);
    }
    document.getElementById('collectionHighestPrice').textContent =
      `The highest price is ${collectionPrice.format(highest)} TZS`;

    const categoryOptions = document.getElementById('collectionCategoryOptions');
    if (categoryLabels.size === 0) categoryOptions.textContent = 'No categories available.';
    for (const [group, name] of categoryLabels) {
      const label = document.createElement('label');
      label.className = 'collection-option';
      const option = document.createElement('input');
      option.type = 'checkbox';
      option.name = 'category';
      option.value = group;
      const text = document.createElement('span');
      const total = document.createElement('span');
      total.className = 'collection-muted';
      const count = categoryCounts.get(group) || 0;
      total.textContent = `(${count})`;
      text.append(`${name} `, total);
      option.disabled = count === 0;
      option.addEventListener('change', renderProducts);
      label.append(option, text);
      categoryOptions.append(label);
    }

    for (const control of [controls.minRange, controls.maxRange, controls.minPrice, controls.maxPrice]) {
      control.max = highest;
    }
    controls.maxRange.defaultValue = highest;
    controls.maxPrice.defaultValue = highest;
    controls.maxRange.value = highest;
    controls.maxPrice.value = highest;

    function updatePrice(control) {
      const minChanged = control.name.startsWith('min');
      const value = control.value === '' ? (minChanged ? 0 : highest) : Number(control.value);
      const min = minChanged ? Math.min(Number(controls.maxPrice.value), Math.max(0, value)) : Number(controls.minPrice.value);
      const max = minChanged ? Number(controls.maxPrice.value) : Math.max(min, Math.min(highest, value));
      controls.minRange.value = controls.minPrice.value = min;
      controls.maxRange.value = controls.maxPrice.value = max;
      renderProducts();
    }

    controls.minRange.addEventListener('input', () => updatePrice(controls.minRange));
    controls.maxRange.addEventListener('input', () => updatePrice(controls.maxRange));
    controls.minPrice.addEventListener('change', () => updatePrice(controls.minPrice));
    controls.maxPrice.addEventListener('change', () => updatePrice(controls.maxPrice));
    controls.inStock.addEventListener('change', renderProducts);
    controls.search.addEventListener('input', () => {
      searchQuery = controls.search.value.trim();
      renderProducts();
    });
    filters.addEventListener('submit', event => event.preventDefault());
    filters.addEventListener('reset', () => {
      searchQuery = '';
      requestAnimationFrame(renderProducts);
    });

    const searchLink = document.querySelector('[href="#collectionSearch"]');
    if (searchLink) {
      searchLink.addEventListener('click', event => {
        event.preventDefault();
        const searchBox = document.getElementById('collectionSearch');
        if (searchBox) searchBox.open = true;
        controls.search?.focus();
      });
    }
  }

  function renderCollectionCards(grid, filtered) {
    grid.setAttribute('aria-busy', 'false');
    if (filtered.length === 0) {
      const message = document.createElement('p');
      message.className = 'collection-empty';
      message.textContent = products.some(collectionGroup)
        ? 'No products match your filters.'
        : 'No products are listed in this collection yet.';
      grid.replaceChildren(message);
      return;
    }

    grid.replaceChildren(...filtered.map(product => {
      const card = document.createElement('button');
      card.type = 'button';
      card.className = 'collection-card';
      const icons = { monitors: 'fa-desktop', chairs: 'fa-chair' };
      const placeholderIcon = icons[product.category] || 'fa-box';
      card.innerHTML = `
        <span class="collection-card-image" aria-hidden="true"><i class="fal ${placeholderIcon}"></i></span>
        <span>
          <span class="collection-card-title"></span>
          <span class="collection-card-price"></span>
        </span>
      `;
      if (product.image) {
        const image = new Image();
        image.alt = product.title || '';
        image.loading = 'lazy';
        image.src = new URL(product.image, new URL('../../', catalogUrl)).href;
        image.addEventListener('error', () => {
          image.style.display = 'none';
        }, { once: true });
        card.querySelector('.collection-card-image').append(image);
      }
      card.querySelector('.collection-card-title').textContent = product.title;
      card.querySelector('.collection-card-price').textContent = `${collectionPrice.format(product.price)} TZS`;
      card.addEventListener('click', () => openModal(product.id));
      return card;
    }));
  }

  function formatTZS(amount) {
    return 'TZS ' + Number(amount).toLocaleString('en-US');
  }

  function renderProducts() {
    const grid = document.getElementById('productGrid');
    const countBadge = document.getElementById('productCountBadge');
    const catalogTitle = document.getElementById('currentCatalogTitle');
    if (!grid) return;

    const filters = getCollectionFilters();
    const controls = filters?.elements;
    const selectedGroups = filters
      ? Array.from(filters.querySelectorAll('input[name="category"]:checked:not(:disabled)'), option => option.value)
      : [];
    const query = searchQuery.toLowerCase();

    let filtered = products.filter(p => {
      // Exclude deactivated products from storefront
      if (p.active === false) return false;

      const matchSearch = !query ||
        p.title.toLowerCase().includes(query) ||
        p.description?.toLowerCase().includes(query) ||
        p.category?.toLowerCase().includes(query);
      if (!matchSearch) return false;

      const group = collectionGroup(p);
      if (controls) {
        const minVal = Number(controls.minPrice?.value) || 0;
        const maxVal = Number(controls.maxPrice?.value) || Infinity;
        return Boolean(group)
          && (selectedGroups.length === 0 || selectedGroups.includes(group))
          && (!controls.inStock?.checked || p.inStock !== false)
          && p.price >= minVal
          && p.price <= maxVal;
      }

      if (collection) {
        return Boolean(group);
      }

      if (currentCategory === 'deals') {
        return Number(p.comparePrice) > Number(p.price);
      }
      if (currentCategory === 'upgrades') {
        return Boolean(p.isFeatured);
      }
      if (currentCategory === 'used') {
        return p.category === 'used' || p.condition === 'used';
      }
      return currentCategory === 'all' || p.category === currentCategory;
    });

    // Sorting
    if (sortBy === 'price-asc') {
      filtered.sort((a, b) => a.price - b.price);
    } else if (sortBy === 'price-desc') {
      filtered.sort((a, b) => b.price - a.price);
    } else if (sortBy === 'rating') {
      filtered.sort((a, b) => (b.rating || 0) - (a.rating || 0));
    } else if (sortBy === 'title-asc') {
      filtered.sort((a, b) => a.title.localeCompare(b.title));
    } else if (sortBy === 'title-desc') {
      filtered.sort((a, b) => b.title.localeCompare(a.title));
    }

    if (countBadge) {
      const count = `${filtered.length} ${filtered.length === 1 ? 'product' : 'products'}`;
      countBadge.innerText = collection ? count : `Showing ${count}`;
    }

    if (collection) {
      renderCollectionCards(grid, filtered);
      return;
    }

    if (catalogTitle) {
      if (searchQuery) {
        catalogTitle.innerText = `Search results for "${searchQuery}"`;
      } else if (currentCategory === 'all') {
        catalogTitle.innerText = 'Featured Products & Deal Zone';
      } else if (currentCategory === 'deals') {
        catalogTitle.innerText = 'Gift Ideas & Special Deals';
      } else {
        catalogTitle.innerText = currentCategory.toUpperCase() + ' Catalog';
      }
    }

    if (filtered.length === 0) {
      grid.innerHTML = `
        <div style="grid-column: 1 / -1; background:#fff; text-align: center; padding: 60px 20px; color: #64748b;">
          <i class="fa-solid fa-box-open" style="font-size: 48px; color: #cbd5e1; margin-bottom: 16px; display: block;"></i>
          <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">No items found</h3>
          <p>Try searching with another keyword or pick a category from the navigation bar above.</p>
        </div>
      `;
      return;
    }

    grid.innerHTML = filtered.map(product => {
      const compareHtml = product.comparePrice && product.comparePrice > product.price 
        ? `<span class="bh-price-original">${formatTZS(product.comparePrice)}</span>` 
        : '';
      const badgeHtml = product.badge 
        ? `<span class="bh-card-badge">${product.badge}</span>` 
        : '';

      const ratingNum = product.rating || 4.8;
      const reviews = product.reviewsCount || Math.floor(Math.random() * 30 + 10);

      return `
        <div class="bh-product-card" data-id="${product.id}">
          ${badgeHtml}
          <div class="bh-card-img-wrap">
            <img src="${resolveAssetUrl(product.image) || product.image}" alt="${product.title}" loading="lazy" onerror="this.src='https://placehold.co/400x400?text=Jobarn'">
          </div>
          <div class="bh-card-info">
            <span class="bh-card-cat">${product.categoryName || product.category}</span>
            <h3 class="bh-card-title"><button type="button">${product.title}</button></h3>
            
            <div class="bh-card-rating">
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star-half-stroke"></i>
              <span>(${reviews})</span>
            </div>

            <div class="bh-card-price-box">
              <div class="bh-price-row">
                <span class="bh-price-current">${formatTZS(product.price)}</span>
                ${compareHtml}
              </div>
              <div class="bh-stock-status">
                ${product.inStock === false ? 'Out of stock' : '<i class="fa-solid fa-check"></i> In Stock & Ready to Ship'}
              </div>
              <button class="bh-btn-add" type="button" ${product.inStock === false ? 'disabled' : ''}>
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  function readCart() {
    try {
      const saved = JSON.parse(localStorage.getItem(cartKey) || '[]');
      if (!Array.isArray(saved)) return [];
      return saved.filter(item => item && typeof item.id === 'string'
        && typeof item.title === 'string' && Number.isFinite(Number(item.price))
        && Number(item.price) >= 0 && Number.isSafeInteger(Number(item.quantity))
        && Number(item.quantity) > 0)
        .map(item => ({ ...item, price: Number(item.price), quantity: Number(item.quantity) }));
    } catch {
      return [];
    }
  }

  function saveCart() {
    localStorage.setItem(cartKey, JSON.stringify(cart));
    if (cart.length === 0) {
      localStorage.removeItem(cartNoteKey);
      if (cartPage) document.getElementById('cartNote').value = '';
    }
    updateCartUI();
  }

  function updateCartUI() {
    const badge = document.getElementById('cartBadge');
    if (badge) {
      const count = cart.reduce((total, item) => total + item.quantity, 0);
      badge.textContent = count;
      badge.style.display = count > 0 || collection || cartPage ? 'inline-block' : 'none';
    }
    if (!cartPage) return;

    const items = document.getElementById('cartItems');
    const active = document.activeElement;
    const action = active?.dataset.cartAction;
    const activeId = action && active.closest('tr')?.dataset.productId;
    const template = document.getElementById('cartRowTemplate');
    const formatCartPrice = amount => collectionPrice.format(amount) + ' TZS';
    let total = 0;
    const rows = cart.map(item => {
      const row = template.content.firstElementChild.cloneNode(true);
      row.dataset.productId = item.id;
      const lineTotal = item.price * item.quantity;
      total += lineTotal;

      row.querySelector('.cart-product-name').textContent = item.title;
      row.querySelector('.cart-quantity-value').textContent = item.quantity;
      row.querySelector('.cart-line-total').textContent = formatCartPrice(lineTotal);
      const image = row.querySelector('img');
      if (item.image) {
        image.addEventListener('error', () => { image.hidden = true; }, { once: true });
        image.src = resolveAssetUrl(item.image) || item.image;
        image.hidden = false;
      }
      row.querySelector('[data-cart-action="decrease"]').setAttribute('aria-label', 'Decrease quantity for ' + item.title);
      row.querySelector('[data-cart-action="increase"]').setAttribute('aria-label', 'Increase quantity for ' + item.title);
      row.querySelector('[data-cart-action="remove"]').setAttribute('aria-label', 'Remove ' + item.title);
      return row;
    });

    items.replaceChildren(...rows);
    document.getElementById('cartEmpty').hidden = rows.length > 0;
    document.getElementById('cartTable').hidden = rows.length === 0;
    document.getElementById('cartSummary').hidden = rows.length === 0;
    document.getElementById('cartTotal').textContent = formatCartPrice(total);

    if (activeId) {
      const row = rows.find(item => item.dataset.productId === activeId);
      const next = row?.querySelector('[data-cart-action="' + action + '"]');
      (next || document.getElementById('cartContinue')).focus();
    }
  }

  function setupCartPage() {
    const note = document.getElementById('cartNote');
    note.value = localStorage.getItem(cartNoteKey) || '';
    note.addEventListener('input', () => localStorage.setItem(cartNoteKey, note.value));
    document.getElementById('whatsappCheckoutBtn').addEventListener('click', checkoutWhatsApp);
    document.getElementById('cartItems').addEventListener('click', event => {
      const button = event.target.closest('[data-cart-action]');
      if (!button) return;
      const id = button.closest('tr').dataset.productId;
      if (button.dataset.cartAction === 'remove') {
        removeFromCart(id);
      } else {
        updateQty(id, button.dataset.cartAction === 'increase' ? 1 : -1);
      }
    });
  }

  function addToCart(id) {
    const product = findProduct(id);
    if (!product || product.inStock === false || product.inquiryOnly) return;

    cart = readCart();
    const existing = cart.find(item => item.id === id);
    if (existing) {
      existing.quantity += 1;
    } else {
      cart.push({
        id: product.id,
        title: product.title,
        price: product.price,
        image: product.image,
        quantity: 1
      });
    }

    saveCart();
    window.location.assign(cartUrl.href);
  }

  function updateQty(id, delta) {
    cart = readCart();
    const item = cart.find(product => product.id === id);
    if (!item) return;
    item.quantity += delta;
    if (item.quantity <= 0) cart = cart.filter(product => product.id !== id);
    saveCart();
  }

  function removeFromCart(id) {
    cart = readCart().filter(product => product.id !== id);
    saveCart();
  }

  function findProduct(id) {
    return featuredProducts.get(id) || products.find(product => product.id === id);
  }

  function bindFeaturedProductCards() {
    for (const card of document.querySelectorAll('[data-featured-product]')) {
      const id = card.dataset.featuredProduct;
      const name = card.querySelector('h3');
      const priceLabel = card.querySelector('.upstore-current-price, .bh-dz-current-price').textContent.trim();
      const price = Number(priceLabel.replace(/[^\d.]/g, ''));
      const photo = card.querySelector('img');
      featuredProducts.set(id, {
        id,
        title: name.title || name.textContent.trim(),
        price,
        priceLabel,
        image: photo.src,
        galleryLead: card.dataset.productImage,
        images: card.dataset.productImages?.split('|'),
        description: card.dataset.productDescription || card.querySelector('.upstore-card-sub')?.textContent?.trim(),
        specs: card.dataset.productFeatures?.split('|'),
        inquiryOnly: !priceLabel.endsWith('TZS') || !Number.isFinite(price) || price <= 0
      });
      card.addEventListener('click', () => openModal(id));
    }
  }

  function openModal(id) {
    const prod = findProduct(id);
    if (!prod) return;

    const modal = document.getElementById('productModalOverlay');
    const content = document.getElementById('productModalContent');
    if (!modal || !content) return;
    const price = prod.priceLabel || `${collectionPrice.format(prod.price)} TZS`;
    const description = content.querySelector('.bh-modal-desc');
    const features = content.querySelector('.bh-modal-features');
    const specs = Array.isArray(prod.specs) ? prod.specs.filter(spec => typeof spec === 'string' && spec.trim()) : [];
    content.querySelector('#productModalTitle').textContent = prod.title;
    content.querySelector('.bh-modal-price').textContent = price;
    description.textContent = prod.description || '';
    description.hidden = !prod.description;
    features.hidden = specs.length === 0;
    features.querySelector('ul').replaceChildren(...specs.map(spec => {
      const item = document.createElement('li');
      const colon = spec.indexOf(':');
      if (colon > 0) {
        const label = document.createElement('strong');
        label.textContent = spec.slice(0, colon + 1);
        item.append(label, spec.slice(colon + 1));
      } else {
        item.textContent = spec;
      }
      return item;
    }));

    const addButton = content.querySelector('.bh-btn-add');
    addButton.hidden = Boolean(prod.inquiryOnly);
    addButton.disabled = prod.inStock === false;
    addButton.textContent = addButton.disabled ? 'Out of stock' : 'Add to cart';
    addButton.onclick = () => {
      closeModal();
      addToCart(prod.id);
    };
    const inquiry = encodeURIComponent(`Hi Jobarn! Inquiring about ${prod.title} (${price})`);
    content.querySelector('.bh-btn-checkout').href = `https://wa.me/${WHATSAPP_NUMBER}?text=${inquiry}`;

    renderModalGallery(content, prod);
    const chat = document.querySelector('[data-site-footer] .site-footer-chat');
    const modalChat = modal.querySelector('.bh-modal-chat');
    modalChat.replaceChildren();
    if (chat) modalChat.append(chat.cloneNode(true));
    if (!modal.open) modal.showModal();
    content.scrollTop = 0;
  }

  function renderModalGallery(content, product) {
    const mainImage = content.querySelector('.bh-modal-img');
    const rail = content.querySelector('.bh-modal-thumbs');
    const root = new URL('../../', catalogUrl);
    const extraImages = Array.isArray(product.images) ? product.images : [];
    const imageUrls = [product.galleryLead, product.image, ...extraImages].flatMap(source => {
      if (typeof source !== 'string' || !source.trim()) return [];
      try {
        return [new URL(source, root).href];
      } catch {
        return [];
      }
    });
    const images = [...new Set(imageUrls)];

    mainImage.hidden = true;
    mainImage.removeAttribute('src');
    let selectedIndex = 0;
    let tryNextImage = true;
    mainImage.onload = () => { tryNextImage = false; };
    mainImage.onerror = () => {
      mainImage.hidden = true;
      if (tryNextImage && selectedIndex < images.length - 1) selectImage(selectedIndex + 1);
    };
    rail.hidden = images.length === 0;
    const thumbnails = images.map((source, index) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'bh-modal-thumb';
      button.setAttribute('aria-label', `Show image ${index + 1} of ${images.length}`);

      const fallback = document.createElement('i');
      fallback.className = 'fas fa-box';
      fallback.setAttribute('aria-hidden', 'true');
      const image = new Image();
      image.alt = '';
      image.onerror = () => { image.hidden = true; };
      image.src = source;
      button.append(fallback, image);
      button.addEventListener('click', () => {
        tryNextImage = false;
        selectImage(index);
      });
      return button;
    });
    rail.replaceChildren(...thumbnails);
    if (images.length > 0) selectImage(0);

    function selectImage(index) {
      selectedIndex = index;
      mainImage.alt = `${product.title}, image ${index + 1}`;
      mainImage.hidden = false;
      mainImage.src = images[index];
      thumbnails.forEach((button, position) => {
        button.setAttribute('aria-pressed', String(position === index));
      });
    }
  }

  function closeModal() {
    const modal = document.getElementById('productModalOverlay');
    if (modal?.open) modal.close();
  }

  function checkoutWhatsApp() {
    cart = readCart();
    if (cart.length === 0) {
      alert('Your cart is empty! Please add products before checking out.');
      return;
    }

    let subtotal = 0;
    let message = `🛒 *NEW JOBARN ORDER REQUEST*\n`;
    message += `────────────────────\n`;
    
    cart.forEach((item, index) => {
      const itemTot = item.price * item.quantity;
      subtotal += itemTot;
      message += `${index + 1}. *${item.title}*\n`;
      message += `   Qty: ${item.quantity} × ${formatTZS(item.price)} = ${formatTZS(itemTot)}\n\n`;
    });

    message += `────────────────────\n`;
    message += `💰 *Subtotal:* ${formatTZS(subtotal)}\n`;
    message += `🚚 *Delivery Request:* Dar es Salaam / Tanzania Delivery\n`;
    message += `📍 *Order Destination:* (Please reply with your location)\n\n`;
    const instructions = document.getElementById('cartNote')?.value.trim();
    if (instructions) message += `Order special instructions\n${instructions}\n\n`;
    message += '_Sent via Jobarn Store_';

    const url = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`;
    window.open(url, '_blank', 'noopener');
  }

  // Hero Slider logic
  function showSlide(index) {
    const slides = document.querySelectorAll('.bh-slide');
    const dots = document.querySelectorAll('.bh-dot');
    if (slides.length === 0) return;

    if (index >= slides.length) index = 0;
    if (index < 0) index = slides.length - 1;
    currentSlide = index;

    slides.forEach((s, i) => s.classList.toggle('active', i === currentSlide));
    dots.forEach((d, i) => d.classList.toggle('active', i === currentSlide));
  }

  function initSlider() {
    const prevBtn = document.getElementById('sliderPrevBtn');
    const nextBtn = document.getElementById('sliderNextBtn');
    const dots = document.querySelectorAll('.bh-dot');

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        showSlide(currentSlide - 1);
        resetSliderTimer();
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        showSlide(currentSlide + 1);
        resetSliderTimer();
      });
    }

    dots.forEach(dot => {
      dot.addEventListener('click', () => {
        const idx = parseInt(dot.getAttribute('data-index') || '0', 10);
        showSlide(idx);
        resetSliderTimer();
      });
    });

    resetSliderTimer();
  }

  function resetSliderTimer() {
    if (slideInterval) clearInterval(slideInterval);
    slideInterval = setInterval(() => {
      showSlide(currentSlide + 1);
    }, 6000);
  }

  // Countdown timer simulation for Deal Zone (B&H style "Ends in 08:31:24" & "Ends in 06:41:49")
  function startDealCountdown() {
    const timerElem = document.getElementById('countdownTimer');
    const dzTimers = document.querySelectorAll('.dz-countdown');

    let seconds = 8 * 3600 + 31 * 60 + 24;
    let dzSeconds = 6 * 3600 + 41 * 60 + 49;

    setInterval(() => {
      seconds--;
      if (seconds < 0) seconds = 12 * 3600;
      const h = String(Math.floor(seconds / 3600)).padStart(2, '0');
      const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
      const s = String(seconds % 60).padStart(2, '0');
      if (timerElem) timerElem.innerText = `${h}:${m}:${s}`;

      dzSeconds--;
      if (dzSeconds < 0) dzSeconds = 8 * 3600;
      const dzh = String(Math.floor(dzSeconds / 3600)).padStart(2, '0');
      const dzm = String(Math.floor((dzSeconds % 3600) / 60)).padStart(2, '0');
      const dzs = String(dzSeconds % 60).padStart(2, '0');
      dzTimers.forEach(el => el.innerText = `${dzh}:${dzm}:${dzs}`);
    }, 1000);
  }

  // Public API
  window.JobarnStore = {
    addToCart,
    updateQty,
    removeFromCart,
    openModal,
    closeModal,
    doSearch: function() {
      const input = document.getElementById('bhSearchInput');
      if (input) {
        const q = input.value.trim();
        const grid = document.getElementById('productGrid');
        if (!grid) {
          const target = new URL('collections/all.html', rootUrl);
          if (q) target.searchParams.set('search', q);
          window.location.href = target.href;
          return;
        }
        searchQuery = q;
        renderProducts();
        const shop = document.getElementById('shopSection');
        if (shop) shop.scrollIntoView({ behavior: 'smooth' });
      }
    }
  };

  function refreshCart() {
    cart = readCart();
    if (cartPage) document.getElementById('cartNote').value = localStorage.getItem(cartNoteKey) || '';
    updateCartUI();
  }

  window.addEventListener('storage', event => {
    if (event.key === cartKey || event.key === null || (cartPage && event.key === cartNoteKey)) refreshCart();
  });
  window.addEventListener('pageshow', event => {
    if (event.persisted) refreshCart();
  });

  // DOM Loaded
  document.addEventListener('DOMContentLoaded', () => {
    loadSettings();
    updateCartUI();
    if (cartPage) {
      setupCartPage();
      return;
    }

    bindFeaturedProductCards();
    const params = new URLSearchParams(window.location.search);
    const requestedCategory = params.get('category');
    const navLinks = Array.from(document.querySelectorAll('.bh-nav-link'));
    if (navLinks.some(link => link.dataset.category === requestedCategory)) {
      currentCategory = requestedCategory;
      navLinks.forEach(link => link.classList.toggle('active', link.dataset.category === currentCategory));
    }
    searchQuery = params.get('search')?.trim() || '';

    loadProducts();
    if (!collection) {
      loadSlides();
      initSlider();
      startDealCountdown();
    }

    // Nav Category clicks
    document.querySelectorAll('a[data-category][href^="#"]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const cat = btn.getAttribute('data-category');
        currentCategory = cat;

        document.querySelectorAll('.bh-nav-link').forEach(l => l.classList.remove('active'));
        if (btn.classList.contains('bh-nav-link')) {
          btn.classList.add('active');
        }

        searchQuery = '';
        const searchInput = document.getElementById('bhSearchInput');
        if (searchInput) searchInput.value = '';

        renderProducts();
        const shop = document.getElementById('shopSection');
        if (shop) shop.scrollIntoView({ behavior: 'smooth' });
      });
    });

    // Search input
    const searchInput = document.getElementById('bhSearchInput');
    if (searchInput) {
      if (searchQuery) searchInput.value = searchQuery;
      searchInput.addEventListener('input', (e) => {
        searchQuery = e.target.value.trim();
        renderProducts();
      });
    }

    // Sort select
    const sortSelect = document.getElementById('bhSortSelect');
    if (sortSelect) {
      sortSelect.addEventListener('change', (e) => {
        sortBy = e.target.value;
        renderProducts();
      });
    }

    // Cart actions
    document.getElementById('productGrid')?.addEventListener('click', event => {
      const card = event.target.closest('.bh-product-card');
      if (!card) return;
      if (event.target.closest('.bh-btn-add')) {
        addToCart(card.dataset.id);
      } else {
        openModal(card.dataset.id);
      }
    });

    // Initialize video showcase audio toggle & click-to-play
    document.querySelectorAll('.upstore-video-card').forEach(card => {
      const video = card.querySelector('video');
      const muteBtn = card.querySelector('.upstore-video-mute-btn');
      if (!video) return;

      // Ensure autoplay starts smoothly
      video.play().catch(() => {});

      if (muteBtn) {
        muteBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          video.muted = !video.muted;
          const icon = muteBtn.querySelector('i');
          if (icon) {
            icon.className = video.muted ? 'fa-solid fa-volume-xmark' : 'fa-solid fa-volume-high';
          }
        });
      }

      card.addEventListener('click', () => {
        if (video.paused) {
          video.play();
        } else {
          video.pause();
        }
      });
    });

    // Initialize Hero plain video banner
    const heroBannerVideo = document.querySelector('.bh-hero-plain-video');
    const heroBannerMuteBtn = document.getElementById('heroVideoMuteBtn');
    if (heroBannerVideo) {
      heroBannerVideo.play().catch(() => {});
      if (heroBannerMuteBtn) {
        heroBannerMuteBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          heroBannerVideo.muted = !heroBannerVideo.muted;
          const icon = heroBannerMuteBtn.querySelector('i');
          if (icon) {
            icon.className = heroBannerVideo.muted ? 'fa-solid fa-volume-xmark' : 'fa-solid fa-volume-high';
          }
        });
      }
      heroBannerVideo.parentElement?.addEventListener('click', () => {
        if (heroBannerVideo.paused) {
          heroBannerVideo.play();
        } else {
          heroBannerVideo.pause();
        }
      });
    }

    // Initialize Mobile Navigation Drawer
    const mobileToggleBtn = document.getElementById('bhMobileMenuToggle');
    const mobileDrawer = document.getElementById('bhMobileDrawer');
    const mobileOverlay = document.getElementById('bhMobileDrawerOverlay');
    const mobileCloseBtn = document.getElementById('bhMobileDrawerClose');

    function openMobileDrawer() {
      mobileDrawer?.classList.add('active');
      mobileOverlay?.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeMobileDrawer() {
      mobileDrawer?.classList.remove('active');
      mobileOverlay?.classList.remove('active');
      document.body.style.overflow = '';
    }

    mobileToggleBtn?.addEventListener('click', openMobileDrawer);
    mobileCloseBtn?.addEventListener('click', closeMobileDrawer);
    mobileOverlay?.addEventListener('click', closeMobileDrawer);

    // Modal close
    document.getElementById('productModalCloseBtn')?.addEventListener('click', closeModal);
    document.getElementById('productModalOverlay')?.addEventListener('click', (e) => {
      if (e.target.id === 'productModalOverlay') closeModal();
    });
  });

})();
