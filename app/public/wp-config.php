<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'HoXwjHNH+d0G2ga8uVvk/MMdT0ZJuUZLnRCrv3ArJKBN7cDfgy7ZO11GmN2T7IGuLVfa+GloOPKeNK13AInTpw==');
define('SECURE_AUTH_KEY',  'SY2SKlI4B8vtDwt85LQKXudlWkTmuO6JcJkMYFzYGTFpCFzinxrFScumACRwafa81ado1iTLQ3cUzdyfb2nGPg==');
define('LOGGED_IN_KEY',    'H/Uh3L8WsGJs5G1lsIc8l0R0QYVlqiV8POMZ2kZ1W3FRFZndGOqbygy0sXHmFOZt6iYgPTiftougREEtdlMk+A==');
define('NONCE_KEY',        '79L+WZCJU1xrxkkuXGy1r/PF0wzK5tzozLCnq8ETVYe1mGSErp5qh9+ERdiXwqNrooiCHnubaUq4NNDvGlOH9A==');
define('AUTH_SALT',        'mo6B+tfBG3YGIFQfHl6rH0Z+MdUgDimp4TLU2uYzblDQnUZK+bSkzKi69d3w1qE74Mzi4F3GaJWc5xAWi19tQg==');
define('SECURE_AUTH_SALT', '4LZN8SESjoDZ70RxKUgCV4wJID6nHWkKSOUmwLmhuSNE7aFvyYIDij77Qwdy8mRpWn9DoB4ctsqIV4i9XLzwOg==');
define('LOGGED_IN_SALT',   'xY66TD8o1uzvJfbTgNwMHe7W+wfac8l4/samFcnYm4PNr7DEAAaPn0+z9FboyjSdeQGhzLyPKdUnFQJyC1OFtg==');
define('NONCE_SALT',       'rWXaQkXXmJ8jER1PenJtnUExalS3OOZ7fIzhhan2bVdDxigPw+aUgOfyxlgSWfBMVMNuGtM+3JIQqJlns6Ubag==');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
