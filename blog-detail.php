<?php
require_once __DIR__ . '/data/db.php';

$identifier = $_GET['slug'] ?? ($_GET['id'] ?? '');
$post = get_post_by_id_or_slug($identifier);

if (!$post) {
    // Post not found
    header("HTTP/1.0 404 Not Found");
    echo "<h1>Article Not Found</h1><p>The requested blog article does not exist. <a href='blogs.php'>Return to Blogs</a></p>";
    exit;
}

// Increment view count
$post['views'] = ($post['views'] ?? 0) + 1;
save_post($post);

// Fetch related articles (same category or recent, excluding current)
$all_posts = get_all_posts(true);
$related = array_values(array_filter($all_posts, function($p) use ($post) {
    return ($p['id'] ?? '') !== ($post['id'] ?? '') && ($p['category_code'] ?? '') === ($post['category_code'] ?? '');
}));

if (count($related) < 3) {
    $others = array_filter($all_posts, function($p) use ($post) {
        return ($p['id'] ?? '') !== ($post['id'] ?? '');
    });
    $related = array_slice($others, 0, 3);
} else {
    $related = array_slice($related, 0, 3);
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($post['title']) ?> | Bigmonks Engineering</title>
  <meta name="description" content="<?= htmlspecialchars($post['excerpt'] ?? '') ?>" />

  <!-- Open Graph -->
  <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($post['excerpt'] ?? '') ?>" />
  <meta property="og:image" content="<?= htmlspecialchars($post['image'] ?? 'images/blog_hero_img.png') ?>" />

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="images/bigmonks-logo.png" />

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

  <!-- Google Font: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />

  <!-- Custom Styles -->
  <link rel="stylesheet" href="styles.css" />

  <style>
    .article-body h2 { font-size: 1.75rem; font-weight: 800; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem; line-height: 1.3; }
    .article-body h3 { font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-top: 1.5rem; margin-bottom: 0.75rem; }
    .article-body p { font-size: 1rem; line-height: 1.8; color: #334155; margin-bottom: 1.25rem; }
    .article-body ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1.25rem; color: #334155; }
    .article-body ol { list-style-type: decimal; padding-left: 1.5rem; margin-bottom: 1.25rem; color: #334155; }
    .article-body li { margin-bottom: 0.5rem; line-height: 1.6; }
    .article-body blockquote { border-left: 4px solid #0f172a; padding-left: 1.25rem; font-style: italic; color: #475569; margin: 1.5rem 0; font-size: 1.1rem; }
    .article-body code { background: #f1f5f9; padding: 0.2rem 0.4rem; rounded: 0.375rem; font-family: monospace; font-size: 0.9em; color: #0f172a; }
  </style>
</head>
<body class="text-slate-900 bg-white font-sans antialiased selection:bg-slate-900 selection:text-white">

  <!-- Floating Pill Navbar Header -->
  <nav id="main-nav" class="fixed top-5 left-1/2 -translate-x-1/2 z-50 transition-all duration-300 w-max max-w-[95vw]">
    <div class="bg-white/95 backdrop-blur-md border border-slate-200/80 rounded-full hidden md:flex flex-nowrap items-center gap-5 md:gap-6 lg:gap-8 pl-5 md:pl-6 pr-2 md:pr-2.5 py-1.5 shadow-xl shadow-slate-900/5 text-sm">
      <a href="./" class="flex items-center gap-2 group shrink-0 mr-4">
        <img src="images/bigmonks wordmark sq (1) 2.png" alt="Bigmonks Logo" class="h-7 w-auto object-contain" />
      </a>
      <div class="flex items-center gap-6 text-slate-900 shrink-0">
        <a href="who-we-are" class="nav-link whitespace-nowrap">Who We Are</a>
        <a href="what-we-do" class="nav-link whitespace-nowrap">What We Do</a>
        <a href="blogs.php" class="nav-link nav-active whitespace-nowrap">Blogs</a>
      </div>
      <a href="contact" class="bg-black hover:bg-slate-800 text-white font-medium text-sm rounded-full px-5 py-2.5 flex items-center gap-2 transition-all shadow-sm">
        <span>Get in touch</span>
      </a>
    </div>
  </nav>

  <!-- ARTICLE HERO HEADER -->
  <header class="pt-32 sm:pt-40 pb-12 px-6 max-w-4xl mx-auto space-y-6">
    <a href="blogs.php" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-black uppercase tracking-wider transition-colors">
      &larr; Back to Research Articles
    </a>

    <div class="flex flex-wrap items-center gap-3">
      <span class="pill-badge bg-slate-100 text-slate-900 text-xs font-bold uppercase tracking-wider px-3.5 py-1.5 rounded-full border border-slate-200">
        <?= htmlspecialchars($post['category'] ?? 'Engineering') ?>
      </span>
      <span class="text-xs font-mono text-slate-400">
        Published <?= htmlspecialchars(date('M d, Y', strtotime($post['date'] ?? 'now'))) ?>
      </span>
      <span class="text-xs font-mono text-slate-400">&bull; <?= htmlspecialchars($post['read_time'] ?? '5 Min Read') ?></span>
    </div>

    <h1 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight leading-tight">
      <?= htmlspecialchars($post['title']) ?>
    </h1>

    <div class="flex items-center justify-between pt-4 border-t border-slate-100 text-xs">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm">
          <?= strtoupper(substr($post['author'] ?? 'B', 0, 1)) ?>
        </div>
        <div>
          <strong class="text-slate-900 block font-bold text-sm"><?= htmlspecialchars($post['author'] ?? 'Bigmonks Engineering') ?></strong>
          <span class="text-slate-500 text-xs"><?= htmlspecialchars($post['author_role'] ?? 'Tech Lead') ?></span>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button onclick="navigator.clipboard.writeText(window.location.href); alert('Article URL copied to clipboard!');" class="p-2 rounded-full border border-slate-200 hover:bg-slate-50 text-slate-600 transition-colors" title="Copy Link">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        </button>
      </div>
    </div>
  </header>

  <!-- FEATURED IMAGE -->
  <div class="max-w-5xl mx-auto px-6 mb-12">
    <div class="rounded-3xl overflow-hidden shadow-2xl border border-slate-200/80 aspect-video bg-slate-900">
      <img src="<?= htmlspecialchars($post['image'] ?? 'images/blog_hero_img.png') ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full h-full object-cover" />
    </div>
  </div>

  <!-- ARTICLE BODY -->
  <main class="max-w-3xl mx-auto px-6 pb-20">
    <div class="article-body">
      <?= $post['content'] ?>
    </div>
  </main>

  <!-- RELATED ARTICLES -->
  <?php if (!empty($related)): ?>
    <section class="py-16 px-6 bg-slate-50 border-t border-slate-200">
      <div class="max-w-7xl mx-auto space-y-8">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Related Research Articles</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <?php foreach ($related as $rel): ?>
            <a href="blog-detail.php?slug=<?= urlencode($rel['slug']) ?>" class="bg-white rounded-3xl border border-slate-200/80 p-6 flex flex-col justify-between shadow-sm hover:shadow-lg transition-all group block">
              <div>
                <div class="overflow-hidden rounded-2xl mb-4 aspect-video bg-slate-900">
                  <img src="<?= htmlspecialchars($rel['image']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                </div>
                <div class="pill-badge bg-slate-100 text-slate-800 text-[10px] font-bold mb-2 uppercase inline-block">
                  <?= htmlspecialchars($rel['category'] ?? 'Engineering') ?>
                </div>
                <h3 class="text-lg font-bold text-slate-900 group-hover:text-slate-600 transition-colors line-clamp-2">
                  <?= htmlspecialchars($rel['title']) ?>
                </h3>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- FOOTER -->
  <footer class="py-12 border-t border-slate-200 text-center text-xs text-slate-500">
    &copy; <?= date('Y') ?> Bigmonks Technologies. All rights reserved.
  </footer>

</body>
</html>
