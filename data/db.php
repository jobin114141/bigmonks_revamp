<?php
// Central DB & Helper File for Bigmonks PHP CMS

define('DATA_FILE', __DIR__ . '/posts.json');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('ADMIN_PASSWORD', 'admin'); // Default password for admin panel

if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Get all posts from JSON storage
function get_all_posts($only_published = false) {
    if (!file_exists(DATA_FILE)) {
        return [];
    }
    $json = file_get_contents(DATA_FILE);
    $posts = json_decode($json, true) ?: [];
    
    // Sort posts by date descending
    usort($posts, function($a, $b) {
        return strtotime($b['date'] ?? 'now') - strtotime($a['date'] ?? 'now');
    });

    if ($only_published) {
        return array_values(array_filter($posts, function($p) {
            return ($p['status'] ?? 'published') === 'published';
        }));
    }
    return $posts;
}

// Get single post by ID or Slug
function get_post_by_id_or_slug($identifier) {
    $posts = get_all_posts(false);
    foreach ($posts as $post) {
        if (($post['id'] ?? '') === $identifier || ($post['slug'] ?? '') === $identifier) {
            return $post;
        }
    }
    return null;
}

// Generate URL slug from title
function generate_slug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// Save or Update Post
function save_post($data, $file = null) {
    $posts = get_all_posts(false);
    
    $id = !empty($data['id']) ? $data['id'] : 'post_' . time() . '_' . rand(100, 999);
    $is_edit = false;

    // Handle Image Upload if provided
    $image_path = !empty($data['existing_image']) ? $data['existing_image'] : 'images/blog_hero_img.png';
    if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
        $uploaded = upload_image($file);
        if ($uploaded) {
            $image_path = $uploaded;
        }
    } elseif (!empty($data['image_url'])) {
        $image_path = trim($data['image_url']);
    }

    $title = trim($data['title'] ?? 'Untitled Post');
    $slug = !empty($data['slug']) ? generate_slug($data['slug']) : generate_slug($title);

    // Map Category code & name
    $category_map = [
        'cloud' => 'Cloud & DevOps',
        'mobile' => 'Mobile Platforms',
        'retail' => 'Omnichannel Retail',
        'ai' => 'AI & Machine Learning',
        'web' => 'Web Platforms',
        'general' => 'Engineering'
    ];
    $category_code = $data['category_code'] ?? 'general';
    $category_name = $category_map[$category_code] ?? ($data['category'] ?? 'Engineering');

    $new_post = [
        'id' => $id,
        'slug' => $slug,
        'title' => $title,
        'category' => $category_name,
        'category_code' => $category_code,
        'author' => !empty($data['author']) ? trim($data['author']) : 'Bigmonks Engineering',
        'author_role' => !empty($data['author_role']) ? trim($data['author_role']) : 'Tech Lead',
        'date' => !empty($data['date']) ? $data['date'] : date('Y-m-d'),
        'read_time' => !empty($data['read_time']) ? trim($data['read_time']) : '5 Min Read',
        'image' => $image_path,
        'excerpt' => trim($data['excerpt'] ?? ''),
        'content' => $data['content'] ?? '',
        'status' => $data['status'] ?? 'published',
        'views' => (int)($data['views'] ?? 0)
    ];

    foreach ($posts as $key => $existing) {
        if (($existing['id'] ?? '') === $id) {
            $posts[$key] = array_merge($existing, $new_post);
            $is_edit = true;
            break;
        }
    }

    if (!$is_edit) {
        array_unshift($posts, $new_post);
    }

    file_put_contents(DATA_FILE, json_encode($posts, JSON_PRETTY_PRINT));
    return $new_post;
}

// Delete Post
function delete_post($id) {
    $posts = get_all_posts(false);
    $filtered = array_filter($posts, function($p) use ($id) {
        return ($p['id'] ?? '') !== $id;
    });
    file_put_contents(DATA_FILE, json_encode(array_values($filtered), JSON_PRETTY_PRINT));
    return true;
}

// Process Image Upload
function upload_image($file) {
    $target_dir = UPLOAD_DIR;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    
    if (!in_array($ext, $allowed)) {
        return false;
    }
    
    $filename = 'blog_' . time() . '_' . rand(100, 999) . '.' . $ext;
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return 'uploads/' . $filename;
    }
    return false;
}
