<?php
// public/includes/header.php

// Ensure main config is loaded for constants like SITE_NAME, BASE_URL etc.
// It's crucial that config.php is included before this header is used.
// One way is to include it in each public-facing PHP file that uses this header,
// or have a central bootstrap file.
if (file_exists(__DIR__ . '/../../config/config.php')) {
    require_once __DIR__ . '/../../config/config.php';
} else {
    // Fallback definitions if config.php isn't loaded, though it should be.
    defined('SITE_NAME') or define('SITE_NAME', 'dipug.com');
    // Attempt to autodetect BASE_URL if not set by config
    if (!defined('BASE_URL')) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Basic assumption: if this header is in public/includes, and script is in public/, then /public is part of script_name
        $script_name_path = dirname($_SERVER['SCRIPT_NAME']);
        // A common setup is that BASE_URL points to the directory containing 'public', not 'public' itself.
        // If SCRIPT_NAME is /public/index.php, dirname is /public. We want the parent of /public.
        // This is still a guess and a defined BASE_URL in config.php is far more reliable.
        $base_path_guess = preg_replace('/\/public$/', '', $script_name_path); // Remove /public if it's at the end
        define('BASE_URL', rtrim($protocol . $host . $base_path_guess, '/'));
    }
    // Define ADMIN_BASE_URL as well if not defined, relative to the guessed BASE_URL
    defined('ADMIN_BASE_URL') or define('ADMIN_BASE_URL', rtrim(BASE_URL, '/') . '/admin');
}

// Ensure Auth class is available if Auth checks are used in the header
// This might be redundant if config.php or a bootstrap ensures it's loaded.
if (!class_exists('Auth') && file_exists(__DIR__ . '/../../lib/Auth.php')) {
    require_once __DIR__ . '/../../lib/Auth.php';
}


// Helper function to load site settings if not already defined (e.g. by config.php)
// This is a placeholder; actual implementation might differ based on Settings.php class
if (!function_exists('load_site_settings')) {
    function load_site_settings() {
        // In a real app, this would fetch from DB via Settings class
        // For now, return defaults or values from config if available
        return [
            'site_name' => defined('SITE_NAME') ? SITE_NAME : 'dipug.com',
            // ... other settings
        ];
    }
}

if (!function_exists('esc_html')) {
    function esc_html(string $string): string {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}


$page_title       = $page_title ?? (defined('SITE_NAME') ? SITE_NAME : 'dipug.com');
$meta_description = $meta_description ?? 'Welcome to ' . (defined('SITE_NAME') ? SITE_NAME : 'dipug.com');
$meta_keywords    = $meta_keywords ?? '';


// If SITE_NAME was not defined by main config, or is a placeholder, try to load it
// This section is a bit complex due to potential load order issues.
// Ideally, config.php (from .env or DB settings) establishes the true SITE_NAME.
if (!defined('SITE_NAME') || SITE_NAME === 'My Admin Panel' || SITE_NAME === 'My Awesome Site' || SITE_NAME === 'dipug.com') {
    // The above check includes default from .env as well, to allow override from a Settings class if it exists
    if (class_exists('Settings')) { // Assuming a Settings class might exist
        // $db_site_name = Settings::get('site_name'); // Hypothetical
        // if ($db_site_name && (!defined('SITE_NAME') || SITE_NAME !== $db_site_name)) {
        //     if (defined('SITE_NAME')) { /* Potentially redefine if truly necessary and allowed */ }
        //     else { define('SITE_NAME', $db_site_name); }
        //     $page_title = $page_title === (SITE_NAME . ' Admin') || $page_title === SITE_NAME ? $db_site_name : $page_title;
        // }
    }
    // If still not a good SITE_NAME, we rely on what config.php set (from .env or its defaults).
}


global $page; // Assuming $page is set in the calling script (e.g. public/index.php)
$current_public_page = $page ?? ($_GET['page'] ?? 'home');

$services_menu_items_header = [
    ["name" => "Web Development",   "page" => "web-development",  "icon" => "code-xml",       "desc" => "Modern websites & applications."],
    ["name" => "Software Solutions","page" => "software-solutions", "icon" => "app-window",    "desc" => "Custom software for your needs."],
    ["name" => "Online Courses",    "page" => "online-courses",   "icon" => "graduation-cap", "desc" => "Upskill with expert-led courses."],
    ["name" => "Tech Support",      "page" => "tech-support",     "icon" => "life-buoy",     "desc" => "Reliable IT assistance & consulting."],
    ["name" => "Cloud Solutions",   "page" => "cloud-solutions",  "icon" => "cloud-cog",     "desc" => "Scalable cloud infrastructure."],
    ["name" => "Cybersecurity",     "page" => "cybersecurity",    "icon" => "shield-check",  "desc" => "Protect your valuable digital assets."]
];

$main_nav_links = [
    ["name" => "Home",      "page" => "home",              "icon" => "home"],
    ["name" => "Services",  "page" => "services", "icon" => "layers", "is_mega_menu" => true],
    ["name" => "Portfolio", "page" => "portfolio",         "icon" => "briefcase"],
    ["name" => "Blog",      "page" => "blog",              "icon" => "book-open"],
    ["name" => "Contact",   "page" => "contact",           "icon" => "mail"]
];

// Ensure BASE_URL is correctly determined, especially if in a subfolder.
// config.php should handle this. If not, this is a basic fallback.
// Re-check BASE_URL as the one above might be too simplistic if config.php didn't run
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // More robust way to get the path if in a subdirectory, assuming 'public' is the webroot
    $script_path = dirname($_SERVER['SCRIPT_NAME']); // Path of the current script's directory
    // If script is /public/index.php, $script_path is /public. We want the part *before* /public
    // If script is /index.php (root), $script_path is /.
    // If script is /subdir/public/index.php, $script_path is /subdir/public.
    $base_path = preg_replace('/\/public$/', '', $script_path); // Remove /public from the end if present
    $base_path = rtrim($base_path, '/'); // Clean up trailing slashes
    define('BASE_URL', $protocol . $host . $base_path);
}
// Define ADMIN_BASE_URL if not already set by config.php
if (!defined('ADMIN_BASE_URL')) {
    define('ADMIN_BASE_URL', rtrim(BASE_URL, '/') . '/admin');
}

?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc_html($page_title); ?></title>
    <meta name="description" content="<?= esc_html($meta_description); ?>">
    <?php if (!empty($meta_keywords)): ?>
      <meta name="keywords" content="<?= esc_html($meta_keywords); ?>">
    <?php endif; ?>

    <!-- Tailwind CSS + custom color configuration -->
    <script src="https://cdn.tailwindcss.com?plugins=typography,forms"></script>
    <script>
      tailwind.config = {
        darkMode: 'class', // or 'media'
        theme: {
          extend: {
            colors: {
              primary:    { DEFAULT: '#0056B3', hover: '#004a99', light: '#e6f0ff' }, // Added light primary
              secondary:  { DEFAULT: '#4B5563', hover: '#374151' }, // Gray
              accent:     { DEFAULT: '#F59E0B', hover: '#d97706' }, // Amber
              background: { DEFAULT: '#F4F7F6', light: '#FFFFFF' }, // Off-white/very light gray
              text:       { DEFAULT: '#1F2937', light: '#4B5563', lighter: '#6B7280'}, // Dark gray, lighter gray

              // Semantic colors based on user's theme
              neutral:          '#F4F7F6', // background
              'neutral-content': '#1F2937', // text
              'neutral-focus':   '#0056B3', // primary

              'base-100':        '#FFFFFF', // background.light or card backgrounds
              'base-200':        '#F4F7F6', // background.default
              'base-300':        '#E5E7EB', // borders, lines

              info:             '#22d3ee', // Cyan
              success:          '#34d399', // Green
              warning:          '#F59E0B', // Amber
              error:            '#f87171', // Red
            },
            fontFamily: {
              sans:   ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', '"Helvetica Neue"', 'Arial', '"Noto Sans"', 'sans-serif', '"Apple Color Emoji"', '"Segoe UI Emoji"', '"Segoe UI Symbol"', '"Noto Color Emoji"'],
              display: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'] // For headings
            },
            transitionProperty: {
              height:     'height',
              spacing:    'margin, padding',
              'max-height': 'max-height'
            },
            animation: {
              'fade-in-up':   'fadeInUp 0.5s ease-out forwards',
              'slide-in-left': 'slideInLeft 0.5s ease-out forwards',
              'slide-down':   'slideDown 0.3s ease-out forwards',
              'slide-up':     'slideUp 0.3s ease-out forwards',
            },
            keyframes: {
              fadeInUp: {
                '0%':   { opacity: '0', transform: 'translateY(20px)' },
                '100%': { opacity: '1', transform: 'translateY(0)' }
              },
              slideInLeft: {
                '0%':   { opacity: '0', transform: 'translateX(-20px)' },
                '100%': { opacity: '1', transform: 'translateX(0)' }
              },
              slideDown: {
                '0%':   { opacity: '0', transform: 'translateY(-10%)' }, // Adjusted for smoother mega menu
                '100%': { opacity: '1', transform: 'translateY(0)' }
              },
              slideUp: { // Not used in current user HTML, but good to have
                '0%':   { opacity: '1', transform: 'translateY(0)' },
                '100%': { opacity: '0', transform: 'translateY(-10%)' }
              }
            }
          }
        }
      };
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Link to a potential theme.css for additional global styles if needed -->
    <link rel="stylesheet" href="<?= rtrim(BASE_URL, '/'); ?>/css/theme.css?v=<?php echo time(); ?>">
    <link rel="icon" href="<?= rtrim(BASE_URL, '/'); ?>/assets/favicon.ico" type="image/x-icon"> <!-- Assuming favicon in public/assets -->

    <style>
      body {
        font-family: tailwind.config.theme.extend.fontFamily.sans; /* Use Tailwind's sans font */
        background-color: tailwind.config.theme.extend.colors.background.DEFAULT;
        color: tailwind.config.theme.extend.colors.text.DEFAULT;
      }
      .font-display {
        font-family: tailwind.config.theme.extend.fontFamily.display;
      }
      .nav-link-active {
        color: tailwind.config.theme.extend.colors.primary.DEFAULT; /* Primary color for active link */
        font-weight: 600;
      }
      /* Mega Menu Styling */
      .mega-menu-container {
        display: none;
        opacity: 0;
        transform: translateY(10px); /* Start slightly lower */
        transition: opacity 0.3s ease-out, transform 0.3s ease-out;
        pointer-events: none; /* Important for not blocking elements below when hidden */
      }
      .group:hover .mega-menu-container,
      .mega-menu-trigger:focus + .mega-menu-container, /* For accessibility */
      .mega-menu-container:hover { /* Keep open when hovering the menu itself */
        display: block;
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
      }
      /* Mobile Menu Styling */
      .mobile-menu {
        max-height: 0;
        overflow-y: auto; /* Allow scrolling if content exceeds viewport */
        /* transition: max-height 0.4s cubic-bezier(0.25, 0.1, 0.25, 1); */
        transition: max-height 0.3s ease-out; /* Smoother transition */
      }
      .mobile-menu.open {
        max-height: calc(100vh - 4rem); /* Adjust based on header height */
      }
    </style>
</head>
<body class="antialiased text-text flex flex-col min-h-screen">

  <!-- Top Bar -->
  <div class="bg-base-200 text-text-light text-xs print:hidden border-b border-base-300">
    <div class="container mx-auto flex justify-between items-center py-2 px-4 sm:px-6 lg:px-8">
      <div class="flex space-x-4">
        <a href="<?= rtrim(BASE_URL, '/'); ?>/about.php"   class="hover:text-primary transition-colors">About Us</a>
        <a href="<?= rtrim(BASE_URL, '/'); ?>/contact.php" class="hover:text-primary transition-colors">Contact</a>
        <a href="<?= rtrim(BASE_URL, '/'); ?>/privacy.php" class="hover:text-primary transition-colors">Privacy Policy</a>
      </div>
      <div class="flex space-x-3 items-center">
        <a href="#" aria-label="Facebook"  class="hover:text-primary transition-colors"><i data-lucide="facebook"  class="w-4 h-4"></i></a>
        <a href="#" aria-label="Twitter"   class="hover:text-primary transition-colors"><i data-lucide="twitter"   class="w-4 h-4"></i></a>
        <a href="#" aria-label="LinkedIn"  class="hover:text-primary transition-colors"><i data-lucide="linkedin"  class="w-4 h-4"></i></a>
        <a href="#" aria-label="Instagram" class="hover:text-primary transition-colors"><i data-lucide="instagram" class="w-4 h-4"></i></a>
        <?php if (class_exists('Auth') && Auth::isLoggedIn() && Auth::hasRole('administrator')): ?>
          <a href="<?= rtrim(ADMIN_BASE_URL, '/'); ?>/" class="ml-3 px-2 py-0.5 text-xs font-medium bg-accent hover:bg-accent-hover text-white rounded transition-colors">Admin Panel</a>
        <?php else: ?>
          <!-- <a href="<?= rtrim(ADMIN_BASE_URL, '/'); ?>/pages/login.php" class="ml-3 px-2 py-0.5 text-xs font-medium bg-secondary hover:bg-secondary-hover text-white rounded transition-colors">Admin Login</a> -->
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Main Navigation -->
  <header id="mainNav" class="bg-base-100/95 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-base-300/70 print:hidden">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16 md:h-20">
        <!-- Logo / Site Name -->
        <a href="<?= rtrim(BASE_URL, '/'); ?>/" class="flex items-center space-x-2 shrink-0">
          <!-- You can use an <img> tag here for a logo -->
          <!-- <img src="<?= rtrim(BASE_URL, '/'); ?>/assets/logo.png" alt="<?= esc_html(SITE_NAME); ?> Logo" class="h-8 w-auto"> -->
          <i data-lucide="cpu" class="w-8 h-8 text-primary"></i> <!-- Placeholder Icon -->
          <span class="font-display text-xl sm:text-2xl font-bold text-text hover:text-primary transition-colors"><?= esc_html(defined('SITE_NAME') ? SITE_NAME : 'dipug.com'); ?></span>
        </a>

        <!-- Desktop Menu -->
        <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
          <?php foreach ($main_nav_links as $link_item): ?>
            <?php
              // Construct URL: if page is 'home', link to BASE_URL, otherwise append page name.
              $link_page_slug = ($link_item['page'] === 'home') ? '' : $link_item['page'] . '.php';
              $link_url = rtrim(BASE_URL, '/') . '/' . $link_page_slug;
              $is_active = ($current_public_page === $link_item['page']);
            ?>
            <?php if (isset($link_item['is_mega_menu']) && $link_item['is_mega_menu']): ?>
              <div class="group relative">
                <a href="<?= $link_url; ?>"
                   aria-haspopup="true" aria-expanded="false"
                   class="mega-menu-trigger px-3 lg:px-4 py-2 rounded-md text-sm font-medium <?= $is_active ? 'nav-link-active bg-primary-light' : 'text-text-light hover:text-primary hover:bg-base-200'; ?> transition-all duration-150 ease-in-out flex items-center space-x-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-base-100 focus:ring-primary">
                  <i data-lucide="<?= $link_item['icon']; ?>" class="w-4 h-4 opacity-80"></i>
                  <span><?= esc_html($link_item['name']); ?></span>
                  <i data-lucide="chevron-down" class="w-4 h-4 ml-0.5 opacity-70 group-hover:rotate-180 transition-transform duration-200"></i>
                </a>
                <div class="mega-menu-container absolute top-full left-1/2 transform -translate-x-1/2 mt-1 w-max min-w-[580px] max-w-3xl pt-1 animate-slide-down" style="animation-duration: 0.2s;">
                  <div class="bg-base-100 shadow-2xl rounded-b-lg border-t-2 border-primary p-5 grid grid-cols-2 gap-x-6 gap-y-3">
                    <?php foreach ($services_menu_items_header as $s_item): ?>
                       <?php $service_link_url = rtrim(BASE_URL, '/') . '/' . $s_item['page'] . '.php'; ?>
                      <a href="<?= $service_link_url; ?>"
                         class="p-3 rounded-lg flex items-start space-x-3.5 transition-colors duration-150 ease-in-out hover:bg-base-200 group/service">
                        <i data-lucide="<?= $s_item['icon']; ?>" class="w-7 h-7 text-primary opacity-80 mt-0.5 shrink-0"></i>
                        <div>
                          <span class="font-semibold text-text group-hover/service:text-primary block text-base leading-tight"><?= esc_html($s_item['name']); ?></span>
                          <span class="text-xs text-text-lighter block leading-snug mt-0.5"><?= esc_html($s_item['desc'] ?? ''); ?></span>
                        </div>
                      </a>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <a href="<?= $link_url; ?>"
                 class="px-3 lg:px-4 py-2 rounded-md text-sm font-medium <?= $is_active ? 'nav-link-active bg-primary-light' : 'text-text-light hover:text-primary hover:bg-base-200'; ?> transition-all duration-150 ease-in-out flex items-center space-x-1.5">
                <i data-lucide="<?= $link_item['icon']; ?>" class="w-4 h-4 opacity-80"></i>
                <span><?= esc_html($link_item['name']); ?></span>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>

          <?php if (class_exists('Auth') && Auth::isLoggedIn() && Auth::hasRole('administrator')): ?>
            <a href="<?= rtrim(ADMIN_BASE_URL, '/'); ?>/" class="px-3 lg:px-4 py-2 rounded-md text-sm font-medium text-text-light hover:text-primary hover:bg-base-200 transition-colors flex items-center space-x-1.5">
              <i data-lucide="settings-2" class="w-4 h-4 opacity-80"></i><span>Admin</span>
            </a>
            <a href="<?= rtrim(ADMIN_BASE_URL, '/'); ?>/logout.php" class="ml-1 lg:ml-2 px-3 py-1.5 rounded-md text-sm font-medium bg-red-600/10 hover:bg-red-600/20 text-red-700 hover:text-red-800 transition-colors flex items-center space-x-1.5">
              <i data-lucide="log-out" class="w-4 h-4"></i><span>Sign Out</span>
            </a>
          <?php endif; ?>
        </nav>

        <!-- Mobile Menu Button -->
        <div class="md:hidden flex items-center">
          <button id="mobileMenuButton" aria-label="Open Menu" aria-expanded="false" aria-controls="mobileMenu"
                  class="text-text-light hover:text-primary focus:outline-none p-2 rounded-md hover:bg-base-200">
            <i id="mobileMenuIconOpen" data-lucide="menu" class="w-7 h-7 block"></i>
            <i id="mobileMenuIconClose" data-lucide="x" class="w-7 h-7 hidden"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu Panel -->
    <div id="mobileMenu" class="mobile-menu hidden md:hidden bg-base-100 border-t border-base-300/70 absolute w-full shadow-lg left-0">
      <nav class="px-2 pt-2 pb-4 space-y-1 sm:px-3">
        <?php foreach ($main_nav_links as $link_item): ?>
          <?php
            $link_page_slug_mobile = ($link_item['page'] === 'home') ? '' : $link_item['page'] . '.php';
            $link_url_mobile = rtrim(BASE_URL, '/') . '/' . $link_page_slug_mobile;
            $is_active_mobile = ($current_public_page === $link_item['page']);
          ?>
          <a href="<?= $link_url_mobile; ?>"
             class="block px-3 py-3 rounded-md text-base font-medium <?= $is_active_mobile ? 'text-primary bg-primary-light' : 'text-text-light hover:text-primary hover:bg-base-200'; ?> transition-colors duration-150 ease-in-out flex items-center space-x-2.5">
            <i data-lucide="<?= $link_item['icon']; ?>" class="w-5 h-5 opacity-90"></i>
            <span><?= esc_html($link_item['name']); ?></span>
          </a>
          <?php if (isset($link_item['is_mega_menu']) && $link_item['is_mega_menu']): ?>
            <div class="pl-6 space-y-1 border-l-2 border-primary-light/50 ml-2.5 mb-2">
              <?php foreach ($services_menu_items_header as $s_item): ?>
                 <?php $service_link_url_mobile = rtrim(BASE_URL, '/') . '/' . $s_item['page'] . '.php'; ?>
                <a href="<?= $service_link_url_mobile; ?>"
                   class="block px-3 py-2.5 rounded-md text-sm font-medium text-text-light hover:text-primary hover:bg-base-200 transition-colors duration-150 ease-in-out flex items-center space-x-2">
                  <i data-lucide="<?= $s_item['icon']; ?>" class="w-4 h-4 opacity-80"></i>
                  <span><?= esc_html($s_item['name']); ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>

        <div class="border-t border-base-300 pt-4 mt-4 space-y-2">
          <?php if (class_exists('Auth') && Auth::isLoggedIn() && Auth::hasRole('administrator')): ?>
            <a href="<?= rtrim(ADMIN_BASE_URL, '/'); ?>/"
               class="block px-3 py-3 rounded-md text-base font-medium text-text-light hover:text-primary hover:bg-base-200 transition-colors">Admin Dashboard</a>
            <a href="<?= rtrim(ADMIN_BASE_URL, '/'); ?>/logout.php"
               class="block w-full text-left px-3 py-3 rounded-md text-base font-medium bg-red-600/10 hover:bg-red-600/20 text-red-700 hover:text-red-800 transition-colors">
              Sign Out
            </a>
          <?php else: ?>
            <!-- <a href="<?= rtrim(ADMIN_BASE_URL, '/'); ?>/pages/login.php"
               class="block px-3 py-3 rounded-md text-base font-medium text-text-light hover:text-primary hover:bg-base-200 transition-colors">Admin Login</a> -->
          <?php endif; ?>
        </div>
      </nav>
    </div>
  </header>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Lucide Icons
      lucide.createIcons();

      // Mobile Menu Toggle
      const mobileMenuButton = document.getElementById('mobileMenuButton');
      const mobileMenu = document.getElementById('mobileMenu');
      const mobileMenuIconOpen = document.getElementById('mobileMenuIconOpen');
      const mobileMenuIconClose = document.getElementById('mobileMenuIconClose');

      if (mobileMenuButton && mobileMenu && mobileMenuIconOpen && mobileMenuIconClose) {
        mobileMenuButton.addEventListener('click', function () {
          const isOpen = mobileMenu.classList.toggle('open');
          mobileMenu.classList.toggle('hidden'); // Toggle hidden class for actual display
          mobileMenuIconOpen.classList.toggle('hidden', isOpen);
          mobileMenuIconClose.classList.toggle('hidden', !isOpen);
          mobileMenuButton.setAttribute('aria-expanded', isOpen);
        });
      }

      // Optional: Close mobile menu when a link is clicked (for SPA-like behavior or if desired)
      // mobileMenu.querySelectorAll('a').forEach(link => {
      //   link.addEventListener('click', () => {
      //     if (mobileMenu.classList.contains('open')) {
      //       mobileMenu.classList.remove('open');
      //       mobileMenu.classList.add('hidden');
      //       mobileMenuIconOpen.classList.remove('hidden');
      //       mobileMenuIconClose.classList.add('hidden');
      //       mobileMenuButton.setAttribute('aria-expanded', 'false');
      //     }
      //   });
      // });

      // Sticky Navigation (optional, if you want to change style on scroll)
      // const mainNav = document.getElementById('mainNav');
      // if (mainNav) {
      //   window.addEventListener('scroll', function() {
      //     if (window.scrollY > 50) { // Adjust scroll threshold
      //       mainNav.classList.add('scrolled'); // Add a class to style the scrolled nav
      //     } else {
      //       mainNav.classList.remove('scrolled');
      //     }
      //   });
      // }

    });
  </script>

  <main class="flex-grow"> <!-- This main tag will be closed in a footer.php -->
    <!-- Page specific content starts here -->
