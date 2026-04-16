</main>

    <!-- Form memory (borradores) -->
    <script defer src="<?php echo asset('js/form-memory.js'); ?>?v=<?php echo filemtime(PUBLIC_PATH . '/assets/js/form-memory.js'); ?>"></script>

    <!-- Scripts adicionales -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script defer src="<?php echo asset("js/{$js}"); ?>?v=<?php echo filemtime(PUBLIC_PATH . '/assets/js/' . $js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>