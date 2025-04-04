<?php
// footer.php
?>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Script pour la gestion du menu actif
        $(document).ready(function() {
            const currentPath = window.location.pathname;
            $('.sidebar-menu-item').each(function() {
                const href = $(this).attr('href');
                if (currentPath.includes(href) && href !== '../index.php') {
                    $(this).addClass('active');
                }
            });
        });
    </script>
</body>
</html>