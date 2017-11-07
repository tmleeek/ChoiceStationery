<?php

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2
define( 'DISALLOW_FILE_EDIT', true ); // Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
define( 'FORCE_SSL_ADMIN', true ); // Force SSL for Dashboard - Security > Settings > Secure Socket Layers (SSL) > SSL for Dashboard
// END iThemes Security - Do not modify or remove this line

/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

//define('WP_HOME','https://www.choicestationery.com/wp/');
//define('WP_SITEURL','https://www.choicestationery.com/wp/');

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'choicesa_choicestationerycom');

/** MySQL database username */
define('DB_USER', 'choicesa_choiceQ');

/** MySQL database password */
define('DB_PASSWORD', 'RzHK$X+9RZu;n8>*');

/** MySQL hostname */
define('DB_HOST', '185.53.174.174');

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
define('AUTH_KEY',         'gbVg!*>C`._@lgqG//fTzROw7ImW!cu!NP/B)%Cp84fZ0L)&PIEJVN^yzRObf#qU');
define('SECURE_AUTH_KEY',  'h,:=8a+b T.}bAuR-EH1pf;03OI#/baK^;W2(+Ys|{)P_5|jZtI4Z`t$E3I{h-hM');
define('LOGGED_IN_KEY',    'e[UcDs3X6rdrGO]O#,]wNb,]xt9NBK+}+sl/B;G!nFj<u`89Rky I;?32SX ik~X');
define('NONCE_KEY',        '=G0^-AcUhz9**s^AmGw3tDuMjLNg8<G%d96I)V;~nF6c+gR;^8LN,}I5%e||7ZV0');
define('AUTH_SALT',        'cZijcJUP:%zLEw{d~WneXNb+Lq$n)(4F.$hj*q&<j*_N&GEdxG_c|BY7SXNHk_Op');
define('SECURE_AUTH_SALT', ';zR>YrDbr%/TT#To}N^h6ow-F39xQEm~d#=5aT8X|IF3].#OR-Bk0(a]&vKjX#.S');
define('LOGGED_IN_SALT',   '^h6(M*I$nrsKa)b+8o6~~oxmDe#<7}qCv+J-hP_,xQuW%!@0lC%CyYE1Zwk_iN;7');
define('NONCE_SALT',       ' DR4-riaN5Z/8h!uk9IV*1>3f+ jLc*V-;yD(aD|x_5b)A {``d%!bT5gC`fbrd#');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
