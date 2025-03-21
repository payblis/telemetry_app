    </main>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <span class="text-muted">© <?php echo date('Y'); ?> Télémétrie IA</span>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <span class="text-muted">Version 1.0</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (si nécessaire) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Animation des cartes au chargement
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.fade-in').forEach(function(element, index) {
            setTimeout(function() {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
    </script>

    <!-- jQuery -->
    <script src="vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FastClick -->
    <script src="vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="vendors/nprogress/nprogress.js"></script>
    <!-- Chart.js -->
    <script src="vendors/Chart.js/dist/Chart.min.js"></script>
    <!-- jQuery Sparklines -->
    <script src="vendors/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
    <!-- Flot -->
    <script src="vendors/Flot/jquery.flot.js"></script>
    <script src="vendors/Flot/jquery.flot.time.js"></script>
    <script src="vendors/Flot/jquery.flot.resize.js"></script>
    <!-- DateJS -->
    <script src="vendors/DateJS/build/date.js"></script>
    <!-- Custom Theme Scripts -->
    <script src="js/custom.min.js"></script>

    <script>
    // Initialisation des composants
    $(document).ready(function() {
        // Gestion des menus déroulants
        $('.dropdown-toggle').dropdown();

        // Gestion des tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Animation des cartes
        $('.fade-in').each(function(index) {
            $(this).delay(100 * index).animate({opacity: 1}, 500);
        });

        // Gestion du menu mobile
        $('#menu_toggle').on('click', function() {
            if ($('body').hasClass('nav-md')) {
                $('body').removeClass('nav-md').addClass('nav-sm');
            } else {
                $('body').removeClass('nav-sm').addClass('nav-md');
            }
        });

        // Notifications temps réel (à implémenter)
        function checkNotifications() {
            $.get('index.php?route=notifications/check', function(data) {
                if (data.count > 0) {
                    $('.info-number .badge').text(data.count).show();
                }
            });
        }
        // setInterval(checkNotifications, 60000); // Vérification toutes les minutes
    });
    </script>

    <?php if (isset($pageSpecificScripts)): ?>
        <?php foreach ($pageSpecificScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html> 