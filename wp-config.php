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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
//define('WP_CACHE', true); //Added by WP-Cache Manager
define( 'WPCACHEHOME', '/home/hosting_users/thecooya/www/wp-content/plugins/wp-super-cache/' ); //Added by WP-Cache Manager
define('DB_NAME', 'thecooya');

/** MySQL database username */
define('DB_USER', 'thecooya');

/** MySQL database password */
define('DB_PASSWORD', 'the9ya**');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'b`_O,]%]e>E]VzvN)a?vfUX{0$!HAJ!d^95KJci7Wuk)j{<9m/#e}1/8OSztbDkO');
define('SECURE_AUTH_KEY',  'A!4vT96[dVw{zI;1h;7?k~oFc*M($x,,p7e9~,,l6#JZCo)4JKUaD=,S>l*^sR=}');
define('LOGGED_IN_KEY',    'Or#bYosw?r}n`*TG)9K~O7#>om,I{3u(|jG.&`?.hWoRr%6r(Y!Y8F|xJDB^c^5E');
define('NONCE_KEY',        'l,#A_cAR$=j@h-!K#A|Q?9X+ywRjkVm!U&5=Mg>K[p1<=a^m.{w @VxVq^qaMoV:');
define('AUTH_SALT',        'a#^^2A2u m|b+mN3(}86nYyC9DwCc>no!`nI[WC-8cK2R?.U<VG=6/t7B ,,OgUm');
define('SECURE_AUTH_SALT', '=a*r:/iLb5$NLuZ2aOB;(J!1^?V/o5o%Gh+q[Nt&H{HWMAT)uL[qV(=j@n( `RL^');
define('LOGGED_IN_SALT',   '+3;7}%~SPN43@9BaKG}Fdv$@6mYUWI9!]ZFb[j#?&XiUQkFUQC|k7zt4uLa#hkz9');
define('NONCE_SALT',       '}|Mf])<Pc@PdFl($oWv9I`Le^xHML@bbsx_P-rn$054 wn{eQ&CsFZas[.@TbOQ?');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
//define('WP_DEBUG', false);
define('WP_DEBUG', true);
define('WP_DEBUG_LOG',true);
define('WP_DEBUG_DISPLAY', false);
/* That's all, stop editing! Happy blogging. */

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
