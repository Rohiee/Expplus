<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'inncomen_wp231' );

/** Database username */
define( 'DB_USER', 'inncomen_wp231' );

/** Database password */
define( 'DB_PASSWORD', 'Vp!S56D1e-' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'cmrejuqzceu30jll8zuoann5yyomecuk1raxtg6ktnk0dse9f5ieudq0lmncqlay' );
define( 'SECURE_AUTH_KEY',  'jpuylqzhi52rvp26xevmqsleqyo0byqtwywjhtthd7mz86klltg0isutsawjzp3w' );
define( 'LOGGED_IN_KEY',    'qmom81vo4cosou04p2zq8phkaqyrsrce471bil9yaqmv77svyvcpjucbz4d3eddx' );
define( 'NONCE_KEY',        '1eklusf747nzqkckr1l6ggjlu4wtcnnqdlvkwcamcef4czdcpry766p8emleebzp' );
define( 'AUTH_SALT',        'kcbvak03m5lg3s3mlzxgdtdprx7vvcyjep3ujfjflfzyuttrpekos9d5rvi9aclm' );
define( 'SECURE_AUTH_SALT', 'dfqydbyzpxnyifov9uhkn5gb09yaxtuv4zagyojgrcx9liioec0c0bhs9bbxbona' );
define( 'LOGGED_IN_SALT',   'ji7n0ct4cppcfxppuyaqhoed47ifo7cgip2n82oxyeiegg8soiyageaesyu6oqpf' );
define( 'NONCE_SALT',       'mchhekmyq2ywxyjmywacwqneijrw91ppchy5tbvw6e9bmjqaudibdja8tpclbom8' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpd8_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
