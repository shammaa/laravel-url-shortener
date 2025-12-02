# Laravel URL Shortener

A professional, feature-rich URL shortener package for Laravel with password protection, QR codes, advanced analytics, hidden UTM tracking, and much more.

## Features

- 🔒 **Password Protection** - Protect your links with passwords
- 📊 **Advanced Analytics** - Track clicks, locations, devices, browsers, and more
- 🎯 **Hidden UTM Tracking** - Track UTM parameters without showing them in URLs
- 📱 **QR Code Generation** - Automatic QR code generation for all links
- ⏰ **Link Expiry** - Set expiration dates for links
- 🔢 **Click Limits** - Limit the number of clicks per link
- 🌍 **Geographic Tracking** - Track visitor locations (country, city)
- 📱 **Device Tracking** - Track devices, browsers, platforms
- 🔗 **Custom Domains** - Use custom domains for shortened URLs
- 🚀 **High Performance** - Optimized with caching
- 🔌 **API Support** - Full REST API included
- 📈 **Real-time Analytics** - Real-time visit tracking
- 🎨 **Professional UI** - Beautiful password protection page

## Installation

```bash
composer require shammaa/laravel-url-shortener
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=url-shortener-config
```

Publish migrations:

```bash
php artisan vendor:publish --tag=url-shortener-migrations
```

Run migrations:

```bash
php artisan migrate
```

## Quick Start

### Create a Short Link

```php
use Shammaa\LaravelUrlShortener\Facades\UrlShortener;

// Simple short link
$link = UrlShortener::create([
    'destination_url' => 'https://example.com/very-long-url',
]);

echo $link->short_url; // https://yoursite.com/s/abc123

// With password protection
$link = UrlShortener::create([
    'destination_url' => 'https://example.com/secret',
    'password' => 'my-secret-password',
]);

// With expiry
$link = UrlShortener::create([
    'destination_url' => 'https://example.com/temporary',
    'expires_in_days' => 30,
]);

// With click limit
$link = UrlShortener::create([
    'destination_url' => 'https://example.com/limited',
    'click_limit' => 100,
]);

// With custom key
$link = UrlShortener::create([
    'destination_url' => 'https://example.com/custom',
    'key' => 'my-custom-key',
]);

// With UTM tracking
$link = UrlShortener::create([
    'destination_url' => 'https://example.com/tracked',
    'utm_parameters' => [
        'utm_source' => 'newsletter',
        'utm_campaign' => 'promo2024',
    ],
]);
```

### Using Helper Function

```php
// Simple
$link = short_url('https://example.com');

// With options
$link = short_url('https://example.com', [
    'title' => 'My Link',
    'password' => 'secret123',
    'expires_in_days' => 7,
    'click_limit' => 50,
]);
```

## Advanced Features

### Password Protection

```php
$link = UrlShortener::create([
    'destination_url' => 'https://example.com/secret',
    'password' => 'my-password',
    'password_protected' => true,
]);
```

Users will be prompted for a password when accessing the link. The password is verified in session, so they don't need to enter it again.

### QR Code Generation

QR codes are automatically generated for all links:

```php
$link = UrlShortener::create([
    'destination_url' => 'https://example.com',
]);

// QR code is automatically generated
$qrCodeUrl = $link->qr_code_path; // Storage path
```

Access QR code via API:
```
GET /api/url-shortener/links/{key}/qr-code
```

### Hidden UTM Tracking

Track UTM parameters without showing them in the URL:

```php
$link = UrlShortener::create([
    'destination_url' => 'https://example.com',
    'utm_parameters' => [
        'utm_source' => 'newsletter',
        'utm_medium' => 'email',
        'utm_campaign' => 'promo2024',
    ],
    'utm_hidden' => true, // Hide in URL but track internally
]);
```

The UTM parameters will be tracked in analytics but won't appear in the destination URL.

### Link Expiry

Set expiration dates for links:

```php
$link = UrlShortener::create([
    'destination_url' => 'https://example.com',
    'expires_in_days' => 30, // Expires in 30 days
]);

// Or set specific date
$link = UrlShortener::create([
    'destination_url' => 'https://example.com',
    'expires_at' => now()->addMonths(3),
]);
```

### Click Limits

Limit the number of clicks:

```php
$link = UrlShortener::create([
    'destination_url' => 'https://example.com',
    'click_limit' => 100, // Stops working after 100 clicks
]);
```

### Advanced Analytics

The package automatically tracks:
- Total clicks
- Unique visitors (by IP)
- Geographic location (country, city)
- Device types (desktop, mobile, tablet)
- Browsers and platforms
- Referrers
- UTM parameters
- Time-based statistics

Access analytics via API:

```php
GET /api/url-shortener/links/{key}/analytics
```

Or in code:

```php
$link = UrlShortener::findByKey('abc123');

// Get all visits
$visits = $link->visits;

// Get statistics
$totalClicks = $link->clicks_count;
$uniqueClicks = $link->visits()->distinct('ip_address')->count();
$clicksToday = $link->visits()->today()->count();
$clicksByCountry = $link->visits()->selectRaw('country, COUNT(*) as clicks')
    ->groupBy('country')
    ->get();
```

## API Usage

### Create Link

```bash
POST /api/url-shortener/links
Content-Type: application/json

{
    "destination_url": "https://example.com",
    "title": "My Link",
    "password": "secret",
    "expires_in_days": 30,
    "click_limit": 100
}
```

### Get Link

```bash
GET /api/url-shortener/links/{key}
```

### Get Analytics

```bash
GET /api/url-shortener/links/{key}/analytics
```

### Get QR Code

```bash
GET /api/url-shortener/links/{key}/qr-code
```

### Update Link

```bash
PUT /api/url-shortener/links/{key}

{
    "is_active": false,
    "expires_at": "2024-12-31 23:59:59"
}
```

### Delete Link

```bash
DELETE /api/url-shortener/links/{key}
```

## Using with Models

The `HasShortLink` trait allows you to automatically create short links for any Eloquent model. This works similarly to the `ash-jc-allen/short-url` package, automatically generating custom keys with a **model prefix + random code**.

### What is the `HasShortLink` Trait?

The `HasShortLink` trait is a powerful feature that automatically associates any Eloquent model with a short link. Instead of manually creating short links and managing keys, you simply add the trait to your model and call `createShortLink()`.

**Key Format:** `{model-name}-{random-code}`

**Examples:**
- `post-xyz1` → `post` = model name, `xyz1` = random code
- `post-coco` → `post` = model name, `coco` = random code  
- `article-abc456` → `article` = model name, `abc456` = random code

### Why Use This Trait?

**Before (without trait):**
```php
// You had to manually create links every time
$link = UrlShortener::create([
    'destination_url' => route('posts.show', $post),
    'key' => 'post-' . Str::random(4), // Manual key generation
]);

// Problems:
// ❌ Manual key generation
// ❌ Need to know the route
// ❌ No relationship between model and link
// ❌ Repetitive code
```

**After (with trait):**
```php
// Just add trait and use it!
class Post extends Model {
    use HasShortLink;
}

$post->createShortLink();
// ✅ Automatic key generation
// ✅ Automatic URL detection
// ✅ Relationship established
// ✅ One line of code!
```

### Basic Usage

#### Step 1: Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Shammaa\LaravelUrlShortener\Traits\HasShortLink;

class Post extends Model
{
    use HasShortLink;
    
    protected $fillable = ['title', 'slug', 'content'];
}
```

#### Step 2: Create Your Model Instance

```php
$post = Post::create([
    'title' => 'My Article',
    'slug' => 'my-article',
    'content' => 'Article content...',
]);
```

#### Step 3: Create the Short Link

```php
// Method 1: Manual creation
$shortLink = $post->createShortLink();

echo $shortLink->key; 
// Output: "post-xyz1" (model name + random code)

echo $shortLink->short_url; 
// Output: "https://yoursite.com/s/post-xyz1"

// Method 2: Direct access
echo $post->short_url; 
// Output: "https://yoursite.com/s/post-xyz1"
```

### How Key Generation Works

The trait follows a simple 4-step process:

#### Step 1: Extract Model Name
The trait automatically converts your model class name to a hyphenated, lowercase prefix format:
- `Post` → `post`
- `BlogPost` → `blog-post`
- `Article` → `article`

#### Step 2: Generate Random Code
A random code is generated using characters from your configuration:
- Default length: 4 characters
- Characters: `a-zA-Z0-9`
- Example outputs: `xyz1`, `coco`, `abc456`

#### Step 3: Combine Them
The model name and random code are combined with a hyphen:
```php
"post" + "-" + "xyz1" = "post-xyz1"
```

#### Step 4: Ensure Uniqueness
The trait checks if the key already exists in the database:
```php
if (ShortLink::where('key', 'post-xyz1')->exists()) {
    // Generate a new random code and try again
    // This process repeats until a unique key is found
}
```

### Complete Example

Here's a complete working example:

```php
// 1. Model definition
class Post extends Model
{
    use HasShortLink;
    
    protected $fillable = ['title', 'slug', 'content'];
}

// 2. Create a post
$post = Post::create([
    'title' => 'My First Article',
    'slug' => 'my-first-article',
    'content' => 'This is the article content...',
]);

// 3. Create short link
$post->createShortLink();

// 4. Access the short link
echo $post->short_url;
// Output: "https://yoursite.com/s/post-xyz1"

echo $post->shortLink->key;
// Output: "post-xyz1"
```

### Different Model Examples

The trait works with any Eloquent model:

```php
// Post model
$post = Post::create(['title' => 'Article']);
$post->createShortLink();
// Key: "post-xyz1"

// Article model
$article = Article::create(['title' => 'News']);
$article->createShortLink();
// Key: "article-abc2"

// Product model
$product = Product::create(['name' => 'Widget']);
$product->createShortLink();
// Key: "product-def3"
```

### Configure Random Code Length

You can customize the length of the random code in your config:

```php
// config/url-shortener.php
'model_key_length' => 4, // Default: 4 characters

// Or via .env
URL_SHORTENER_MODEL_KEY_LENGTH=6
```

**Examples:**
- Length 4: `post-xyz1`
- Length 6: `post-xyz123`, `post-abc456`

### Custom Prefix Field

You can add a custom field to your model to specify the prefix instead of using the model name:

#### Option 1: Database Field

```php
// Migration
Schema::table('posts', function (Blueprint $table) {
    $table->string('short_link_prefix')->nullable();
});

// Model
class Post extends Model
{
    use HasShortLink;
    
    protected $fillable = ['title', 'slug', 'short_link_prefix'];
}

// Usage
$post = Post::create([
    'title' => 'My Article',
    'short_link_prefix' => 'blog', // Custom prefix
]);

$post->createShortLink();
// Key: "blog-xyz1" (uses custom prefix instead of "post-xyz1")
```

#### Option 2: Override Method

```php
class Post extends Model
{
    use HasShortLink;
    
    protected function getShortLinkPrefix(): string
    {
        // Always use "blog" as prefix
        return 'blog';
    }
}

$post->createShortLink();
// Key: "blog-xyz1"
```

#### How Prefix Priority Works

The trait uses the following priority order:

1. **Custom field**: `short_link_prefix` (if exists in model)
2. **Custom method**: `getShortLinkPrefix()` (if exists)
3. **Model name**: Automatically extracted (default)

#### Understanding String Conversion to Hyphenated Format

The trait automatically converts class names to a hyphenated, lowercase format (also known as dash-case or slug format):

```php
// The conversion transforms CamelCase/PascalCase to hyphenated lowercase format
'BlogPost'      // → "blog-post"
'Article'       // → "article"
'ProductItem'   // → "product-item"

// How the conversion process works:
// Step 1: Takes the input string (e.g., "BlogPost")
// Step 2: Converts all characters to lowercase: "blogpost"
// Step 3: Inserts hyphens before capital letters: "blog-post"

// In the trait implementation:
class_basename($this)        // "App\Models\Post" → "Post"
// Converts to hyphenated format: "Post" → "post"
// For compound names: "BlogPost" → "blog-post"
```

**Technical Note:** This conversion uses Laravel's string helper methods to transform class names into URL-friendly, hyphenated format suitable for use in URLs and identifiers.

**Examples:**

```php
// Example 1: Default (uses model name)
class Post extends Model {
    use HasShortLink;
}
$post->createShortLink();
// Key: "post-xyz1"

// Example 2: Custom prefix field
$post->short_link_prefix = 'blog';
$post->createShortLink();
// Key: "blog-xyz1"

// Example 3: Custom method
protected function getShortLinkPrefix(): string {
    return 'news';
}
$post->createShortLink();
// Key: "news-xyz1"
```

### Custom Key Generation

You can override the entire key generation method in your model for custom formats:

```php
class Post extends Model
{
    use HasShortLink;
    
    // Custom key generation - use your own format
    public function generateShortLinkKey(): string
    {
        // Convert model name to hyphenated lowercase format
        $modelName = Str::kebab(class_basename($this));
        $randomCode = $this->generateRandomCode(); // 4 chars by default
        
        // Custom format: post-xyz1-2024
        return "{$modelName}-{$randomCode}-" . date('Y');
    }
    
    // Or customize the random code generation
    protected function generateRandomCode(): string
    {
        // Use only lowercase letters
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $code = '';
        for ($i = 0; $i < 4; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }
}
```

### Custom Destination URL

Override the destination URL method:

```php
class Post extends Model
{
    use HasShortLink;
    
    public function getShortLinkUrl(): string
    {
        // Return the URL to the post page
        return route('posts.show', $this);
    }
}
```

### Manual Key

You can also specify a custom key manually:

```php
$post->createShortLink([
    'key' => 'my-custom-key', // Custom key
    'title' => 'My Post', // Optional
    'password' => 'secret', // Optional
    'expires_in_days' => 30, // Optional
]);
```

### Check if Short Link Exists

```php
// Check if a short link exists for this model
if ($post->hasShortLink()) {
    echo $post->short_url;
} else {
    // Create one if it doesn't exist
    $post->createShortLink();
}
```

### Relationships

The trait establishes a polymorphic one-to-one relationship between your model and the short link:

```php
// Access the relationship
$post->shortLink(); // Returns the ShortLink model instance

// Check if relationship exists
$post->shortLink; // Returns ShortLink or null

// Access from ShortLink back to model
$shortLink = $post->shortLink;
$originalModel = $shortLink->shortLinkable; // Returns the Post instance

// Get short URL directly
$post->short_url; // Returns full short URL or null
```

**Important Notes:**
- Each model instance can have only **one** short link
- If you call `createShortLink()` multiple times, it returns the existing link
- The relationship is polymorphic, so it works with any model type

### How URL Detection Works

The trait automatically tries to detect the URL for your model using these methods:

#### Method 1: Route Name Detection
The trait tries to find a route based on your model name:

```php
// For Post model, it tries:
- "posts.show"
- "post.show"

// For Article model, it tries:
- "articles.show"
- "article.show"
```

#### Method 2: Custom URL Method
If your model has a `getUrl()` method, it will use it:

```php
class Post extends Model
{
    use HasShortLink;
    
    public function getUrl()
    {
        return route('posts.show', $this->slug);
    }
}
```

#### Method 3: Fallback URL
If neither method works, it generates a fallback URL:

```php
// Format: {app_url}/{model-name}/{id}
// Example: https://yoursite.com/post/123
```

#### Override URL Detection

You can override the URL detection method in your model:

```php
class Post extends Model
{
    use HasShortLink;
    
    public function getShortLinkUrl(): string
    {
        // Custom URL logic
        return route('blog.show', ['slug' => $this->slug]);
    }
}
```

### Database Structure

The trait uses a polymorphic relationship, which requires these fields in the `short_links` table:

```php
// Migration automatically includes:
$table->morphs('short_linkable');
// Creates:
// - short_linkable_type (stores model class name)
// - short_linkable_id (stores model ID)
```

**Example Database Record:**
```
id: 1
key: post-xyz1
destination_url: https://yoursite.com/posts/my-article
short_linkable_type: App\Models\Post
short_linkable_id: 123
created_at: 2024-01-01 12:00:00
```

This allows one `short_links` table to store links for multiple model types.

### Real-World Complete Example

Here's a complete real-world example showing how to integrate the trait into your blog application:

#### 1. Model Setup

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Shammaa\LaravelUrlShortener\Traits\HasShortLink;

class Post extends Model
{
    use HasShortLink;
    
    protected $fillable = ['title', 'slug', 'content', 'user_id'];
    
    /**
     * Automatically create a short link when a post is created
     */
    protected static function booted()
    {
        static::created(function ($post) {
            $post->createShortLink([
                'title' => $post->title,
            ]);
        });
    }
}
```

#### 2. Routes Setup

```php
// routes/web.php
Route::get('/posts/{post}', [PostController::class, 'show'])
    ->name('posts.show');
```

#### 3. Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function show(Post $post)
    {
        return view('posts.show', [
            'post' => $post,
            // Short URL is automatically available via $post->short_url
        ]);
    }
}
```

#### 4. Blade Template

```blade
{{-- resources/views/posts/show.blade.php --}}
<div class="post">
    <h1>{{ $post->title }}</h1>
    <p>{{ $post->content }}</p>
    
    {{-- Share section with short link --}}
    <div class="share-section">
        <h3>Share this post:</h3>
        
        @if($post->hasShortLink())
            <div class="short-url">
                <input type="text" 
                       value="{{ $post->short_url }}" 
                       readonly 
                       id="shortUrl">
                <button onclick="copyToClipboard('{{ $post->short_url }}')">
                    Copy Link
                </button>
            </div>
            
            {{-- Social sharing buttons --}}
            <div class="social-share">
                <a href="https://twitter.com/intent/tweet?url={{ $post->short_url }}" 
                   target="_blank">
                    Share on Twitter
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $post->short_url }}" 
                   target="_blank">
                    Share on Facebook
                </a>
            </div>
        @else
            <p>Short link not available</p>
        @endif
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Link copied to clipboard!');
    });
}
</script>
```

#### 5. Expected Results

When you create a new post:

```
Post #1:
- Full URL: https://yoursite.com/posts/my-first-article
- Short URL: https://yoursite.com/s/post-xyz1
- Key: post-xyz1

Post #2:
- Full URL: https://yoursite.com/posts/another-article
- Short URL: https://yoursite.com/s/post-abc2
- Key: post-abc2
```

### Key Benefits

#### 1. Automatic Key Generation
- ✅ No manual key management required
- ✅ Format: `{model-name}-{random-code}` (e.g., `post-xyz1`)
- ✅ Automatically ensures uniqueness

#### 2. Automatic URL Detection
- ✅ Automatically detects routes based on model name
- ✅ Works with standard Laravel route patterns (`posts.show`, `articles.show`)
- ✅ Fallback URL generation if route not found

#### 3. Polymorphic Relationship
- ✅ Works with any Eloquent model (Post, Article, Product, etc.)
- ✅ One-to-one relationship between model and short link
- ✅ If you call `createShortLink()` again, it returns the existing link

#### 4. Easy Integration
- ✅ Just add `use HasShortLink;` to your model
- ✅ No database schema changes needed (migration already includes polymorphic fields)
- ✅ Works with existing models without modifications

#### 5. Flexible & Customizable
- ✅ Override key generation method for custom formats
- ✅ Override URL detection method for custom routes
- ✅ Configure random code length via config file
- ✅ Pass additional options when creating links

### Comparison: Before vs After

| Feature | Without Trait | With Trait |
|---------|---------------|------------|
| **Code Lines** | 5-10 lines | 1 line |
| **Key Generation** | Manual | Automatic |
| **URL Detection** | Manual | Automatic |
| **Relationship** | None | Polymorphic |
| **Uniqueness Check** | Manual | Automatic |
| **Maintenance** | High | Low |

### Quick Reference

**Minimum code needed:**
```php
// 1. Add trait
class Post extends Model {
    use HasShortLink;
}

// 2. Create link
$post->createShortLink();

// 3. Use link
echo $post->short_url;
```

**That's it!** The trait handles everything else automatically.

## Configuration Options

Edit `config/url-shortener.php`:

```php
// Short URL prefix
'prefix' => 's', // Links will be: /s/abc123

// Key length
'key_length' => 6,

// QR Code settings
'qr_code' => [
    'enabled' => true,
    'size' => 200,
    'format' => 'svg', // svg or png
],

// UTM tracking
'utm' => [
    'enabled' => true,
    'hidden' => true, // Hide in URL but track internally
],

// Tracking options
'track_visits' => true,
'track_ip_address' => true,
'track_user_agent' => true,
'track_referer' => true,
'track_geo' => false, // Requires geolocation service
```

## Performance

- **Smart Caching** - Links are cached for fast retrieval
- **Optimized Queries** - Efficient database queries with proper indexing
- **Minimal Overhead** - Lightweight tracking with minimal performance impact

## License

MIT

## Author

Shadi Shammaa

## Support

For issues and feature requests, please use the GitHub issue tracker.
