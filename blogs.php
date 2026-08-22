<?php
require_once __DIR__ . '/data/db.php';

// Fetch published posts
$posts = get_all_posts(true);
$total_published = count($posts);

// Category filter from query param
$selected_cat = $_GET['cat'] ?? 'all';
if ($selected_cat !== 'all') {
    $posts = array_values(array_filter($posts, function($p) use ($selected_cat) {
        return ($p['category_code'] ?? 'general') === $selected_cat;
    }));
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Primary Meta Tags -->
  <title>Engineering Insights &amp; Articles | Bigmonks Tech Labs</title>
  <meta name="title" content="Engineering Insights &amp; Articles | Bigmonks Tech Labs" />
  <meta name="description"
    content="Deep dives into enterprise web systems, high-concurrency microservices, GraphQL, Next.js 15, AI vector pipelines, and omnichannel retail tech from Bigmonks engineers." />
  <meta name="keywords"
    content="Bigmonks Blogs, Engineering Insights, GraphQL Redis Microservices, SwiftUI Retail POS, AWS Lambda Serverless, LLM Vector Search, NextJS 15 Performance" />
  <meta name="robots" content="index, follow, max-image-preview:large" />
  <link rel="canonical" href="https://www.bigmonks.com/blogs.php" />

  <!-- Favicon Links -->
  <link rel="icon" type="image/x-icon" href="images/favicon.ico" />
  <link rel="icon" type="image/png" sizes="32x32" href="images/bigmonks-logo.png" />
  <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png" />

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

  <!-- Google Font: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />

  <!-- Custom Styles -->
  <link rel="stylesheet" href="styles.css" />

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              dark: '#0c0c0c',
              gray: '#f3f4f6'
            }
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          }
        }
      }
    }
  </script>
</head>

<body class="text-slate-900 bg-white font-sans antialiased selection:bg-slate-900 selection:text-white">

  <!-- Toast Notification Container -->
  <div id="toast-container" class="fixed top-6 right-6 z-[100] flex flex-col gap-3 pointer-events-none"></div>

  <!-- BEGIN: Centered Floating Replica Pill Navbar Header -->
  <nav id="main-nav"
    class="fixed top-5 left-1/2 -translate-x-1/2 z-50 transition-all duration-300 w-max max-w-[95vw]">

    <!-- DESKTOP Replica Navbar Pill (md and up) -->
    <div
      class="bg-white/95 backdrop-blur-md border border-slate-200/80 rounded-full hidden md:flex flex-nowrap items-center gap-5 md:gap-6 lg:gap-8 pl-5 md:pl-6 pr-2 md:pr-2.5 py-1.5 shadow-xl shadow-slate-900/5 text-sm transition-all duration-300">
      
      <!-- Brand Logo Left -->
      <a href="./" class="flex items-center gap-2 group shrink-0 mr-2 md:mr-4 lg:mr-6" aria-label="Bigmonks Home">
        <img src="images/bigmonks wordmark sq (1) 2.png" alt="Bigmonks Technologies Logo"
          class="h-7 md:h-8 w-auto object-contain transition-transform duration-300 group-hover:scale-105" />
      </a>

      <!-- Center Navigation Links -->
      <div class="flex items-center gap-5 md:gap-6 lg:gap-8 text-slate-900 shrink-0">
        <a href="who-we-are" class="nav-link whitespace-nowrap shrink-0">Who We Are</a>
        <a href="what-we-do" class="nav-link whitespace-nowrap shrink-0">What We Do</a>
        <a href="blogs.php" class="nav-link nav-active whitespace-nowrap shrink-0">Blogs</a>
        <a href="admin.php" target="_blank" class="nav-link whitespace-nowrap shrink-0 text-xs font-bold text-slate-400 hover:text-black">CMS Admin</a>
      </div>

      <!-- Right CTA Button -->
      <a href="contact"
        class="bg-black hover:bg-black text-white font-medium text-sm rounded-full px-5 py-2.5 md:py-3 flex items-center gap-2.5 transition-all shadow-sm hover:shadow-md cursor-pointer group shrink-0 ml-4 md:ml-6 lg:ml-8">
        <svg class="w-4 h-4 text-white transform group-hover:rotate-45 transition-all"
          fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M7 17L17 7M17 7H7M17 7V17" />
        </svg>
        <span class="whitespace-nowrap">Get in touch</span>
      </a>
    </div>

    <!-- MOBILE Replica Navbar Pill (below md) -->
    <div class="md:hidden">
      <!-- Closed state bar -->
      <div class="bg-white/95 backdrop-blur-md border border-slate-200/80 rounded-full flex items-center justify-between px-5 py-1.5 shadow-xl shadow-slate-900/5">
        <a href="./" class="flex items-center gap-2" aria-label="Bigmonks Home">
          <img src="images/bigmonks wordmark sq (1) 2.png" alt="Bigmonks Logo"
            class="h-6 w-auto object-contain" />
        </a>

        <div class="flex items-center gap-3">
          <button id="mobile-nav-btn" onclick="toggleMobileNav()" aria-label="Toggle menu"
            class="w-8 h-8 flex flex-col justify-center items-center gap-[5px] focus:outline-none">
            <span class="nav-burger-bar"></span>
            <span class="nav-burger-bar"></span>
            <span class="nav-burger-bar"></span>
          </button>
        </div>
      </div>

      <!-- Mobile Dropdown Menu -->
      <div id="mobile-nav-menu"
        class="bg-white/95 backdrop-blur-md border border-slate-200/80 mt-2 rounded-2xl overflow-hidden max-h-0 opacity-0 transition-all duration-300 ease-in-out shadow-2xl">
        <div class="flex flex-col py-2">
          <a href="who-we-are" onclick="closeMobileNav()" class="nav-link mobile-nav-item">Who We Are</a>
          <a href="what-we-do" onclick="closeMobileNav()" class="nav-link mobile-nav-item">What We Do</a>
          <a href="blogs.php" onclick="closeMobileNav()" class="nav-link mobile-nav-item nav-active">Blogs</a>
          <a href="admin.php" target="_blank" onclick="closeMobileNav()" class="nav-link mobile-nav-item text-slate-500">CMS Admin Portal</a>
          <div class="px-5 py-3 border-t border-slate-100">
            <a href="contact"
              class="w-full bg-black text-white font-medium text-sm rounded-full py-2.5 flex items-center justify-center gap-2 shadow-sm">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path d="M7 17L17 7M17 7H7M17 7V17" />
              </svg>
              <span>Get in touch</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- BEGIN: Bespoke Blogs Hero Section -->
  <section
    class="relative min-h-[50vh] sm:min-h-[75vh] flex flex-col justify-between pt-20 sm:pt-28 pb-4 sm:pb-8 px-4 sm:px-12 overflow-hidden"
    data-purpose="hero">
    <!-- Hero Background Image Container -->
    <div class="absolute inset-0 z-0 bg-white overflow-hidden flex items-center justify-center">
      <img alt="Bigmonks Engineering Blogs &amp; Insights"
        class="w-full h-full object-contain sm:object-cover object-center scale-100 sm:scale-85 transform transition-all duration-500"
        src="images/blog_hero_img.png" onerror="this.onerror=null; this.src='images/hero_widescreen.png';" />
    </div>

    <!-- Hero Content Container -->
    <div class="relative z-10 max-w-7xl mx-auto w-full flex-1 flex flex-col justify-between pb-0">

      <!-- Top Row -->
      <div class="flex justify-between items-center gap-3 pt-2 sm:pt-6">
        <div class="flex items-center gap-2 bg-white/90 backdrop-blur-md border border-slate-900/10 px-4 py-2 rounded-full shadow-sm text-xs font-semibold text-slate-900">
          <span>UPDATED <?= strtoupper(date('M d, Y')) ?> &bull; <strong class="font-extrabold text-black">BIGMONKS TECH LABS</strong></span>
        </div>

        <a href="admin.php" target="_blank" class="bg-black text-white hover:bg-slate-800 text-xs font-bold px-4 py-2 rounded-full shadow-md transition-colors flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span>Write / Manage Articles</span>
        </a>
      </div>

      <!-- Bottom Content Block -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-end pt-12 sm:pt-0">
        <div class="lg:col-span-9 space-y-4">
          <div class="pill-badge bg-white/80 backdrop-blur-sm text-slate-900 border-slate-300 uppercase tracking-widest font-bold text-xs inline-flex">
            INSIGHTS &amp; ARTICLES
          </div>

          <h1 class="text-slate-900 text-3xl sm:text-5xl md:text-6xl font-extrabold tracking-tight leading-tight uppercase">
            ENGINEERING INSIGHTS <br /> &amp; TECH THOUGHT LEADERSHIP
          </h1>

          <div class="flex items-center gap-2.5 pt-1">
            <span class="text-xs font-bold text-slate-900 uppercase tracking-widest">ARCHITECTURE &bull; AI AGENTS &bull; CLOUD &bull; RETAIL</span>
          </div>

          <p class="text-slate-900 text-xs sm:text-sm max-w-xl leading-relaxed font-normal tracking-wide">
            Deep dives into modern enterprise web systems, high-concurrency microservices, AI vector pipelines, and real-time omnichannel retail technologies.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- BEGIN: Interactive Category Filter Bar -->
  <section class="py-6 px-6 max-w-7xl mx-auto">
    <div class="flex flex-wrap justify-between items-center gap-4 border-b border-slate-200 pb-6">
      <div class="flex flex-wrap gap-2.5">
        <a href="blogs.php?cat=all" class="blog-cat-btn <?= ($selected_cat === 'all') ? 'bg-black text-white border-black' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-400' ?> px-5 py-2.5 rounded-full text-xs font-bold border transition-all">
          All Articles
        </a>
        <a href="blogs.php?cat=ai" class="blog-cat-btn <?= ($selected_cat === 'ai') ? 'bg-black text-white border-black' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-400' ?> px-5 py-2.5 rounded-full text-xs font-bold border transition-all">
          AI &amp; Machine Learning
        </a>
        <a href="blogs.php?cat=cloud" class="blog-cat-btn <?= ($selected_cat === 'cloud') ? 'bg-black text-white border-black' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-400' ?> px-5 py-2.5 rounded-full text-xs font-bold border transition-all">
          Cloud &amp; DevOps
        </a>
        <a href="blogs.php?cat=mobile" class="blog-cat-btn <?= ($selected_cat === 'mobile') ? 'bg-black text-white border-black' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-400' ?> px-5 py-2.5 rounded-full text-xs font-bold border transition-all">
          Mobile Platforms
        </a>
        <a href="blogs.php?cat=retail" class="blog-cat-btn <?= ($selected_cat === 'retail') ? 'bg-black text-white border-black' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-400' ?> px-5 py-2.5 rounded-full text-xs font-bold border transition-all">
          Omnichannel Retail
        </a>
        <a href="blogs.php?cat=web" class="blog-cat-btn <?= ($selected_cat === 'web') ? 'bg-black text-white border-black' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-400' ?> px-5 py-2.5 rounded-full text-xs font-bold border transition-all">
          Web Platforms
        </a>
      </div>

      <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">
        Showing <?= count($posts) ?> Published Articles
      </div>
    </div>
  </section>

  <!-- BEGIN: Blog Articles Grid Section -->
  <section class="py-12 px-6 max-w-7xl mx-auto">
    <?php if (empty($posts)): ?>
      <div class="text-center py-20 bg-slate-50 rounded-3xl border border-slate-200">
        <h3 class="text-xl font-bold text-slate-800">No published articles found in this category</h3>
        <p class="text-xs text-slate-500 mt-2">Log in to the CMS Admin panel to create or publish articles.</p>
        <a href="admin.php" class="inline-block mt-4 bg-black text-white text-xs font-bold px-6 py-3 rounded-full">Go to CMS Admin</a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($posts as $post): ?>
          <a href="blog-detail.php?slug=<?= urlencode($post['slug']) ?>" class="blog-card bg-white rounded-3xl border border-slate-200/80 p-6 flex flex-col justify-between shadow-sm hover:shadow-xl transition-all duration-300 group block">
            <div>
              <div class="overflow-hidden rounded-2xl mb-6 aspect-video border border-slate-100 bg-slate-900">
                <img src="<?= htmlspecialchars($post['image'] ?? 'images/blog_hero_img.png') ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
              </div>
              <div class="pill-badge bg-slate-100 text-slate-800 text-[10px] font-bold mb-3 border-slate-200 uppercase inline-block">
                <?= htmlspecialchars(strtoupper($post['category'] ?? 'ENGINEERING')) ?>
              </div>
              <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-slate-600 transition-colors leading-snug">
                <?= htmlspecialchars($post['title']) ?>
              </h3>
              <p class="text-slate-600 text-xs leading-relaxed mb-6 font-normal line-clamp-3">
                <?= htmlspecialchars($post['excerpt'] ?? '') ?>
              </p>
            </div>
            <div class="pt-4 border-t border-slate-100 flex justify-between items-center text-xs font-mono">
              <span class="font-bold text-slate-900"><?= htmlspecialchars(strtoupper(date('M d, Y', strtotime($post['date'] ?? 'now')))) ?></span>
              <span class="text-slate-400"><?= htmlspecialchars($post['read_time'] ?? '5 Min Read') ?></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- BEGIN: Footer -->
  <footer class="pt-24 px-6 bg-white border-t border-slate-100">
    <div class="max-w-7xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start mb-24">

        <div class="lg:col-span-3">
          <a href="contact" class="relative rounded-[2rem] overflow-hidden group shadow-lg block cursor-pointer">
            <img alt="Bigmonks Enterprise Software Engineering" class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-700" src="images/real_future_logistics.png" />
            <div class="absolute inset-0 bg-black/50 flex flex-col justify-center items-center text-white p-8 text-center">
              <span class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-slate-900 transform group-hover:rotate-45 transition-all shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
              </span>
            </div>
          </a>
        </div>

        <div class="lg:col-span-6 text-center lg:text-left">
          <h2 class="text-4xl sm:text-6xl font-black tracking-tighter leading-none mb-10 text-slate-900">
            Building the future of digital software.
          </h2>

          <div class="flex flex-wrap justify-center lg:justify-start gap-3 mb-12">
            <div class="pill-badge bg-slate-100 text-slate-700 font-bold text-xs border-slate-200">WEB &amp; MOBILE APPS</div>
            <div class="pill-badge bg-slate-100 text-slate-700 font-bold text-xs border-slate-200">CLOUD &amp; AI ARCHITECTURE</div>
            <div class="pill-badge bg-slate-100 text-slate-700 font-bold text-xs border-slate-200">ENTERPRISE SOFTWARE</div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-sm font-bold text-slate-600 text-center sm:text-left">
            <div>
              <p class="text-slate-900 font-extrabold uppercase text-xs tracking-wider mb-2">Locations</p>
              <div class="space-y-2 text-xs text-slate-700 leading-relaxed font-normal">
                <div><strong class="text-slate-900 font-bold block">India HQ:</strong> Kakkanad, Ernakulam, Kerala - 682037</div>
                <div><strong class="text-slate-900 font-bold block">Canada Hub:</strong> 576 Prince St Unit 1, Truro, NS B2N 1G3</div>
              </div>
            </div>

            <div>
              <p class="text-slate-900 font-extrabold uppercase text-xs tracking-wider mb-2">Contact</p>
              <div class="space-y-1 font-mono text-xs font-semibold">
                <a href="tel:+918848806406" class="text-slate-700 hover:text-black transition-colors block"><span class="text-slate-400 font-normal">IN:</span> +91 88488 06406</a>
                <a href="tel:+19027004454" class="text-slate-700 hover:text-black transition-colors block"><span class="text-slate-400 font-normal">CA:</span> +1 902-700-4454</a>
              </div>
            </div>

            <div>
              <p class="text-slate-900 font-extrabold uppercase text-xs tracking-wider mb-2">Connect</p>
              <a href="mailto:info@bigmonks.com" class="text-slate-900 hover:text-slate-600 block font-mono text-sm transition-colors font-bold">info@bigmonks.com</a>
              <a href="https://www.bigmonks.com" target="_blank" rel="noopener noreferrer" class="text-slate-500 hover:text-slate-600 block font-mono text-xs mt-1 transition-colors">www.bigmonks.com</a>
            </div>
          </div>
        </div>

        <div class="lg:col-span-3 flex flex-col gap-3">
          <a class="text-sm font-bold flex justify-between items-center border-b border-slate-100 py-3 text-slate-800 hover:text-slate-600" href="https://x.com/BigmonksTech" target="_blank">X (Twitter) <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg></a>
          <a class="text-sm font-bold flex justify-between items-center border-b border-slate-100 py-3 text-slate-800 hover:text-slate-600" href="https://www.instagram.com/bigmonks_technologies/" target="_blank">Instagram <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg></a>
          <a class="text-sm font-bold flex justify-between items-center border-b border-slate-100 py-3 text-slate-800 hover:text-slate-600" href="https://www.linkedin.com/company/bigmonks-technologies01" target="_blank">LinkedIn <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg></a>
        </div>
      </div>
    </div>
  </footer>

  <script src="app.js"></script>
</body>
</html>
