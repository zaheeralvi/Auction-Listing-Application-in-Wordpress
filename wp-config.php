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
define('DB_NAME', 'ccjk');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'X.IJj_kv$Y)R:/k@uqI?ccTY{K$RswWFQDGN/?%0-3;71sRf:^sgE>uF|{}nD{P,');
define('SECURE_AUTH_KEY',  '+EvhnASn2kCh>YpxpDuFVy{`)#$vZdd!M <98l;>.0@`Vxj-J0{Vze+p=y$01[CR');
define('LOGGED_IN_KEY',    'LSnXTYEe?wlvv*u(Xw&3ACR_R.!Ao7FH6^N:bht{C!~;h]fo,0<]J4hBU?&3I=sT');
define('NONCE_KEY',        'zSTX=jAV1roL2ONp&a>Vz^<=tJmS1RWI13ynSzDnIdQ$3neLZ{]5Shu)T:~X?5?u');
define('AUTH_SALT',        'D&-AZ*nSYl}R5nsI]3R`RHic/dx@im~q.@qcC{Ho+zy.ptsz@)8-j>u-6)>q=Qs0');
define('SECURE_AUTH_SALT', 'X[gW1fZ$,O0O ]J4QrZs#9=[` #j$j(_aV}.!Uu2quQVcsOR<K(@[S*,],Y`QM0E');
define('LOGGED_IN_SALT',   ';i,AQBx/zugkALW09bm^eNR5oiEJ&#L~{}%}(qjeMgs>rliZK`L(yc1/3|>0V2Rm');
define('NONCE_SALT',       'Q}yM&n5zK5y`nu~5oL#9oOn7>Fp~1an|M;KI;/T~q?nYd[x]S`3t[;9%WvpPz:N)');

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
