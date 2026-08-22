/**
 * Bigmonks Technologies - Client-Side Blog CMS Store (No PHP / No External Server Required)
 * Manages blog articles in LocalStorage with fallback to seed data.
 * Supports image file uploads (base64 DataURL), image URLs, rich HTML content, JSON export/import, and CRUD operations.
 */

const LOCAL_STORAGE_KEY = 'bigmonks_blog_posts_v1';

// Initial seed data with rich content for Bigmonks engineering articles
const DEFAULT_SEED_BLOGS = [
  {
    id: 'blog-101',
    slug: 'building-sub-15ms-apis-graphql-redis-kubernetes',
    title: 'Building Sub-15ms APIs with GraphQL, Redis Edge & Kubernetes',
    category: 'cloud',
    categoryLabel: 'Cloud & DevOps',
    author: 'Alex Rivera',
    authorRole: 'Principal Cloud Architect',
    authorImage: 'images/apple-touch-icon.png',
    date: 'AUG 15, 2026',
    readTime: '5 Min Read',
    image: 'images/blog_api_architecture.png',
    excerpt: 'A comprehensive guide on eliminating database latency bottlenecks using distributed Redis caching layers and edge API gateways.',
    status: 'published',
    content: `
      <h2>The Challenge of Latency at Enterprise Scale</h2>
      <p>As enterprise web applications scale to handle hundreds of thousands of concurrent users, traditional database querying patterns quickly become severe bottlenecks. Every microservice attempting to query relational or document databases creates network roundtrips that compound overall response times.</p>
      
      <div class="my-6 p-6 rounded-2xl bg-slate-900 text-slate-100 border border-slate-800">
        <h4 class="font-bold text-white mb-2 text-sm uppercase tracking-wider">Key Architecture Takeaway</h4>
        <p class="text-xs text-slate-300 leading-relaxed font-mono">By placing multi-region Redis clusters at the network edge and routing queries via optimized GraphQL resolvers, we achieved a sustained p99 latency under 14.2ms across global endpoints.</p>
      </div>

      <h3>Architectural Layer Breakdown</h3>
      <p>Our engineering team structured the API execution pipeline into three ultra-low latency layers:</p>
      <ul>
        <li><strong>Edge API Gateway:</strong> Envoy proxies deployed close to regional POPs to terminate TLS connections instantly.</li>
        <li><strong>GraphQL Schema Federation:</strong> Unifying 14 underlying microservice schemas into a single performant edge graph.</li>
        <li><strong>Distributed L2 Caching:</strong> Redis Cluster with read-through invalidation hooks powered by Kafka event consumers.</li>
      </ul>

      <h3>Sample Resolver Implementation</h3>
      <pre><code>// GraphQL Edge Resolver with Redis Cache Strategy
const getCatalogItem = async (parent, { id }, context) => {
  const cacheKey = \`catalog:\${id}\`;
  const cached = await context.redis.get(cacheKey);
  if (cached) return JSON.parse(cached);

  const item = await context.db.items.findUnique({ where: { id } });
  await context.redis.set(cacheKey, JSON.stringify(item), 'EX', 300);
  return item;
};</code></pre>

      <h3>Results and Performance Metrics</h3>
      <p>Following full deployment across AWS EKS clusters, database CPU utilization plummeted by 68% while throughput increased by 4.2x without dropping a single HTTP connection during peak sale events.</p>
    `
  },
  {
    id: 'blog-102',
    slug: 'why-swiftui-jetpack-compose-revolutionizing-retail-pos',
    title: 'Why SwiftUI & Jetpack Compose are Revolutionizing Retail POS',
    category: 'mobile',
    categoryLabel: 'Mobile Platforms',
    author: 'Elena Rostova',
    authorRole: 'Lead Mobile Systems Engineer',
    authorImage: 'images/apple-touch-icon.png',
    date: 'AUG 12, 2026',
    readTime: '4 Min Read',
    image: 'images/blog_mobile_pos.png',
    excerpt: 'Analyzing 120Hz native UI rendering pipelines for in-store tablet checkout terminals and offline SQLite synchronization.',
    status: 'published',
    content: `
      <h2>The Shift to Modern Declarative Mobile UIs</h2>
      <p>Legacy Point-of-Sale (POS) hardware was notorious for slow UI transitions, rigid layouts, and complex state management. With modern iOS (SwiftUI) and Android (Jetpack Compose), retail tablet interfaces are transitioning into sleek, high-frame-rate, reactive touch surfaces.</p>

      <h3>120Hz Motion & Responsive Layouts</h3>
      <p>Declarative UI frameworks allow engineering teams to write dynamic layouts that instantly adapt to tablet screen rotations, customer-facing dual screens, and barcode scanner accessory integration.</p>

      <blockquote class="border-l-4 border-black pl-4 py-2 my-6 font-semibold italic text-slate-800 text-base">
        "Transitioning our client's POS tablet software to declarative SwiftUI cut UI code size by 45% while guaranteeing 120fps animations during fast inventory lookups."
      </blockquote>

      <h3>Offline-First Architecture</h3>
      <p>In-store environments often experience temporary Wi-Fi drops. Utilizing SQLite with Room (Android) and GRDB (iOS), transactions are serialized locally into an encrypted persistent queue and seamlessly pushed to cloud endpoints upon reconnection.</p>
    `
  },
  {
    id: 'blog-103',
    slug: 'monolith-to-serverless-99-99-reliability-case-study',
    title: 'From Monolith to Serverless: A 99.99% Reliability Case Study',
    category: 'retail',
    categoryLabel: 'Omnichannel Retail',
    author: 'Marcus Vance',
    authorRole: 'VP of Platform Engineering',
    authorImage: 'images/apple-touch-icon.png',
    date: 'AUG 08, 2026',
    readTime: '7 Min Read',
    image: 'images/blog_cloud_serverless.png',
    excerpt: 'How Bigmonks refactored a legacy retail catalog backend into AWS Lambda and Kafka event-driven microservices.',
    status: 'published',
    content: `
      <h2>Deconstructing the Legacy Retail Monolith</h2>
      <p>Omnichannel retail platforms handle distinct spikes during promotional launches. Monolithic architectures often struggle to scale specific hot paths like inventory reservation or payment processing without provisioning massive infrastructure over-capacity.</p>

      <h3>Event-Driven Serverless Pattern</h3>
      <p>By decoupling order ingestion into Apache Kafka topics and processing updates via AWS Lambda functions with DynamoDB global tables, system availability reached 99.99% multi-region uptime.</p>
    `
  },
  {
    id: 'blog-104',
    slug: 'predictive-inventory-models-llms-vector-search-cut-waste',
    title: 'Predictive Inventory Models: How LLMs & Vector Search Cut Waste by 40%',
    category: 'ai',
    categoryLabel: 'AI & Machine Learning',
    author: 'Dr. Sarah Chen',
    authorRole: 'Head of AI Research',
    authorImage: 'images/apple-touch-icon.png',
    date: 'AUG 02, 2026',
    readTime: '8 Min Read',
    image: 'images/blog_ai_inventory.png',
    excerpt: 'Implementing machine learning time-series forecasting to automate supply chain reorders with zero manual intervention.',
    status: 'published',
    content: `
      <h2>AI-Powered Supply Chain Intelligence</h2>
      <p>Traditional reordering systems rely on static threshold rules. By pairing vector embeddings of product metadata with real-time sales trends, our models accurately forecast regional demand weeks in advance.</p>

      <h3>Vector Similarity in Product Catalogs</h3>
      <p>Vector databases allow instant identification of cannibalizing product substitutes, seasonal demand shifts, and supplier risk factors automatically.</p>
    `
  },
  {
    id: 'blog-105',
    slug: 'zero-downtime-blue-green-deployment-strategies',
    title: 'Zero-Downtime Blue/Green Deployment Strategies for Global Scale',
    category: 'cloud',
    categoryLabel: 'Cloud & DevOps',
    author: 'Liam O\'Connor',
    authorRole: 'Lead Site Reliability Engineer',
    authorImage: 'images/apple-touch-icon.png',
    date: 'JUL 27, 2026',
    readTime: '5 Min Read',
    image: 'images/blog_devops_deployment.png',
    excerpt: 'Automating Terraform Infrastructure as Code and GitHub Actions for continuous release pipelines without dropping a single HTTP request.',
    status: 'published',
    content: `
      <h2>Continuous Delivery without Downtime</h2>
      <p>Deploying production updates to global backend systems requires zero customer disruption. Blue/Green deployment routing combined with automated smoke test verifications ensures smooth releases every time.</p>
    `
  },
  {
    id: 'blog-106',
    slug: 'designing-conversion-driven-web-systems-headless-nextjs-15',
    title: 'Designing Conversion-Driven Web Systems with Headless Next.js 15',
    category: 'web',
    categoryLabel: 'Web Platforms',
    author: 'Vikram Mehta',
    authorRole: 'Lead Frontend Architect',
    authorImage: 'images/apple-touch-icon.png',
    date: 'JUL 20, 2026',
    readTime: '6 Min Read',
    image: 'images/blog_web_nextjs.png',
    excerpt: 'Exploring React Server Components (RSC), partial pre-rendering, and layout shift optimizations for 100/100 Lighthouse performance.',
    status: 'published',
    content: `
      <h2>Sub-Second Web Experiences in Next.js 15</h2>
      <p>Speed is directly linked to web conversion rates. By utilizing server-side streaming, static asset optimization, and modular UI components, modern web platforms deliver instant page loads across all devices.</p>
    `
  }
];

class BlogCMSStore {
  constructor() {
    this.initStore();
  }

  initStore() {
    const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
    if (!raw) {
      this.saveAll(DEFAULT_SEED_BLOGS);
    }
  }

  getAll() {
    try {
      const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
      if (!raw) return DEFAULT_SEED_BLOGS;
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) && parsed.length > 0 ? parsed : DEFAULT_SEED_BLOGS;
    } catch (e) {
      console.error('Error parsing blogs from LocalStorage:', e);
      return DEFAULT_SEED_BLOGS;
    }
  }

  getPublished() {
    return this.getAll().filter(b => b.status === 'published' || !b.status);
  }

  getBySlug(slug) {
    if (!slug) return null;
    const blogs = this.getAll();
    return blogs.find(b => b.slug.toLowerCase() === slug.toLowerCase()) || null;
  }

  getById(id) {
    if (!id) return null;
    const blogs = this.getAll();
    return blogs.find(b => b.id === id) || null;
  }

  saveBlog(blogData) {
    const blogs = this.getAll();
    const existingIndex = blogs.findIndex(b => b.id === blogData.id);

    const formattedBlog = {
      id: blogData.id || 'blog-' + Date.now(),
      slug: blogData.slug ? this.slugify(blogData.slug) : this.slugify(blogData.title),
      title: blogData.title || 'Untitled Post',
      category: blogData.category || 'cloud',
      categoryLabel: this.getCategoryLabel(blogData.category),
      author: blogData.author || 'Bigmonks Editorial Team',
      authorRole: blogData.authorRole || 'Engineering Contributor',
      authorImage: blogData.authorImage || 'images/apple-touch-icon.png',
      date: blogData.date || this.formatDate(new Date()),
      readTime: blogData.readTime || '5 Min Read',
      image: blogData.image || 'images/blog_hero_img.png',
      excerpt: blogData.excerpt || '',
      content: blogData.content || '<p>Article content coming soon.</p>',
      status: blogData.status || 'published',
      updatedAt: new Date().toISOString()
    };

    if (existingIndex >= 0) {
      blogs[existingIndex] = { ...blogs[existingIndex], ...formattedBlog };
    } else {
      blogs.unshift(formattedBlog); // Insert new blog at the top
    }

    this.saveAll(blogs);
    return formattedBlog;
  }

  deleteBlog(id) {
    const blogs = this.getAll().filter(b => b.id !== id);
    this.saveAll(blogs);
  }

  saveAll(blogs) {
    try {
      localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(blogs));
    } catch (e) {
      console.error('LocalStorage save error (storage quota may be exceeded if images are very large):', e);
      alert('Warning: LocalStorage quota limit reached. Try using image URLs instead of heavy image uploads.');
    }
  }

  resetToDefaults() {
    this.saveAll(DEFAULT_SEED_BLOGS);
    return DEFAULT_SEED_BLOGS;
  }

  exportJSON() {
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(this.getAll(), null, 2));
    const downloadAnchor = document.createElement('a');
    downloadAnchor.setAttribute("href", dataStr);
    downloadAnchor.setAttribute("download", `bigmonks_blogs_backup_${Date.now()}.json`);
    document.body.appendChild(downloadAnchor);
    downloadAnchor.click();
    downloadAnchor.remove();
  }

  importJSON(jsonString) {
    try {
      const parsed = JSON.parse(jsonString);
      if (Array.isArray(parsed)) {
        this.saveAll(parsed);
        return true;
      }
      return false;
    } catch (e) {
      console.error('Failed to import JSON:', e);
      return false;
    }
  }

  slugify(text) {
    return text
      .toString()
      .toLowerCase()
      .trim()
      .replace(/[\s\W-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  getCategoryLabel(categoryKey) {
    const catMap = {
      'ai': 'AI & Machine Learning',
      'cloud': 'Cloud & DevOps',
      'mobile': 'Mobile Platforms',
      'retail': 'Omnichannel Retail',
      'web': 'Web Platforms'
    };
    return catMap[categoryKey] || 'Engineering Insights';
  }

  formatDate(dateObj) {
    const d = new Date(dateObj);
    const month = d.toLocaleString('en-US', { month: 'short' }).toUpperCase();
    const day = String(d.getDate()).padStart(2, '0');
    const year = d.getFullYear();
    return `${month} ${day}, ${year}`;
  }

  // Convert File object to Base64 Data URL
  readFileAsDataURL(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = (e) => resolve(e.target.result);
      reader.onerror = (e) => reject(e);
      reader.readAsDataURL(file);
    });
  }
}

// Global Singleton Instance
window.blogStore = new BlogCMSStore();
