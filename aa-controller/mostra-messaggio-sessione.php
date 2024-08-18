<?php
if (isset($_SESSION['messaggio'])) {
	echo '<!-- messaggio -->';
	echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
	echo '<strong>AVVISO</strong> ' . $_SESSION['messaggio'];
	echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
	echo '</div>';

	unset($_SESSION['messaggio']);
}
