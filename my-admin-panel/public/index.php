<?php
// public/index.php - Public Home Page

// This variable can be used by header.php to identify the current page for active nav links etc.
$page = 'home';
$page_title = 'Homepage - ' . (defined('SITE_NAME') ? SITE_NAME : 'Welcome');
$meta_description = 'This is the homepage of our awesome website built with PHP and Tailwind CSS.';

// Ensure config is loaded. This should ideally be done by a central bootstrap file if not directly.
// For now, header.php also tries to load it.
if (file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
} else {
    // Define fallbacks if config is missing, though this indicates a setup issue.
    defined('SITE_NAME') or define('SITE_NAME', 'dipug.com');
    // Fallback for BASE_URL if config.php didn't load and define it
    if (!defined('BASE_URL')) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        // If SCRIPT_NAME is /public/index.php, dirname is /public. We want the parent.
        // This logic is simplified and might need adjustment depending on actual server structure.
        $base_path_final = preg_replace('/\/public$/', '', $base_path);
        define('BASE_URL', rtrim($protocol . $host . $base_path_final, '/'));
    }
    if (!function_exists('esc_html')) {
        function esc_html(string $string): string {
            return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        }
    }
}

// Include the public header
require_once __DIR__ . '/includes/header.php';
?>

   <!-- Hero Section Example -->
   <section class="bg-gradient-to-r from-primary/80 via-primary/70 to-secondary/60 text-white py-20 md:py-32 animate-fade-in-up">
       <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
           <h1 class="text-4xl md:text-6xl font-display font-bold mb-6">
               Welcome to <?= esc_html(defined('SITE_NAME') ? SITE_NAME : 'dipug.com'); ?>!
           </h1>
           <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto text-primary-light/90">
               Discover amazing services and content. We are dedicated to providing the best solutions.
           </p>
           <a href="<?= rtrim(BASE_URL, '/'); ?>/services.php"
              class="bg-accent hover:bg-accent-hover text-white font-semibold py-3 px-8 rounded-lg text-lg transition-transform transform hover:scale-105 inline-block">
               Explore Services
           </a>
       </div>
   </section>

   <!-- Features Section Example -->
   <section class="py-16 md:py-24 bg-base-100">
       <div class="container mx-auto px-4 sm:px-6 lg:px-8">
           <div class="text-center mb-12">
               <h2 class="text-3xl md:text-4xl font-display font-bold text-text">Why Choose Us?</h2>
               <p class="text-text-light mt-2 max-w-xl mx-auto">
                   We offer cutting-edge solutions tailored to your needs, backed by expert support.
               </p>
           </div>
           <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12">
               <div class="bg-background-light p-8 rounded-xl shadow-lg text-center hover:shadow-xl transition-shadow animate-fade-in-up" style="animation-delay: 0.1s;">
                   <div class="flex justify-center mb-4">
                       <i data-lucide="zap" class="w-12 h-12 text-primary"></i>
                   </div>
                   <h3 class="text-xl font-display font-semibold text-text mb-2">Innovative Solutions</h3>
                   <p class="text-text-lighter">
                       Our team constantly explores new technologies to deliver innovative and efficient results.
                   </p>
               </div>
               <div class="bg-background-light p-8 rounded-xl shadow-lg text-center hover:shadow-xl transition-shadow animate-fade-in-up" style="animation-delay: 0.2s;">
                   <div class="flex justify-center mb-4">
                       <i data-lucide="users" class="w-12 h-12 text-primary"></i>
                   </div>
                   <h3 class="text-xl font-display font-semibold text-text mb-2">Customer Focused</h3>
                   <p class="text-text-lighter">
                       We prioritize your needs and work closely with you to achieve your goals.
                   </p>
               </div>
               <div class="bg-background-light p-8 rounded-xl shadow-lg text-center hover:shadow-xl transition-shadow animate-fade-in-up" style="animation-delay: 0.3s;">
                   <div class="flex justify-center mb-4">
                       <i data-lucide="shield-check" class="w-12 h-12 text-primary"></i>
                   </div>
                   <h3 class="text-xl font-display font-semibold text-text mb-2">Reliable & Secure</h3>
                   <p class="text-text-lighter">
                       Trust in our robust and secure platforms to keep your data safe and operations smooth.
                   </p>
               </div>
           </div>
       </div>
   </section>

   <!-- Call to Action Example -->
    <section class="py-16 md:py-24 bg-base-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-display font-bold text-text mb-6">
                Ready to Get Started?
            </h2>
            <p class="text-text-light mb-8 max-w-xl mx-auto">
                Contact us today to learn more about how we can help your business succeed.
            </p>
            <a href="<?= rtrim(BASE_URL, '/'); ?>/contact.php"
               class="bg-primary hover:bg-primary-hover text-white font-semibold py-3 px-8 rounded-lg text-lg transition-transform transform hover:scale-105 inline-block">
                Contact Us
            </a>
        </div>
    </section>

   <script>
     // Re-initialize Lucide icons if they were added dynamically or if needed after page load.
     // Header already calls createIcons(), so this might only be needed for icons added by JS later.
     // lucide.createIcons();
   </script>

   <?php
   // Include the public footer
   require_once __DIR__ . '/includes/footer.php';
   ?>
