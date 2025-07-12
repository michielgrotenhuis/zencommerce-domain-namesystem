<?php
/**
 * Domain Landing Page - Page Content Section
 * 
 * @package YourSite
 * @since 1.0.0
 */
?>

<!-- Page Content Section (if there's content in the editor) -->
<section class="py-20 bg-white dark:bg-gray-800">
    <div class="layout-container">
        <div class="max-w-4xl mx-auto prose prose-lg dark:prose-invert">
            <?php the_content(); ?>
        </div>
    </div>
</section>