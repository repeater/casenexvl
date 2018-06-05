<?php
add_filter( 'wp_mail_from', function($email) { if ($email == 'wordpress@') { return get_option( 'admin_email' ); } return $email;} );
