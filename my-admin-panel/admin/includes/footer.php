<?php
// admin/includes/footer.php
$current_year = date('Y');
$site_name = defined('SITE_NAME') ? SITE_NAME : 'My Admin Panel';
?>
        <!-- Page content ends here -->
    </main>
    <footer class="bg-gray-200 text-gray-700 text-center p-4 mt-8">
        <p>&copy; <?php echo $current_year; ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.</p>
    </footer>
</body>
</html>
