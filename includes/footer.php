            </div>
        </main>
    </div>
    
    <?php
    /**
     * Authors:
     * - Joshane Beecher (2304845)
     * - Abbygayle Higgins (2106327)
     * - Serena Morris (2208659)
     * - Jahzeal Simms (2202446)
     */
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo BASE_URL . $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

