<?php
// public/includes/footer.php
$current_year = date('Y');
$site_name_footer = defined('SITE_NAME') ? SITE_NAME : 'dipug.com';
?>
       <!-- Page specific content ends here (inside the main tag from header) -->
       </main> <!-- Closing main tag opened in public/includes/header.php -->

       <footer class="bg-base-200 border-t border-base-300 print:hidden">
           <div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8 text-center text-text-light text-sm">
               <p>&copy; <?php echo $current_year; ?> <?php echo htmlspecialchars($site_name_footer); ?>. All Rights Reserved.</p>
               <p class="mt-1">
                   <a href="<?= rtrim(BASE_URL, '/'); ?>/privacy.php" class="hover:text-primary">Privacy Policy</a> |
                   <a href="<?= rtrim(BASE_URL, '/'); ?>/terms.php" class="hover:text-primary">Terms of Service</a>
               </p>
           </div>
       </footer>

       <?php
       // Hook for any scripts to be loaded at the end of the body
       // if (function_exists('public_footer_scripts')) {
       //     public_footer_scripts();
       // }
       ?>
   </body>
   </html>
