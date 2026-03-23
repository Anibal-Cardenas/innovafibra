    </main>
    
    <!-- Footer -->
    <footer class="text-center text-muted py-4 mt-auto border-top">
        <div class="container">
            <p class="mb-0">
                &copy; <?= date('Y') ?> <?= APP_NAME ?> - Versión <?= APP_VERSION ?>
            </p>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery removed from footer (loaded in header to ensure availability for view inline scripts) -->
    
    <!-- Chart.js (para gráficos en dashboard) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= JS_URL ?>/app.js"></script>
</body>
</html>
