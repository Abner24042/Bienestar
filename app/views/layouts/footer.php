</main>

    <!-- Scripts adicionales -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script defer src="<?php echo asset("js/{$js}"); ?>?v=<?php echo filemtime(PUBLIC_PATH . '/assets/js/' . $js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>