<?php
session_start();
require_once __DIR__ . '/data/db.php';

$message = '';
$error = '';

// Handle Login
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $password = $_POST['password'] ?? '';
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Invalid password. Default is "admin".';
    }
}

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Ensure user is logged in for all CMS actions
$logged_in = !empty($_SESSION['admin_logged_in']);

// Handle Post Actions (Save / Delete / Status toggle)
if ($logged_in && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_post') {
        $file = $_FILES['image_file'] ?? null;
        $saved = save_post($_POST, $file);
        if ($saved) {
            $_SESSION['flash_msg'] = 'Blog post saved successfully!';
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Failed to save blog post.';
        }
    }
    
    if ($action === 'delete_post') {
        $id = $_POST['post_id'] ?? '';
        if ($id && delete_post($id)) {
            $_SESSION['flash_msg'] = 'Post deleted successfully.';
            header('Location: admin.php');
            exit;
        }
    }

    if ($action === 'toggle_status') {
        $id = $_POST['post_id'] ?? '';
        $post = get_post_by_id_or_slug($id);
        if ($post) {
            $post['status'] = ($post['status'] === 'published') ? 'draft' : 'published';
            save_post($post);
            $_SESSION['flash_msg'] = 'Status updated to ' . ucfirst($post['status']);
            header('Location: admin.php');
            exit;
        }
    }
}

// Fetch flash message
if (!empty($_SESSION['flash_msg'])) {
    $message = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

// Routing & View Selection
$action = $_GET['action'] ?? 'list';
$edit_post = null;
if ($action === 'edit' && !empty($_GET['id'])) {
    $edit_post = get_post_by_id_or_slug($_GET['id']);
}
$all_posts = get_all_posts(false);

// Metrics
$total_posts = count($all_posts);
$published_count = count(array_filter($all_posts, fn($p) => ($p['status'] ?? 'published') === 'published'));
$draft_count = $total_posts - $published_count;
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100 selection:bg-white selection:text-black">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bigmonks Blog CMS Admin</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="images/bigmonks-logo.png" />
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com?plugins=forms"></script>

  <!-- Google Font: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <style>
    body { font-family: 'Inter', sans-serif; }
    .glass-card {
      background: rgba(15, 23, 42, 0.75);
      backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
  </style>
</head>
<body class="min-h-full flex flex-col bg-slate-950 text-slate-100">

<?php if (!$logged_in): ?>
  <!-- LOGIN SCREEN -->
  <div class="flex-1 flex items-center justify-center p-6">
    <div class="w-full max-w-md glass-card rounded-3xl p-8 shadow-2xl space-y-6">
      <div class="text-center space-y-2">
        <div class="inline-flex p-3 rounded-2xl bg-white/10 mb-2">
          <img src="images/bigmonks wordmark sq (1) 2.png" alt="Bigmonks Logo" class="h-8 w-auto invert" />
        </div>
        <h1 class="text-2xl font-bold tracking-tight">Blog CMS Portal</h1>
        <p class="text-xs text-slate-400">Enter administrator credentials to manage website blog content</p>
      </div>

      <?php if ($error): ?>
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-xs flex items-center gap-2">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="login" />
        <div>
          <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">CMS Password</label>
          <input type="password" name="password" required placeholder="Enter password (default: admin)" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-white transition-colors" />
        </div>
        <button type="submit" class="w-full bg-white text-black font-bold py-3.5 rounded-xl text-sm hover:bg-slate-200 transition-colors shadow-lg shadow-white/5 cursor-pointer">
          Authenticate &amp; Access Admin
        </button>
      </form>
      <div class="text-center text-[11px] text-slate-500">
        Default password is <code class="bg-slate-900 px-1.5 py-0.5 rounded text-slate-300 font-mono">admin</code>
      </div>
    </div>
  </div>

<?php else: ?>

  <!-- LOGGED IN ADMIN DASHBOARD -->
  <!-- Top Navigation Header -->
  <header class="border-b border-white/10 bg-slate-900/80 backdrop-blur-md sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <a href="admin.php" class="flex items-center gap-2">
          <img src="images/bigmonks wordmark sq (1) 2.png" alt="Bigmonks" class="h-6 w-auto invert" />
          <span class="bg-white/10 text-white text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full border border-white/10">CMS Admin</span>
        </a>
      </div>

      <div class="flex items-center gap-3">
        <a href="blogs.php" target="_blank" class="text-xs font-semibold bg-white/5 hover:bg-white/10 px-4 py-2 rounded-full border border-white/10 transition-colors flex items-center gap-1.5">
          <span>View Live Blog</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
        <a href="admin.php?action=logout" class="text-xs font-semibold bg-red-500/10 hover:bg-red-500/20 text-red-400 px-4 py-2 rounded-full border border-red-500/20 transition-colors">
          Logout
        </a>
      </div>
    </div>
  </header>

  <!-- Flash Message Notification -->
  <?php if ($message): ?>
    <div class="bg-emerald-500/10 border-b border-emerald-500/20 text-emerald-400 px-6 py-3 text-xs font-semibold text-center flex items-center justify-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      <span><?= htmlspecialchars($message) ?></span>
    </div>
  <?php endif; ?>

  <main class="max-w-7xl mx-auto px-6 py-8 flex-1 w-full space-y-8">

    <?php if ($action === 'create' || $action === 'edit'): ?>
      <!-- CREATE / EDIT BLOG POST FORM -->
      <div class="space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold tracking-tight">
              <?= $edit_post ? 'Edit Blog Article' : 'Create New Blog Article' ?>
            </h2>
            <p class="text-xs text-slate-400">Fill in the details below to publish or draft a research article</p>
          </div>
          <a href="admin.php" class="text-xs font-semibold bg-white/10 hover:bg-white/20 px-4 py-2 rounded-full border border-white/10 transition-colors">
            &larr; Back to Dashboard
          </a>
        </div>

        <form method="POST" enctype="multipart/form-data" class="glass-card rounded-3xl p-8 space-y-6">
          <input type="hidden" name="action" value="save_post" />
          <?php if ($edit_post): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($edit_post['id']) ?>" />
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($edit_post['image']) ?>" />
            <input type="hidden" name="views" value="<?= (int)($edit_post['views'] ?? 0) ?>" />
          <?php endif; ?>

          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Title, Excerpt, Content -->
            <div class="lg:col-span-8 space-y-6">
              <!-- Title -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Article Title *</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($edit_post['title'] ?? '') ?>" placeholder="e.g., Building High-Concurrency Microservices with GraphQL" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-white transition-colors" />
              </div>

              <!-- Slug -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Custom URL Permalink / Slug (Optional)</label>
                <input type="text" name="slug" value="<?= htmlspecialchars($edit_post['slug'] ?? '') ?>" placeholder="building-high-concurrency-microservices" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs font-mono text-slate-300 focus:outline-none focus:border-white transition-colors" />
                <span class="text-[11px] text-slate-500 mt-1 block">Leave empty to generate automatically from title</span>
              </div>

              <!-- Excerpt -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Short Summary / Excerpt *</label>
                <textarea name="excerpt" rows="3" required placeholder="Brief 1-2 sentence overview shown in blog cards..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs leading-relaxed focus:outline-none focus:border-white transition-colors"><?= htmlspecialchars($edit_post['excerpt'] ?? '') ?></textarea>
              </div>

              <!-- Content Editor -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Full Article Body Content (HTML supported) *</label>
                  <div class="flex items-center gap-1 bg-slate-900 border border-slate-700 rounded-lg p-1 text-[11px]">
                    <button type="button" onclick="insertTag('<b>', '</b>')" class="px-2 py-0.5 hover:bg-white/10 rounded font-bold" title="Bold">B</button>
                    <button type="button" onclick="insertTag('<i>', '</i>')" class="px-2 py-0.5 hover:bg-white/10 rounded italic" title="Italic">I</button>
                    <button type="button" onclick="insertTag('<h3>', '</h3>')" class="px-2 py-0.5 hover:bg-white/10 rounded font-semibold" title="Heading 3">H3</button>
                    <button type="button" onclick="insertTag('<p>', '</p>')" class="px-2 py-0.5 hover:bg-white/10 rounded" title="Paragraph">P</button>
                    <button type="button" onclick="insertTag('<ul>\n  <li>', '</li>\n</ul>')" class="px-2 py-0.5 hover:bg-white/10 rounded" title="Unordered List">List</button>
                    <button type="button" onclick="insertTag('<blockquote class=\'border-l-4 border-slate-500 pl-4 my-4 italic text-slate-300\'>', '</blockquote>')" class="px-2 py-0.5 hover:bg-white/10 rounded" title="Quote">Quote</button>
                  </div>
                </div>
                <textarea id="article-content" name="content" rows="14" required placeholder="<p>Write your detailed technical article here...</p>" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-4 text-xs font-mono leading-relaxed focus:outline-none focus:border-white transition-colors"><?= htmlspecialchars($edit_post['content'] ?? '') ?></textarea>
              </div>
            </div>

            <!-- Right Column: Meta, Featured Image Upload, Status -->
            <div class="lg:col-span-4 space-y-6">
              <!-- Category Picker -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Category *</label>
                <select name="category_code" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-slate-100 focus:outline-none focus:border-white transition-colors">
                  <?php 
                  $cats = [
                    'cloud' => 'Cloud & DevOps',
                    'mobile' => 'Mobile Platforms',
                    'retail' => 'Omnichannel Retail',
                    'ai' => 'AI & Machine Learning',
                    'web' => 'Web Platforms',
                    'general' => 'General Engineering'
                  ];
                  $curr_cat = $edit_post['category_code'] ?? 'cloud';
                  foreach ($cats as $code => $name): ?>
                    <option value="<?= $code ?>" <?= ($curr_cat === $code) ? 'selected' : '' ?>><?= $name ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Author Info -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Author Name</label>
                  <input type="text" name="author" value="<?= htmlspecialchars($edit_post['author'] ?? 'Bigmonks Engineering') ?>" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-white" />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Author Role</label>
                  <input type="text" name="author_role" value="<?= htmlspecialchars($edit_post['author_role'] ?? 'Tech Lead') ?>" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-white" />
                </div>
              </div>

              <!-- Date & Read Time -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Publication Date</label>
                  <input type="date" name="date" value="<?= htmlspecialchars($edit_post['date'] ?? date('Y-m-d')) ?>" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-white" />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Estimated Read Time</label>
                  <input type="text" name="read_time" value="<?= htmlspecialchars($edit_post['read_time'] ?? '5 Min Read') ?>" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-white" />
                </div>
              </div>

              <!-- Featured Image Upload & Preview -->
              <div class="space-y-3 bg-slate-900/60 border border-slate-800 rounded-2xl p-4">
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Featured Image</label>
                
                <!-- Live Preview -->
                <div class="aspect-video w-full rounded-xl overflow-hidden bg-slate-950 border border-slate-800 flex items-center justify-center relative">
                  <img id="image-preview" src="<?= htmlspecialchars($edit_post['image'] ?? 'images/blog_hero_img.png') ?>" alt="Preview" class="w-full h-full object-cover" />
                </div>

                <div>
                  <label class="block text-[11px] text-slate-400 mb-1">Upload New Image File (JPG, PNG, WebP, SVG)</label>
                  <input type="file" name="image_file" accept="image/*" onchange="previewFile(this)" class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-white/10 file:text-white hover:file:bg-white/20 cursor-pointer" />
                </div>

                <div class="pt-2 border-t border-slate-800">
                  <label class="block text-[11px] text-slate-400 mb-1">Or Paste Image URL Path</label>
                  <input type="text" name="image_url" placeholder="images/blog_api_architecture.png" onchange="document.getElementById('image-preview').src=this.value" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs font-mono" />
                </div>
              </div>

              <!-- Status Toggle -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Publishing Status</label>
                <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xs text-slate-100 focus:outline-none focus:border-white transition-colors">
                  <option value="published" <?= (($edit_post['status'] ?? 'published') === 'published') ? 'selected' : '' ?>>🚀 Published (Visible on website)</option>
                  <option value="draft" <?= (($edit_post['status'] ?? '') === 'draft') ? 'selected' : '' ?>>📝 Draft (Hidden from website)</option>
                </select>
              </div>

              <!-- Submit Buttons -->
              <div class="pt-4 flex flex-col gap-2">
                <button type="submit" class="w-full bg-white text-black font-bold py-3.5 rounded-xl text-xs uppercase tracking-wider hover:bg-slate-200 transition-colors shadow-xl cursor-pointer">
                  <?= $edit_post ? 'Update &amp; Save Post' : 'Publish Blog Article' ?>
                </button>
                <a href="admin.php" class="w-full text-center bg-white/5 hover:bg-white/10 text-slate-400 font-semibold py-2.5 rounded-xl text-xs border border-white/5 transition-colors">
                  Cancel
                </a>
              </div>

            </div>
          </div>
        </form>
      </div>

      <script>
        function previewFile(input) {
          if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
              document.getElementById('image-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
          }
        }
        function insertTag(start, end) {
          var area = document.getElementById('article-content');
          var startPos = area.selectionStart;
          var endPos = area.selectionEnd;
          var text = area.value;
          var selectedText = text.substring(startPos, endPos) || 'Your text here';
          area.value = text.substring(0, startPos) + start + selectedText + end + text.substring(endPos);
          area.focus();
        }
      </script>

    <?php else: ?>

      <!-- DASHBOARD LIST VIEW -->
      <div class="space-y-6">
        <!-- Top Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div class="glass-card rounded-2xl p-6 flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Articles</p>
              <h3 class="text-3xl font-extrabold mt-1"><?= $total_posts ?></h3>
            </div>
            <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
          </div>

          <div class="glass-card rounded-2xl p-6 flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Published</p>
              <h3 class="text-3xl font-extrabold text-emerald-400 mt-1"><?= $published_count ?></h3>
            </div>
            <div class="w-12 h-12 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-emerald-400">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>

          <div class="glass-card rounded-2xl p-6 flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Drafts</p>
              <h3 class="text-3xl font-extrabold text-amber-400 mt-1"><?= $draft_count ?></h3>
            </div>
            <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-400">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
          </div>
        </div>

        <!-- Action Header & Search -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-4">
          <div>
            <h2 class="text-xl font-bold tracking-tight">Blog Articles</h2>
            <p class="text-xs text-slate-400">Manage, edit, publish, or remove blog posts</p>
          </div>

          <a href="admin.php?action=create" class="bg-white text-black font-bold px-6 py-3 rounded-full text-xs hover:bg-slate-200 transition-all shadow-lg flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span>Create New Article</span>
          </a>
        </div>

        <!-- Posts Table -->
        <div class="glass-card rounded-3xl overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-900/90 border-b border-white/10 uppercase text-[10px] tracking-wider text-slate-400">
                <tr>
                  <th class="py-4 px-6">Article</th>
                  <th class="py-4 px-4">Category</th>
                  <th class="py-4 px-4">Date</th>
                  <th class="py-4 px-4">Status</th>
                  <th class="py-4 px-6 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/5">
                <?php if (empty($all_posts)): ?>
                  <tr>
                    <td colspan="5" class="py-12 text-center text-slate-500 font-medium">
                      No blog articles found. Click "Create New Article" to add one.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($all_posts as $post): ?>
                    <tr class="hover:bg-white/[0.02] transition-colors">
                      <!-- Article Thumbnail & Title -->
                      <td class="py-4 px-6">
                        <div class="flex items-center gap-4">
                          <div class="w-14 h-10 rounded-lg overflow-hidden bg-slate-900 shrink-0 border border-white/10">
                            <img src="<?= htmlspecialchars($post['image'] ?? 'images/blog_hero_img.png') ?>" alt="" class="w-full h-full object-cover" />
                          </div>
                          <div>
                            <a href="blog-detail.php?slug=<?= urlencode($post['slug']) ?>" target="_blank" class="font-bold text-slate-100 hover:text-white transition-colors line-clamp-1">
                              <?= htmlspecialchars($post['title']) ?>
                            </a>
                            <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                              <?= htmlspecialchars($post['excerpt'] ?? '') ?>
                            </p>
                          </div>
                        </div>
                      </td>

                      <!-- Category -->
                      <td class="py-4 px-4 whitespace-nowrap">
                        <span class="bg-slate-800 text-slate-300 px-2.5 py-1 rounded-full text-[10px] font-semibold border border-white/5">
                          <?= htmlspecialchars($post['category'] ?? 'Engineering') ?>
                        </span>
                      </td>

                      <!-- Date -->
                      <td class="py-4 px-4 whitespace-nowrap text-slate-400 font-mono text-[11px]">
                        <?= htmlspecialchars($post['date'] ?? '') ?>
                      </td>

                      <!-- Status Badge -->
                      <td class="py-4 px-4 whitespace-nowrap">
                        <form method="POST" class="inline">
                          <input type="hidden" name="action" value="toggle_status" />
                          <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['id']) ?>" />
                          <button type="submit" title="Click to toggle status" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold cursor-pointer transition-all <?= ($post['status'] === 'published') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30 hover:bg-amber-500/20' ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?= ($post['status'] === 'published') ? 'bg-emerald-400' : 'bg-amber-400' ?>"></span>
                            <span><?= ucfirst($post['status'] ?? 'published') ?></span>
                          </button>
                        </form>
                      </td>

                      <!-- Actions -->
                      <td class="py-4 px-6 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-2">
                          <a href="blog-detail.php?slug=<?= urlencode($post['slug']) ?>" target="_blank" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg text-slate-300 hover:text-white transition-colors" title="Preview Article">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                          </a>
                          <a href="admin.php?action=edit&id=<?= urlencode($post['id']) ?>" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg text-slate-300 hover:text-white transition-colors" title="Edit Article">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                          </a>
                          <form method="POST" onsubmit="return confirm('Are you sure you want to delete this article?');" class="inline">
                            <input type="hidden" name="action" value="delete_post" />
                            <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['id']) ?>" />
                            <button type="submit" class="p-2 bg-red-500/10 hover:bg-red-500/20 rounded-lg text-red-400 transition-colors cursor-pointer" title="Delete Article">
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    <?php endif; ?>

  </main>

  <footer class="border-t border-white/10 py-6 text-center text-xs text-slate-500">
    &copy; <?= date('Y') ?> Bigmonks Technologies Blog CMS. All rights reserved.
  </footer>

<?php endif; ?>

</body>
</html>
