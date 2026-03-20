</main>

    <!-- Scripts adicionales -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script defer src="<?php echo asset("js/{$js}"); ?>?v=<?php echo filemtime(PUBLIC_PATH . '/assets/js/' . $js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Liquid blob indicator del sidebar -->
    <script defer src="<?php echo asset('js/sidebar-liquid.js'); ?>?v=<?php echo filemtime(PUBLIC_PATH . '/assets/js/sidebar-liquid.js'); ?>"></script>
</body>
</html>