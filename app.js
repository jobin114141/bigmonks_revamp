/**
 * Connect Logistics - Interactive Web Application Logic
 */

// 1. Navigation Scroll Effect & Auto-Hide on Scroll Down / Reveal on Scroll Up
document.addEventListener('DOMContentLoaded', () => {
  // Slow down hero video playback speed to 0.5x half speed
  const heroVideo = document.getElementById('hero-bg-video');
  if (heroVideo) {
    heroVideo.playbackRate = 1.0;
  }

  const mainNav = document.getElementById('main-nav');
  let lastScrollY = window.scrollY;

  window.addEventListener('scroll', () => {
    const currentScrollY = window.scrollY;

    // Glass pill background threshold
    if (currentScrollY > 50) {
      mainNav.classList.add('scrolled');
    } else {
      mainNav.classList.remove('scrolled');
    }

    // Auto-hide when scrolling down, reveal when scrolling up
    if (currentScrollY > lastScrollY && currentScrollY > 120) {
      // Scrolling DOWN -> Hide nav
      mainNav.classList.add('nav-hidden');
    } else {
      // Scrolling UP or at top -> Show nav
      mainNav.classList.remove('nav-hidden');
    }

    lastScrollY = currentScrollY;
  });

  // Active section highlight via IntersectionObserver
  const navSections = ['about', 'services', 'operations', 'testimonials'];

  // Map each section id → ALL its nav links (desktop + mobile)
  const navLinksMap = {};
  navSections.forEach(id => {
    navLinksMap[id] = Array.from(document.querySelectorAll(`#main-nav a[href="#${id}"]`));
  });

  const mobileBrandLabel = document.getElementById('mobile-brand-label');
  const mobileActiveDot = document.getElementById('mobile-active-dot');

  function setActiveSection(activeId) {
    navSections.forEach(id => {
      navLinksMap[id].forEach(link => {
        if (id === activeId) {
          link.classList.add('nav-active');
        } else {
          link.classList.remove('nav-active');
        }
      });
    });
    // Update mobile brand label + dot
    const labels = { about: 'About', services: 'Services', operations: 'Operations', testimonials: 'Testimonials' };
    if (mobileBrandLabel) {
      mobileBrandLabel.textContent = activeId ? (labels[activeId] || 'Connect') : 'Connect';
    }
    if (mobileActiveDot) {
      mobileActiveDot.style.opacity = activeId ? '1' : '0';
    }
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        setActiveSection(entry.target.id);
      }
    });

    // If back at hero, clear all
    const anyVisible = entries.some(e => e.isIntersecting);
    if (!anyVisible && window.scrollY < 200) {
      setActiveSection(null);
    }
  }, { threshold: [0.1, 0.25], rootMargin: '-70px 0px -20% 0px' });

  navSections.forEach(id => {
    const el = document.getElementById(id);
    if (el) observer.observe(el);
  });

  // Initialize Counter Animations & Fast Scroll Reveal
  initScrollCounters();
  initScrollReveal();
});

function initScrollReveal() {
  const revealElements = document.querySelectorAll('.scroll-reveal');
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.05, rootMargin: '0px 0px 50px 0px' });

  revealElements.forEach(el => revealObserver.observe(el));
}

// Mobile nav toggle
function toggleMobileNav() {
  const nav = document.getElementById('main-nav');
  const menu = document.getElementById('mobile-nav-menu');
  const isOpen = nav.classList.contains('mobile-open');
  if (isOpen) {
    closeMobileNav();
  } else {
    nav.classList.add('mobile-open');
    menu.classList.add('nav-menu-open');
  }
}

function closeMobileNav() {
  const nav = document.getElementById('main-nav');
  const menu = document.getElementById('mobile-nav-menu');
  nav.classList.remove('mobile-open');
  menu.classList.remove('nav-menu-open');
}

// Close mobile menu when clicking outside
document.addEventListener('click', (e) => {
  const nav = document.getElementById('main-nav');
  if (nav && !nav.contains(e.target)) {
    closeMobileNav();
  }
});

// 2. Mobile Menu Toggle
function toggleMobileMenu() {
  const menu = document.getElementById('mobile-menu');
  if (menu.classList.contains('opacity-0')) {
    menu.classList.remove('opacity-0', 'pointer-events-none');
    menu.classList.add('opacity-100');
  } else {
    menu.classList.add('opacity-0', 'pointer-events-none');
    menu.classList.remove('opacity-100');
  }
}

// 3. Hero Quick Track Input Handler
function handleHeroTrack() {
  const input = document.getElementById('hero-track-input');
  const code = input.value.trim();
  if (!code) {
    showToast('Please enter a tracking ID (e.g., CN-884920)');
    return;
  }
  setSampleTrack(code);
  document.getElementById('tracking-section').scrollIntoView({ behavior: 'smooth' });
}

// 4. Accordion & Card Detail Toggle
function toggleCardDetail(id) {
  const elem = document.getElementById(id);
  const icon = document.getElementById(`${id}-icon`);
  if (!elem) return;

  if (elem.classList.contains('hidden')) {
    elem.classList.remove('hidden');
    if (icon) icon.innerText = '−';
  } else {
    elem.classList.add('hidden');
    if (icon) icon.innerText = '+';
  }
}

// 5. Service Cards Filtering
function filterServices(category) {
  const cards = document.querySelectorAll('.service-card');
  const buttons = document.querySelectorAll('.service-tab-btn');

  buttons.forEach(btn => {
    if (btn.getAttribute('onclick').includes(category)) {
      btn.classList.add('active');
    } else {
      btn.classList.remove('active');
    }
  });

  cards.forEach(card => {
    if (category === 'all' || card.classList.contains(category)) {
      card.classList.remove('hidden-card');
    } else {
      card.classList.add('hidden-card');
    }
  });

  showToast(`Showing ${category.toUpperCase()} digital solutions`);
}

// Toggle extra service cards
function toggleMoreServices() {
  const extraCards = document.querySelectorAll('.extra-service-card');
  const btnText = document.getElementById('toggle-more-services-text');
  const btnIcon = document.getElementById('toggle-more-services-icon');

  let isExpanding = false;
  extraCards.forEach(card => {
    if (card.classList.contains('hidden')) {
      card.classList.remove('hidden');
      isExpanding = true;
    } else {
      card.classList.add('hidden');
    }
  });

  if (isExpanding) {
    if (btnText) btnText.innerText = 'Show Fewer Services';
    if (btnIcon) btnIcon.style.transform = 'rotate(180deg)';
    showToast('Showing all Bigmonks digital service offerings');
  } else {
    if (btnText) btnText.innerText = 'View All Services';
    if (btnIcon) btnIcon.style.transform = 'rotate(0deg)';
  }
}

// 6. Live Shipment Tracking Telemetry Engine
const sampleShipments = {
  'CN-884920': {
    code: 'CN-884920',
    mode: 'Air Cargo Express ✈️',
    status: 'In Transit - Flight EK-204',
    origin: 'Shanghai (PVG)',
    destination: 'Dubai (DXB)',
    eta: 'Tomorrow, 08:30 AM',
    progress: 75,
    steps: [
      { name: 'Cargo Pickup at Factory (Shanghai)', time: 'Yesterday 09:00 AM', done: true },
      { name: 'Customs Export Clearance Approved', time: 'Yesterday 04:30 PM', done: true },
      { name: 'Departed Shanghai Pudong Hub', time: 'Today 02:15 AM', done: true },
      { name: 'In Transit to Dubai South Terminal', time: 'Estimated 08:30 AM', done: false, active: true },
      { name: 'Final Delivery Destination', time: 'Pending Arrival', done: false }
    ]
  },
  'UAE-992104': {
    code: 'UAE-992104',
    mode: 'Sea Freight FCL 🚢',
    status: 'Customs Clearance in Progress',
    origin: 'Rotterdam (RTM)',
    destination: 'Jebel Ali Port, Dubai',
    eta: '27 July 2026, 04:00 PM',
    progress: 50,
    steps: [
      { name: 'Container Loaded Vessel MSC Maya', time: '14 July 10:00 AM', done: true },
      { name: 'Crossed Suez Canal Transit', time: '20 July 06:12 PM', done: true },
      { name: 'Arrived Jebel Ali Port Berth 4', time: 'Today 07:00 AM', done: true, active: true },
      { name: 'Customs Duty Inspection', time: 'In Progress', done: false },
      { name: 'Dispatched to Warehouse', time: 'Pending', done: false }
    ]
  },
  'DXB-774011': {
    code: 'DXB-774011',
    mode: 'Land Fleet Heavy Haulage 🚛',
    status: 'Delivered & Signed',
    origin: 'Dubai South Warehouse',
    destination: 'Riyadh Logistics Park, KSA',
    eta: 'Delivered Today 10:15 AM',
    progress: 100,
    steps: [
      { name: 'Dispatched from Dubai Hub', time: '25 July 08:00 AM', done: true },
      { name: 'GCC Border Customs Clearance', time: '26 July 02:00 PM', done: true },
      { name: 'Out for Last-Mile Delivery', time: 'Today 08:00 AM', done: true },
      { name: 'Delivered to Recipient (Signed)', time: 'Today 10:15 AM', done: true, active: true }
    ]
  }
};

function setSampleTrack(code) {
  document.getElementById('main-tracking-input').value = code;
  searchTracking();
}

function searchTracking() {
  const input = document.getElementById('main-tracking-input');
  const code = input.value.trim().toUpperCase();
  const box = document.getElementById('tracking-result-box');

  if (!code) {
    showToast('Please enter a tracking ID');
    return;
  }

  const shipment = sampleShipments[code] || {
    code: code,
    mode: 'Standard Freight 📦',
    status: 'In Transit - On Schedule',
    origin: 'Dubai Hub (DXB)',
    destination: 'Global Partner Depot',
    eta: '2-3 Business Days',
    progress: 60,
    steps: [
      { name: 'Consignment Received at Facility', time: '26 July 09:00 AM', done: true },
      { name: 'Security & Manifest Clearance', time: '26 July 02:00 PM', done: true },
      { name: 'In Transit to Regional Center', time: 'Today 05:00 AM', done: true, active: true },
      { name: 'Last Mile Dispatch', time: 'Pending', done: false }
    ]
  };

  renderTrackingDetails(shipment, box);
}

function renderTrackingDetails(shipment, box) {
  box.classList.remove('hidden');

  let stepsHTML = shipment.steps.map(step => `
    <div class="flex items-start gap-4 relative">
      <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 z-10 
                  ${step.done ? 'bg-white text-slate-900' : step.active ? 'bg-white text-slate-900 animate-pulse' : 'bg-slate-700 text-slate-400'}">
        ${step.done ? '✓' : step.active ? '●' : '○'}
      </div>
      <div>
        <h4 class="font-bold text-sm text-white">${step.name}</h4>
        <p class="text-xs text-slate-400">${step.time}</p>
      </div>
    </div>
  `).join('');

  box.innerHTML = `
    <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 space-y-6">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-4 border-b border-slate-800">
        <div>
          <span class="text-xs text-orange-400 font-mono uppercase font-bold">Tracking Ref: ${shipment.code}</span>
          <h3 class="text-2xl font-black text-white mt-0.5">${shipment.status}</h3>
          <p class="text-xs text-slate-400">${shipment.mode} &bull; Route: ${shipment.origin} &rarr; ${shipment.destination}</p>
        </div>
        <div class="bg-slate-900 px-4 py-2 rounded-xl border border-slate-700 text-right">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Estimated Arrival</span>
          <span class="text-sm font-bold text-white">${shipment.eta}</span>
        </div>
      </div>

      <!-- Telemetry Progress Bar -->
      <div>
        <div class="flex justify-between text-xs text-slate-400 mb-2 font-medium">
          <span>Progress Complete</span>
          <span>${shipment.progress}%</span>
        </div>
        <div class="w-full bg-slate-800 h-3 rounded-full overflow-hidden p-0.5 border border-slate-700">
          <div class="bg-gradient-to-r from-slate-600 to-white h-full rounded-full transition-all duration-1000" style="width: ${shipment.progress}%"></div>
        </div>
      </div>

      <!-- Timeline Steps -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
        <div class="space-y-4">
          <h4 class="text-xs uppercase font-bold text-slate-400 tracking-wider">Milestone Timeline</h4>
          ${stepsHTML}
        </div>
        <div class="bg-slate-900 p-5 rounded-xl border border-slate-800 space-y-3">
          <h4 class="text-xs uppercase font-bold text-slate-400 tracking-wider">Live Cargo Conditions</h4>
          <div class="flex justify-between text-xs">
            <span class="text-slate-400">Container Temp:</span>
            <span class="font-mono text-white font-bold">+4.2°C (Optimal)</span>
          </div>
          <div class="flex justify-between text-xs">
            <span class="text-slate-400">GPS Coordinates:</span>
            <span class="font-mono text-slate-300">25.2048° N, 55.2708° E</span>
          </div>
          <div class="flex justify-between text-xs">
            <span class="text-slate-400">Seal Security:</span>
            <span class="font-mono text-white">Intact (Tamper-Proof)</span>
          </div>
          <button onclick="showToast('Live telemetry refreshed just now')" class="w-full mt-2 bg-white/10 hover:bg-white/20 text-white text-xs py-2 rounded-lg transition-colors">
            🔄 Refresh Telemetry Log
          </button>
        </div>
      </div>
    </div>
  `;

  showToast(`Found tracking info for ${shipment.code}`);
}

// 7. Program Experience Slide Navigation
let programYears = 15;
function prevProgramSlide() {
  showToast('Viewing previous milestone');
}

function nextProgramSlide() {
  showToast('Viewing next milestone');
}

// 8. Testimonials Multi-Card Carousel Controls
const allTestimonials = [
  {
    quote: '"Bigmonks transformed our entire web platform and mobile apps. Their technical execution, speed, and continuous support are world-class."',
    author: 'Ahmed Al Mansoor',
    role: 'Product Director, TechVentures GCC',
    badge: 'Web & Mobile App',
    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80'
  },
  {
    quote: '"Delivered our complex e-commerce architecture and cloud migration 2 weeks ahead of schedule. Code quality and UI performance are exceptional!"',
    author: 'Elena Rostova',
    role: 'VP of Technology, Apex Global E-Commerce',
    badge: 'E-Commerce & Cloud',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80'
  },
  {
    quote: '"From API design to mobile app store launch, Bigmonks handled everything seamlessly. Our active user engagement increased by 180%."',
    author: 'David Chen',
    role: 'Co-Founder & CTO, Nexus Cloud Labs',
    badge: 'SaaS & AI API',
    avatar: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=200&q=80'
  },
  {
    quote: '"The React and Node microservices Bigmonks built allowed us to scale to 500,000 active users effortlessly. Outstanding engineering partner."',
    author: 'Sarah Jenkins',
    role: 'Chief Digital Officer, Finovate UK',
    badge: 'Fintech & Microservices',
    avatar: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=200&q=80'
  },
  {
    quote: '"Their team executed our complete digital platform revamp with exceptional attention to detail, high security, and 99.99% cloud uptime."',
    author: 'Marcus Vance',
    role: 'Managing Director, Vance Tech Hub',
    badge: 'Enterprise Digital',
    avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80'
  }
];

let testimonialOffset = 0;

function renderTestimonialCards() {
  const container = document.getElementById('testimonials-cards-container');
  if (!container) return;

  const count = allTestimonials.length;
  const cardsToDisplay = [];
  for (let i = 0; i < 3; i++) {
    const itemIndex = (testimonialOffset + i) % count;
    cardsToDisplay.push(allTestimonials[itemIndex]);
  }

  container.innerHTML = cardsToDisplay.map(item => `
    <div class="bg-[#f8f9fa] p-8 md:p-10 card-rounded border border-slate-200/80 shadow-sm hover:shadow-2xl transition-all duration-500 group hover:-translate-y-2 flex flex-col justify-between">
      <div>
        <div class="flex justify-between items-center mb-6">
          <div class="flex gap-0.5">
            <svg class="w-4 h-4 text-slate-900 fill-white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <svg class="w-4 h-4 text-slate-900 fill-white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <svg class="w-4 h-4 text-slate-900 fill-white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <svg class="w-4 h-4 text-slate-900 fill-white" viewBox="0 0 24 24" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <svg class="w-4 h-4 text-slate-900 fill-white" viewBox="0 0 24 24" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          </div>
          <span class="text-[10px] font-bold uppercase tracking-widest px-3 py-1 bg-white rounded-full text-slate-700 border border-slate-200">${item.badge}</span>
        </div>
        <p class="text-slate-700 text-sm md:text-base leading-relaxed italic mb-8">${item.quote}</p>
      </div>
      <div class="pt-6 border-t border-slate-200/80 flex items-center gap-4">
        <img class="w-11 h-11 rounded-full object-cover border-2 border-white shadow-md" src="${item.avatar}" alt="${item.author}" />
        <div>
          <p class="font-bold text-slate-900 text-sm">${item.author}</p>
          <p class="text-slate-500 text-xs">${item.role}</p>
        </div>
      </div>
    </div>
  `).join('');
}

function prevTestimonialSlide() {
  testimonialOffset = (testimonialOffset - 1 + allTestimonials.length) % allTestimonials.length;
  renderTestimonialCards();
}

function nextTestimonialSlide() {
  testimonialOffset = (testimonialOffset + 1) % allTestimonials.length;
  renderTestimonialCards();
}

window.prevTestimonialSlide = prevTestimonialSlide;
window.nextTestimonialSlide = nextTestimonialSlide;

// 9. Instant Rate Estimator / Quote Calculator
function recalculateQuote() {
  const form = document.getElementById('quote-form');
  if (!form) return;

  const mode = form.elements['mode'].value;
  const weight = parseFloat(document.getElementById('quote-weight').value) || 100;
  const volume = parseFloat(document.getElementById('quote-volume').value) || 1;

  let baseRate = 500;
  let perKgRate = 3.5;
  let transitDays = '2-3 Business Days';

  if (mode === 'Sea Freight') {
    baseRate = 350;
    perKgRate = 1.2;
    transitDays = '14-18 Business Days';
  } else if (mode === 'Land Transport') {
    baseRate = 250;
    perKgRate = 1.8;
    transitDays = '3-5 Business Days';
  }

  const estimatedCost = (baseRate + (weight * perKgRate) + (volume * 45)).toFixed(2);

  document.getElementById('quote-cost-display').innerText = `$${Number(estimatedCost).toLocaleString()} USD`;
  document.getElementById('quote-time-display').innerText = `Est. Transit: ${transitDays}`;
}

function openQuoteModal(preferredMode) {
  const modal = document.getElementById('quote-modal');
  modal.classList.add('modal-active');

  if (preferredMode) {
    const radio = document.querySelector(`input[name="mode"][value="${preferredMode}"]`);
    if (radio) {
      radio.checked = true;
      recalculateQuote();
    }
  }
}

function closeQuoteModal() {
  const modal = document.getElementById('quote-modal');
  modal.classList.remove('modal-active');
}

function handleQuoteSubmit(event) {
  event.preventDefault();
  closeQuoteModal();
  const refCode = 'QT-' + Math.floor(100000 + Math.random() * 900000);
  showToast(`Quote Request Submitted! Reference ID: ${refCode}`);
}

// 10. Operations Detail Modal
const opsData = {
  'Customs Clearance & Documentation': {
    desc: 'Our specialized customs team in Dubai handles end-to-end duty declarations, tariff codes, exemption permits, and fast-track clearance for air, sea, and land cargo arriving across GCC ports.',
    hub: 'Dubai South & Jebel Ali Freezone',
    time: '< 4 Hours',
    compliance: 'World Customs Organization (WCO)'
  },
  'Warehousing & Storage Solution': {
    desc: 'State-of-the-art climate-controlled facilities featuring 24/7 CCTV security, automated inventory RFID tracking, bonded storage, and fulfillment services.',
    hub: 'Dubai South Logistics District',
    time: '24/7 Dispatch',
    compliance: 'ISO 9001:2015 Certified'
  },
  'Import / Export Advisory': {
    desc: 'Strategic consultation on international trade regulations, GCC free trade agreements, tariff reduction, customs audit defense, and supply chain compliance.',
    hub: 'Dubai International Financial Centre',
    time: 'Same Day Consult',
    compliance: 'GCC Customs Union Law'
  },
  'Freight Forwarding & Consolidation': {
    desc: 'Multi-modal air-sea transport routing, buyer consolidation services, chartered cargo vessels, and door-to-door cargo insurance.',
    hub: 'Global Network Hub',
    time: 'Scheduled Weekly Sailing',
    compliance: 'IATA & FIATA Accredited'
  }
};

// 10. Operations Dynamic Accordion Switcher
const operationsList = [
  {
    title: 'High-Throughput API Gateways & Data Pipelines',
    desc: 'Real-time event streaming, GraphQL & RESTful API gateways, Redis caching layers, and high-frequency data pipelines built for sub-50ms latency.',
    badge: '< 50ms Latency',
    tags: ['GraphQL & REST Gateways', 'Kafka Event Streams', 'Redis Caching'],
    img: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=400&q=80'
  },
  {
    title: 'Automated QA Testing & Code Audit Systems',
    desc: 'End-to-end Playwright browser test suites, static security vulnerability scanning, automated accessibility compliance, and zero-regression releases.',
    badge: 'Zero-Regression',
    tags: ['Playwright E2E Testing', 'Static Code Analysis', 'Security Audit'],
    img: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=400&q=80'
  },
  {
    title: '24/7 Production Monitoring & SRE Management',
    desc: 'Real-time Datadog telemetry, server cluster auto-scaling, instant incident response protocols, and continuous uptime SLA management.',
    badge: '24/7 Telemetry',
    tags: ['Datadog Telemetry', 'Auto-Scaling Clusters', 'Instant Incident Response'],
    img: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=400&q=80'
  },
  {
    title: 'Legacy Monolith Refactoring & Cloud Migration',
    desc: 'Seamless migration of legacy monolithic codebases into modern decoupled frontend & microservices without interrupting live user traffic.',
    badge: 'Zero Downtime',
    tags: ['Monolith to Microservices', 'Database Schema Migration', 'Code Modernization'],
    img: 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=400&q=80'
  }
];

let activeOperationIndex = 1; // Default active: Warehousing & Storage Solution

function renderOperations() {
  const container = document.getElementById('operations-accordion-list');
  if (!container) return;

  container.innerHTML = operationsList.map((op, idx) => {
    const isActive = idx === activeOperationIndex;

    if (isActive) {
      return `
        <div class="group relative flex flex-col lg:flex-row items-start lg:items-center justify-between py-10 px-6 cursor-pointer bg-white text-slate-950 rounded-2xl transition-all duration-300 shadow-xl my-4 transform scale-[1.01]"
          onclick="handleOperationClick(${idx})">
          <div class="mb-6 lg:mb-0">
            <span class="text-2xl md:text-3xl font-extrabold block mb-3 text-slate-900">${op.title}</span>
            <div class="flex flex-wrap gap-2">
              ${op.tags.map(tag => `<span class="text-[10px] uppercase font-bold bg-slate-100 px-3 py-1 rounded-full text-slate-800">${tag}</span>`).join('')}
            </div>
          </div>
          <div class="flex items-center gap-6">
            <img alt="${op.title}" class="w-36 h-24 object-cover rounded-xl border-2 border-black/20 transform -rotate-3 hidden md:block shadow-xl hover:rotate-0 transition-transform" src="${op.img}" />
            <span class="bg-slate-950 text-white w-14 h-14 rounded-full flex items-center justify-center transform group-hover:rotate-45 transition-all"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg></span>
          </div>
        </div>
      `;
    } else {
      return `
        <div class="group flex flex-col md:flex-row md:items-center justify-between py-8 cursor-pointer hover:px-4 transition-all duration-300"
          onclick="handleOperationClick(${idx})">
          <div>
            <span class="text-xl md:text-2xl font-bold block group-hover:text-white transition-colors">${op.title}</span>
            <span class="text-xs text-white/50 mt-1 block">${op.desc}</span>
          </div>
          <div class="mt-4 md:mt-0 flex items-center gap-4">
            <span class="text-xs px-3 py-1 bg-white/10 rounded-full text-white/80">${op.badge}</span>
            <span class="opacity-40 group-hover:opacity-100 group-hover:translate-x-1 transition-all"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg></span>
          </div>
        </div>
      `;
    }
  }).join('');
}

function handleOperationClick(index) {
  if (activeOperationIndex === index) {
    openOperationModal(operationsList[index].title);
  } else {
    activeOperationIndex = index;
    renderOperations();
  }
}

// 11. Back to Top Scroll
function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// 12. Interactive Liquid Corridor Simulation Vector
function selectCorridorMode(mode) {
  const btnAir = document.getElementById('corridor-btn-air');
  const btnSea = document.getElementById('corridor-btn-sea');
  const btnLand = document.getElementById('corridor-btn-land');

  const speedEl = document.getElementById('corridor-speed');
  const customsEl = document.getElementById('corridor-customs');
  const carbonEl = document.getElementById('corridor-carbon');

  [btnAir, btnSea, btnLand].forEach(btn => {
    if (btn) {
      btn.className = 'corridor-tab-btn px-6 py-3.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-black flex items-center gap-2 transition-all hover:scale-105';
    }
  });

  if (mode === 'air') {
    if (btnAir) btnAir.className = 'corridor-tab-btn active px-6 py-3.5 rounded-full bg-black text-white text-xs font-black flex items-center gap-2 transition-all shadow-lg hover:scale-105';
    if (speedEl) speedEl.textContent = '24 - 48 Hours';
    if (customsEl) customsEl.textContent = 'Express Fast-Track WCO';
    if (carbonEl) carbonEl.innerHTML = 'Certified <span class="text-white font-bold">Low-Emission Charter</span>';
    showToast('Air Priority Vector Selected');
  } else if (mode === 'sea') {
    if (btnSea) btnSea.className = 'corridor-tab-btn active px-6 py-3.5 rounded-full bg-black text-white text-xs font-black flex items-center gap-2 transition-all shadow-lg hover:scale-105';
    if (speedEl) speedEl.textContent = '12 - 18 Days';
    if (customsEl) customsEl.textContent = 'Port-to-Port Pre-Cleared';
    if (carbonEl) carbonEl.innerHTML = 'Certified <span class="text-white font-bold">Low-Emission Vessel</span>';
    showToast('Ocean FCL Vector Selected');
  } else if (mode === 'land') {
    if (btnLand) btnLand.className = 'corridor-tab-btn active px-6 py-3.5 rounded-full bg-black text-white text-xs font-black flex items-center gap-2 transition-all shadow-lg hover:scale-105';
    if (speedEl) speedEl.textContent = '24 - 72 Hours';
    if (customsEl) customsEl.textContent = 'GCC Cross-Border Seal Approval';
    if (carbonEl) carbonEl.innerHTML = 'Certified <span class="text-white font-bold">Eco-Route Telemetry</span>';
    showToast('GCC Land Vector Selected');
  }
}

window.selectCorridorMode = selectCorridorMode;

// Sticky Pinned "From a Kitchen" Scroll Shrink & Reveal Effect
function initKitchenPinnedScroll() {
  const wrapper = document.getElementById('kitchen-pinned-wrapper');
  const heading = document.getElementById('kitchen-heading');

  if (!wrapper || !heading) return;

  function updateKitchenScroll() {
    const rect = wrapper.getBoundingClientRect();
    const windowHeight = window.innerHeight;
    const maxScroll = wrapper.offsetHeight - windowHeight;

    if (maxScroll <= 0) return;

    // Sticky offset top position is 64px (sticky top-16)
    const stickyTopOffset = 64;
    const scrolled = stickyTopOffset - rect.top;
    let progress = scrolled / maxScroll;
    progress = Math.max(0, Math.min(1, progress));

    // 1. Font Size Reduction (0 -> 0.35 scroll ratio)
    const shrinkRatio = Math.min(1, progress / 0.35);

    const isDesktop = window.innerWidth >= 1024;
    const isTablet = window.innerWidth >= 640;
    const maxFs = isDesktop ? 120 : (isTablet ? 90 : 60); // Max font size in px
    const minFs = isDesktop ? 38 : (isTablet ? 32 : 26);  // Min font size in px

    const currentFs = maxFs - (shrinkRatio * (maxFs - minFs));
    heading.style.fontSize = `${currentFs}px`;
    heading.style.lineHeight = shrinkRatio > 0.4 ? '1.1' : '0.93';

    // 2. Reveal "to more..." text inline as font size reduces (0.08 -> 0.4 scroll ratio)
    const toMoreEl = document.getElementById('kitchen-to-more');
    const brEl = heading.querySelector('.kitchen-br');

    if (toMoreEl) {
      let toMoreRatio = (progress - 0.08) / 0.32;
      toMoreRatio = Math.max(0, Math.min(1, toMoreRatio));

      toMoreEl.style.opacity = toMoreRatio.toFixed(3);
      toMoreEl.style.transform = `translateX(${(1 - toMoreRatio) * 20}px)`;
    }

    if (brEl) {
      if (shrinkRatio > 0.4) {
        brEl.style.display = 'none';
        heading.style.whiteSpace = 'nowrap';
      } else {
        brEl.style.display = 'block';
        heading.style.whiteSpace = 'normal';
      }
    }

    // 3. Reveal Large Image below headline as font shrinking completes (0.12 -> 0.35 scroll ratio)
    const largeImgWrapper = document.getElementById('kitchen-large-image-wrapper');
    if (largeImgWrapper) {
      let imgRatio = (progress - 0.12) / 0.23;
      imgRatio = Math.max(0, Math.min(1, imgRatio));

      largeImgWrapper.style.opacity = imgRatio.toFixed(3);
      largeImgWrapper.style.transform = `scale(${0.95 + (imgRatio * 0.05)}) translateY(${(1 - imgRatio) * 35}px)`;

      if (imgRatio > 0.1) {
        largeImgWrapper.style.pointerEvents = 'auto';
      } else {
        largeImgWrapper.style.pointerEvents = 'none';
      }
    }

    // 4. Reveal Story Narrative Text below image (0.20 -> 0.55 scroll ratio)
    const storyTextEl = document.getElementById('kitchen-story-text');
    if (storyTextEl) {
      let storyRatio = (progress - 0.18) / 0.35;
      storyRatio = Math.max(0, Math.min(1, storyRatio));

      storyTextEl.style.opacity = storyRatio.toFixed(3);
      storyTextEl.style.transform = `translateY(${(1 - storyRatio) * 25}px)`;

      if (storyRatio > 0.1) {
        storyTextEl.style.pointerEvents = 'auto';
      } else {
        storyTextEl.style.pointerEvents = 'none';
      }
    }
  }

  window.addEventListener('scroll', updateKitchenScroll, { passive: true });
  window.addEventListener('resize', updateKitchenScroll, { passive: true });
  updateKitchenScroll();
}

// Sticky Pinned "Our Team" Scroll Shrink & Reveal Effect
function initTeamPinnedScroll() {
  const wrapper = document.getElementById('team-pinned-wrapper');
  const heading = document.getElementById('team-heading');
  const revealContent = document.getElementById('team-reveal-content');

  if (!wrapper || !heading || !revealContent) return;

  function updateTeamScroll() {
    const rect = wrapper.getBoundingClientRect();
    const windowHeight = window.innerHeight;
    const maxScroll = wrapper.offsetHeight - windowHeight;

    if (maxScroll <= 0) return;

    const stickyTopOffset = 64;
    const scrolled = stickyTopOffset - rect.top;
    let progress = scrolled / maxScroll;
    progress = Math.max(0, Math.min(1, progress));

    // 1. Font Size Reduction (0 -> 0.35 scroll ratio)
    const shrinkRatio = Math.min(1, progress / 0.35);

    const isDesktop = window.innerWidth >= 1024;
    const isTablet = window.innerWidth >= 640;
    const maxFs = isDesktop ? 120 : (isTablet ? 90 : 60); // Max font size in px
    const minFs = isDesktop ? 38 : (isTablet ? 32 : 26);  // Min font size in px

    const currentFs = maxFs - (shrinkRatio * (maxFs - minFs));
    heading.style.fontSize = `${currentFs}px`;
    heading.style.lineHeight = shrinkRatio > 0.4 ? '1.1' : '0.93';

    // 2. Reveal "of architects & builders..." text inline as font size reduces (0.08 -> 0.4 scroll ratio)
    const toMoreEl = document.getElementById('team-to-more');
    const brEl = heading.querySelector('.team-br');

    if (toMoreEl) {
      let toMoreRatio = (progress - 0.08) / 0.32;
      toMoreRatio = Math.max(0, Math.min(1, toMoreRatio));

      toMoreEl.style.opacity = toMoreRatio.toFixed(3);
      toMoreEl.style.transform = `translateX(${(1 - toMoreRatio) * 20}px)`;
    }

    if (brEl) {
      if (shrinkRatio > 0.4) {
        brEl.style.display = 'none';
        heading.style.whiteSpace = 'nowrap';
      } else {
        brEl.style.display = 'block';
        heading.style.whiteSpace = 'normal';
      }
    }

    // 3. Reveal Team Grid Cards (0.2 -> 0.65 scroll ratio)
    let revealRatio = (progress - 0.2) / 0.45;
    revealRatio = Math.max(0, Math.min(1, revealRatio));

    revealContent.style.opacity = revealRatio.toFixed(3);
    revealContent.style.transform = `translateY(${(1 - revealRatio) * 30}px)`;

    if (revealRatio > 0.05) {
      revealContent.style.pointerEvents = 'auto';
    } else {
      revealContent.style.pointerEvents = 'none';
    }
  }

  window.addEventListener('scroll', updateTeamScroll, { passive: true });
  window.addEventListener('resize', updateTeamScroll, { passive: true });
  updateTeamScroll();
}

// Call on load and immediately
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    try { renderOperations(); } catch(e){}
    try { renderTestimonialCards(); } catch(e){}
    try { initScrollCounters(); } catch(e){}
    initKitchenPinnedScroll();
    initTeamPinnedScroll();
  });
} else {
  initKitchenPinnedScroll();
  initTeamPinnedScroll();
}

// ----------------------------------------------------
// Interactive Scope Estimator Widget Functions
// ----------------------------------------------------
let currentEstimatorType = 'Web Platform';
let currentEstimatorScale = 'Scaleup Squad';

function selectEstimatorType(typeVal, el) {
  currentEstimatorType = typeVal;
  document.querySelectorAll('.estimator-type').forEach(btn => {
    btn.classList.remove('bg-black', 'text-white', 'border-black');
    btn.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
  });
  el.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
  el.classList.add('bg-black', 'text-white', 'border-black');
  updateEstimatorOutput();
}

function selectEstimatorScale(scaleVal, el) {
  currentEstimatorScale = scaleVal;
  document.querySelectorAll('.estimator-scale').forEach(btn => {
    btn.classList.remove('bg-black', 'text-white', 'border-black');
    btn.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
  });
  el.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
  el.classList.add('bg-black', 'text-white', 'border-black');
  updateEstimatorOutput();
}

function updateEstimatorOutput() {
  const outputVelocity = document.getElementById('calc-velocity');
  const outputTeam = document.getElementById('calc-team');
  const outputHub = document.getElementById('calc-hub');

  if (!outputVelocity || !outputTeam) return;

  if (currentEstimatorScale === 'Startup MVP') {
    outputVelocity.innerText = '3-4 Weeks Sprint Velocity';
    outputTeam.innerText = '1 Tech Lead, 2 Full-Stack Engineers, 1 QA';
    outputHub.innerText = 'India HQ (Kakkanad)';
  } else if (currentEstimatorScale === 'Scaleup Squad') {
    outputVelocity.innerText = '6-8 Weeks Sprint Velocity';
    outputTeam.innerText = '1 Product Architect, 4 Senior Engineers, 1 DevOps';
    outputHub.innerText = 'India HQ + Canada Hub';
  } else {
    outputVelocity.innerText = 'Continuous 24/7 Global Agile';
    outputTeam.innerText = 'Dedicated 10+ Eng Squad + 24/7 Telemetry';
    outputHub.innerText = 'India HQ & Canada Hub (Dual Center)';
  }
}

function launchEstimatedQuote() {
  const scopeSummary = `${currentEstimatorType} - ${currentEstimatorScale}`;
  openQuoteModal(scopeSummary);
}

// ----------------------------------------------------
// Live Architectural Telemetry Console Switcher
// ----------------------------------------------------
function switchTelemetryTab(tabId, el) {
  document.querySelectorAll('.telemetry-panel').forEach(panel => panel.classList.add('hidden'));
  document.querySelectorAll('.telemetry-tab-btn').forEach(btn => {
    btn.classList.remove('bg-white/15', 'text-white');
    btn.classList.add('text-slate-400');
  });

  const activePanel = document.getElementById(tabId);
  if (activePanel) {
    activePanel.classList.remove('hidden');
  }

  el.classList.remove('text-slate-400');
  el.classList.add('bg-white/15', 'text-white');
}

// ----------------------------------------------------
// Index Architecture Matrix Sandbox Switcher
// ----------------------------------------------------
function switchIndexArchTab(tabId, el) {
  document.querySelectorAll('.arch-panel').forEach(panel => panel.classList.add('hidden'));
  document.querySelectorAll('.arch-tab-btn').forEach(btn => {
    btn.classList.remove('bg-white/15', 'text-white', 'border-white/20');
    btn.classList.add('bg-black/40', 'text-slate-400', 'border-white/10');
  });

  const activePanel = document.getElementById(tabId);
  if (activePanel) {
    activePanel.classList.remove('hidden');
  }

  el.classList.remove('bg-black/40', 'text-slate-400', 'border-white/10');
  el.classList.add('bg-white/15', 'text-white', 'border-white/20');
}

// ----------------------------------------------------
// Blog Interactive Category Filtering & Actions
// ----------------------------------------------------
function filterBlogCategory(catKey, el) {
  const allCards = document.querySelectorAll('.blog-card');
  document.querySelectorAll('.blog-cat-btn').forEach(btn => {
    btn.classList.remove('bg-black', 'text-white', 'border-black');
    btn.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
  });

  el.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
  el.classList.add('bg-black', 'text-white', 'border-black');

  allCards.forEach(card => {
    if (catKey === 'all' || card.classList.contains(catKey)) {
      card.style.display = 'flex';
    } else {
      card.style.display = 'none';
    }
  });
}

function openBlogArticleModal(articleTitle) {
  openQuoteModal('Blog Inquiry: ' + articleTitle);
}

function handleNewsletterSubmit(e) {
  e.preventDefault();
  showToast('Subscribed to Bigmonks Engineering Insights!');
  e.target.reset();
}

// ----------------------------------------------------
// Dynamic Blog Date (Always 2 Days Before Current Date)
// ----------------------------------------------------
function initBlogUpdatedDate() {
  const el = document.getElementById('blog-updated-date');
  if (!el) return;
  const d = new Date();
  d.setDate(d.getDate() - 2);
  const month = d.toLocaleString('en-US', { month: 'short' }).toUpperCase();
  const day = d.getDate();
  const year = d.getFullYear();
  el.innerHTML = `UPDATED ${month} ${day}, ${year} &bull; <strong class="font-extrabold text-black">BIGMONKS TECH LABS</strong>`;
}

document.addEventListener('DOMContentLoaded', () => {
  initBlogUpdatedDate();
});

function handleContactPageSubmit(e) {
  e.preventDefault();
  const firstName = document.getElementById('contact-first-name')?.value || '';
  const lastName = document.getElementById('contact-last-name')?.value || '';
  const contactInfo = document.getElementById('contact-contact-info')?.value || '';
  const message = document.getElementById('contact-message')?.value || '';

  const subject = encodeURIComponent(`Website Inquiry from ${firstName} ${lastName}`.trim());
  const bodyText = `Name: ${firstName} ${lastName}\nContact Info: ${contactInfo}\n\nMessage:\n${message}`;
  const body = encodeURIComponent(bodyText);

  const mailtoUrl = `mailto:info@bigmonks.com?subject=${subject}&body=${body}`;

  // Direct synchronous window navigation + link click fallback for maximum OS compatibility
  try {
    window.location.href = mailtoUrl;
  } catch (err) {
    const link = document.createElement('a');
    link.href = mailtoUrl;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  showToast('Thank you! Opening your email app...');
  e.target.reset();
}

function handleCareersPageSubmit(e) {
  e.preventDefault();
  const firstName = document.getElementById('career-first-name')?.value || '';
  const lastName = document.getElementById('career-last-name')?.value || '';
  const email = document.getElementById('career-email')?.value || '';
  const position = document.getElementById('career-position')?.value || '';
  const portfolio = document.getElementById('career-portfolio')?.value || '';
  const note = document.getElementById('career-note')?.value || '';

  const subject = encodeURIComponent(`Job Application: ${position} - ${firstName} ${lastName}`.trim());
  const bodyText = `Applicant Name: ${firstName} ${lastName}\nEmail: ${email}\nPosition: ${position}\nPortfolio/LinkedIn: ${portfolio}\n\nBackground & Experience:\n${note}`;
  const body = encodeURIComponent(bodyText);

  const mailtoUrl = `mailto:info@bigmonks.com?subject=${subject}&body=${body}`;

  try {
    window.location.href = mailtoUrl;
  } catch (err) {
    const link = document.createElement('a');
    link.href = mailtoUrl;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  showToast('Opening email app with your application details...');
  e.target.reset();
}



