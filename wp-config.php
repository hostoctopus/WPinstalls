<?php
/**
 * This config file is yours to hack on. It will work out of the box on Pantheon
 * but you may find there are a lot of neat tricks to be used here.
 *
 * See our documentation for more details:
 *
 * https://pantheon.io/docs
 */

/**
 * Local configuration information.
 *
 * If you are working in a local/desktop development environment and want to
 * keep your config separate, we recommend using a 'wp-config-local.php' file,
 * which you should also make sure you .gitignore.
 */
if (file_exists(dirname(__FILE__) . '/wp-config-local.php') && !isset($_ENV['PANTHEON_ENVIRONMENT'])):
  # IMPORTANT: ensure your local config does not include wp-settings.php
  require_once(dirname(__FILE__) . '/wp-config-local.php');

/**
 * Pantheon platform settings. Everything you need should already be set.
 */
else:
  if (isset($_ENV['PANTHEON_ENVIRONMENT'])):
    // ** MySQL settings - included in the Pantheon Environment ** //
    /** The name of the database for WordPress */
    define('DB_NAME', $_ENV['DB_NAME']);

    /** MySQL database username */
    define('DB_USER', $_ENV['DB_USER']);

    /** MySQL database password */
    define('DB_PASSWORD', $_ENV['DB_PASSWORD']);

    /** MySQL hostname; on Pantheon this includes a specific port number. */
    define('DB_HOST', $_ENV['DB_HOST'] . ':' . $_ENV['DB_PORT']);

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
     * Pantheon sets these values for you also. If you want to shuffle them you
     * must contact support: https://pantheon.io/docs/getting-support 
     *
     * @since 2.6.0
     */
    define('AUTH_KEY',         $_ENV['AUTH_KEY']);
    define('SECURE_AUTH_KEY',  $_ENV['SECURE_AUTH_KEY']);
    define('LOGGED_IN_KEY',    $_ENV['LOGGED_IN_KEY']);
    define('NONCE_KEY',        $_ENV['NONCE_KEY']);
    define('AUTH_SALT',        $_ENV['AUTH_SALT']);
    define('SECURE_AUTH_SALT', $_ENV['SECURE_AUTH_SALT']);
    define('LOGGED_IN_SALT',   $_ENV['LOGGED_IN_SALT']);
    define('NONCE_SALT',       $_ENV['NONCE_SALT']);
    /**#@-*/

    /** A couple extra tweaks to help things run well on Pantheon. **/
    if (isset($_SERVER['HTTP_HOST'])) {
        // HTTP is still the default scheme for now. 
        $scheme = 'http';
        // If we have detected that the end use is HTTPS, make sure we pass that
        // through here, so <img> tags and the like don't generate mixed-mode
        // content warnings.
        if (isset($_SERVER['HTTP_USER_AGENT_HTTPS']) && $_SERVER['HTTP_USER_AGENT_HTTPS'] == 'ON') {
            $scheme = 'https';
        }
        define('WP_HOME', $scheme . '://' . $_SERVER['HTTP_HOST']);
        define('WP_SITEURL', $scheme . '://' . $_SERVER['HTTP_HOST']);
    }
    // Don't show deprecations; useful under PHP 5.5
    error_reporting(E_ALL ^ E_DEPRECATED);
    // Force the use of a safe temp directory when in a container
    if ( defined( 'PANTHEON_BINDING' ) ):
        define( 'WP_TEMP_DIR', sprintf( '/srv/bindings/%s/tmp', PANTHEON_BINDING ) );
    endif;

    // FS writes aren't permitted in test or live, so we should let WordPress know to disable relevant UI
    if ( in_array( $_ENV['PANTHEON_ENVIRONMENT'], array( 'test', 'live' ) ) && ! defined( 'DISALLOW_FILE_MODS' ) ) :
        define( 'DISALLOW_FILE_MODS', true );
    endif;

  else:
    /**
     * This block will be executed if you have NO wp-config-local.php and you
     * are NOT running on Pantheon. Insert alternate config here if necessary.
     *
     * If you are only running on Pantheon, you can ignore this block.
     */
    define('DB_NAME',          'database_name');
    define('DB_USER',          'database_username');
    define('DB_PASSWORD',      'database_password');
    define('DB_HOST',          'database_host');
    define('DB_CHARSET',       'utf8');
    define('DB_COLLATE',       '');
    define('AUTH_KEY',         'put your unique phrase here');
    define('SECURE_AUTH_KEY',  'put your unique phrase here');
    define('LOGGED_IN_KEY',    'put your unique phrase here');
    define('NONCE_KEY',        'put your unique phrase here');
    define('AUTH_SALT',        'put your unique phrase here');
    define('SECURE_AUTH_SALT', 'put your unique phrase here');
    define('LOGGED_IN_SALT',   'put your unique phrase here');
    define('NONCE_SALT',       'put your unique phrase here');
  endif;
endif;

/** Standard wp-config.php stuff from here on down. **/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 *
 * You may want to examine $_ENV['PANTHEON_ENVIRONMENT'] to set this to be
 * "true" in dev, but false in test and live.
 */
/** The snippet below is to force HTTPS loading
 */
if ( ! defined( 'WP_DEBUG' ) ) {
    define('WP_DEBUG', false);
}

/* That's all, stop editing! Happy Pressing. */
if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
  // Redirect to https://$primary_domain in the Live environment
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
    /** Replace www.hostoctopus.com with your registered domain name */
    $primary_domain = 'www.hostoctopus.com';
  }
  else {
    // Redirect to HTTPS on every Pantheon environment.
    $primary_domain = $_SERVER['HTTP_HOST'];
  }

  if ($_SERVER['HTTP_HOST'] != $primary_domain
      || !isset($_SERVER['HTTP_X_SSL'])
      || $_SERVER['HTTP_X_SSL'] != 'ON' ) {

    # Name transaction "redirect" in New Relic for improved reporting (optional)
    if (extension_loaded('newrelic')) {
      newrelic_name_transaction("redirect");
    }

    header('HTTP/1.0 301 Moved Permanently');
    header('Location: https://'. $primary_domain . $_SERVER['REQUEST_URI']);
    exit();
  }
}
define('FS_METHOD', 'direct');
define( 'FS_CHMOD_DIR', ( 0755 & ~ umask() ) );
define( 'FS_CHMOD_FILE', ( 0755 & ~ umask() ) );
define('FTP_BASE', __DIR__);
define('FTP_CONTENT_DIR', __DIR__ .'/wp-content/');
define('FTP_PLUGIN_DIR', __DIR__ .'/wp-content/plugins/');
define('FTP_HOST', 'appserver.dev.294ef13b-41af-4b74-abf4-2ef875fdc8a7.drush.in');
define('FTP_USER', 'dev.294ef13b-41af-4b74-abf4-2ef875fdc8a7');
define('FTP_PASS', '9a:29:2a:00:75:19:d7:17:e6:26:5c:04:0b:1f:ff:ad');
define('FTP_SSL', true);



/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

# BEGIN iThemes Security - Do not modify or remove this line
# iThemes Security Config Details: 2
	# Enable HackRepair.com's blacklist feature - Security > Settings > Banned Users > Default Blacklist
	# Start HackRepair.com Blacklist
	# Start Abuse Agent Blocking
	if ($http_user_agent ~* "^Mozilla.*Indy"){return 403;}
	if ($http_user_agent ~* "^Mozilla.*NEWT"){return 403;}
	if ($http_user_agent ~* "^$"){return 403;}
	if ($http_user_agent ~* "^Maxthon$"){return 403;}
	if ($http_user_agent ~* "^SeaMonkey$"){return 403;}
	if ($http_user_agent ~* "^Acunetix"){return 403;}
	if ($http_user_agent ~* "^binlar"){return 403;}
	if ($http_user_agent ~* "^BlackWidow"){return 403;}
	if ($http_user_agent ~* "^Bolt 0"){return 403;}
	if ($http_user_agent ~* "^BOT for JCE"){return 403;}
	if ($http_user_agent ~* "^Bot mailto\:craftbot@yahoo\.com"){return 403;}
	if ($http_user_agent ~* "^casper"){return 403;}
	if ($http_user_agent ~* "^checkprivacy"){return 403;}
	if ($http_user_agent ~* "^ChinaClaw"){return 403;}
	if ($http_user_agent ~* "^clshttp"){return 403;}
	if ($http_user_agent ~* "^cmsworldmap"){return 403;}
	if ($http_user_agent ~* "^Custo"){return 403;}
	if ($http_user_agent ~* "^Default Browser 0"){return 403;}
	if ($http_user_agent ~* "^diavol"){return 403;}
	if ($http_user_agent ~* "^DIIbot"){return 403;}
	if ($http_user_agent ~* "^DISCo"){return 403;}
	if ($http_user_agent ~* "^dotbot"){return 403;}
	if ($http_user_agent ~* "^Download Demon"){return 403;}
	if ($http_user_agent ~* "^eCatch"){return 403;}
	if ($http_user_agent ~* "^EirGrabber"){return 403;}
	if ($http_user_agent ~* "^EmailCollector"){return 403;}
	if ($http_user_agent ~* "^EmailSiphon"){return 403;}
	if ($http_user_agent ~* "^EmailWolf"){return 403;}
	if ($http_user_agent ~* "^Express WebPictures"){return 403;}
	if ($http_user_agent ~* "^extract"){return 403;}
	if ($http_user_agent ~* "^ExtractorPro"){return 403;}
	if ($http_user_agent ~* "^EyeNetIE"){return 403;}
	if ($http_user_agent ~* "^feedfinder"){return 403;}
	if ($http_user_agent ~* "^FHscan"){return 403;}
	if ($http_user_agent ~* "^FlashGet"){return 403;}
	if ($http_user_agent ~* "^flicky"){return 403;}
	if ($http_user_agent ~* "^g00g1e"){return 403;}
	if ($http_user_agent ~* "^GetRight"){return 403;}
	if ($http_user_agent ~* "^GetWeb\!"){return 403;}
	if ($http_user_agent ~* "^Go\!Zilla"){return 403;}
	if ($http_user_agent ~* "^Go\-Ahead\-Got\-It"){return 403;}
	if ($http_user_agent ~* "^grab"){return 403;}
	if ($http_user_agent ~* "^GrabNet"){return 403;}
	if ($http_user_agent ~* "^Grafula"){return 403;}
	if ($http_user_agent ~* "^harvest"){return 403;}
	if ($http_user_agent ~* "^HMView"){return 403;}
	if ($http_user_agent ~* "^Image Stripper"){return 403;}
	if ($http_user_agent ~* "^Image Sucker"){return 403;}
	if ($http_user_agent ~* "^InterGET"){return 403;}
	if ($http_user_agent ~* "^Internet Ninja"){return 403;}
	if ($http_user_agent ~* "^InternetSeer\.com"){return 403;}
	if ($http_user_agent ~* "^jakarta"){return 403;}
	if ($http_user_agent ~* "^Java"){return 403;}
	if ($http_user_agent ~* "^JetCar"){return 403;}
	if ($http_user_agent ~* "^JOC Web Spider"){return 403;}
	if ($http_user_agent ~* "^kanagawa"){return 403;}
	if ($http_user_agent ~* "^kmccrew"){return 403;}
	if ($http_user_agent ~* "^larbin"){return 403;}
	if ($http_user_agent ~* "^LeechFTP"){return 403;}
	if ($http_user_agent ~* "^libwww"){return 403;}
	if ($http_user_agent ~* "^Mass Downloader"){return 403;}
	if ($http_user_agent ~* "^microsoft\.url"){return 403;}
	if ($http_user_agent ~* "^MIDown tool"){return 403;}
	if ($http_user_agent ~* "^miner"){return 403;}
	if ($http_user_agent ~* "^Mister PiX"){return 403;}
	if ($http_user_agent ~* "^MSFrontPage"){return 403;}
	if ($http_user_agent ~* "^Navroad"){return 403;}
	if ($http_user_agent ~* "^NearSite"){return 403;}
	if ($http_user_agent ~* "^Net Vampire"){return 403;}
	if ($http_user_agent ~* "^NetAnts"){return 403;}
	if ($http_user_agent ~* "^NetSpider"){return 403;}
	if ($http_user_agent ~* "^NetZIP"){return 403;}
	if ($http_user_agent ~* "^nutch"){return 403;}
	if ($http_user_agent ~* "^Octopus"){return 403;}
	if ($http_user_agent ~* "^Offline Explorer"){return 403;}
	if ($http_user_agent ~* "^Offline Navigator"){return 403;}
	if ($http_user_agent ~* "^PageGrabber"){return 403;}
	if ($http_user_agent ~* "^Papa Foto"){return 403;}
	if ($http_user_agent ~* "^pavuk"){return 403;}
	if ($http_user_agent ~* "^pcBrowser"){return 403;}
	if ($http_user_agent ~* "^PeoplePal"){return 403;}
	if ($http_user_agent ~* "^planetwork"){return 403;}
	if ($http_user_agent ~* "^psbot"){return 403;}
	if ($http_user_agent ~* "^purebot"){return 403;}
	if ($http_user_agent ~* "^pycurl"){return 403;}
	if ($http_user_agent ~* "^RealDownload"){return 403;}
	if ($http_user_agent ~* "^ReGet"){return 403;}
	if ($http_user_agent ~* "^Rippers 0"){return 403;}
	if ($http_user_agent ~* "^sitecheck\.internetseer\.com"){return 403;}
	if ($http_user_agent ~* "^SiteSnagger"){return 403;}
	if ($http_user_agent ~* "^skygrid"){return 403;}
	if ($http_user_agent ~* "^SmartDownload"){return 403;}
	if ($http_user_agent ~* "^sucker"){return 403;}
	if ($http_user_agent ~* "^SuperBot"){return 403;}
	if ($http_user_agent ~* "^SuperHTTP"){return 403;}
	if ($http_user_agent ~* "^Surfbot"){return 403;}
	if ($http_user_agent ~* "^tAkeOut"){return 403;}
	if ($http_user_agent ~* "^Teleport Pro"){return 403;}
	if ($http_user_agent ~* "^Toata dragostea mea pentru diavola"){return 403;}
	if ($http_user_agent ~* "^turnit"){return 403;}
	if ($http_user_agent ~* "^vikspider"){return 403;}
	if ($http_user_agent ~* "^VoidEYE"){return 403;}
	if ($http_user_agent ~* "^Web Image Collector"){return 403;}
	if ($http_user_agent ~* "^WebAuto"){return 403;}
	if ($http_user_agent ~* "^WebBandit"){return 403;}
	if ($http_user_agent ~* "^WebCopier"){return 403;}
	if ($http_user_agent ~* "^WebFetch"){return 403;}
	if ($http_user_agent ~* "^WebGo IS"){return 403;}
	if ($http_user_agent ~* "^WebLeacher"){return 403;}
	if ($http_user_agent ~* "^WebReaper"){return 403;}
	if ($http_user_agent ~* "^WebSauger"){return 403;}
	if ($http_user_agent ~* "^Website eXtractor"){return 403;}
	if ($http_user_agent ~* "^Website Quester"){return 403;}
	if ($http_user_agent ~* "^WebStripper"){return 403;}
	if ($http_user_agent ~* "^WebWhacker"){return 403;}
	if ($http_user_agent ~* "^WebZIP"){return 403;}
	if ($http_user_agent ~* "^Widow"){return 403;}
	if ($http_user_agent ~* "^WPScan"){return 403;}
	if ($http_user_agent ~* "^WWW\-Mechanize"){return 403;}
	if ($http_user_agent ~* "^WWWOFFLE"){return 403;}
	if ($http_user_agent ~* "^Xaldon WebSpider"){return 403;}
	if ($http_user_agent ~* "^Zeus"){return 403;}
	if ($http_user_agent ~* "^zmeu"){return 403;}
	if ($http_user_agent ~* "360Spider"){return 403;}
	if ($http_user_agent ~* "CazoodleBot"){return 403;}
	if ($http_user_agent ~* "discobot"){return 403;}
	if ($http_user_agent ~* "EasouSpider"){return 403;}
	if ($http_user_agent ~* "ecxi"){return 403;}
	if ($http_user_agent ~* "GT\:\:WWW"){return 403;}
	if ($http_user_agent ~* "heritrix"){return 403;}
	if ($http_user_agent ~* "HTTP\:\:Lite"){return 403;}
	if ($http_user_agent ~* "HTTrack"){return 403;}
	if ($http_user_agent ~* "ia_archiver"){return 403;}
	if ($http_user_agent ~* "id\-search"){return 403;}
	if ($http_user_agent ~* "IDBot"){return 403;}
	if ($http_user_agent ~* "Indy Library"){return 403;}
	if ($http_user_agent ~* "IRLbot"){return 403;}
	if ($http_user_agent ~* "ISC Systems iRc Search 2\.1"){return 403;}
	if ($http_user_agent ~* "LinksCrawler"){return 403;}
	if ($http_user_agent ~* "LinksManager\.com_bot"){return 403;}
	if ($http_user_agent ~* "linkwalker"){return 403;}
	if ($http_user_agent ~* "lwp\-trivial"){return 403;}
	if ($http_user_agent ~* "MFC_Tear_Sample"){return 403;}
	if ($http_user_agent ~* "Microsoft URL Control"){return 403;}
	if ($http_user_agent ~* "Missigua Locator"){return 403;}
	if ($http_user_agent ~* "MJ12bot"){return 403;}
	if ($http_user_agent ~* "panscient\.com"){return 403;}
	if ($http_user_agent ~* "PECL\:\:HTTP"){return 403;}
	if ($http_user_agent ~* "PHPCrawl"){return 403;}
	if ($http_user_agent ~* "PleaseCrawl"){return 403;}
	if ($http_user_agent ~* "SBIder"){return 403;}
	if ($http_user_agent ~* "SearchmetricsBot"){return 403;}
	if ($http_user_agent ~* "SeznamBot"){return 403;}
	if ($http_user_agent ~* "Snoopy"){return 403;}
	if ($http_user_agent ~* "Steeler"){return 403;}
	if ($http_user_agent ~* "URI\:\:Fetch"){return 403;}
	if ($http_user_agent ~* "urllib"){return 403;}
	if ($http_user_agent ~* "Web Sucker"){return 403;}
	if ($http_user_agent ~* "webalta"){return 403;}
	if ($http_user_agent ~* "WebCollage"){return 403;}
	if ($http_user_agent ~* "Wells Search II"){return 403;}
	if ($http_user_agent ~* "WEP Search"){return 403;}
	if ($http_user_agent ~* "XoviBot"){return 403;}
	if ($http_user_agent ~* "YisouSpider"){return 403;}
	if ($http_user_agent ~* "zermelo"){return 403;}
	if ($http_user_agent ~* "ZyBorg"){return 403;}
	# End Abuse Agent Blocking
	# Start Abuse HTTP Referrer Blocking
	if ($http_referer ~* "^https?://(?:[^/]+\.)?semalt\.com"){return 403;}
	if ($http_referer ~* "^https?://(?:[^/]+\.)?kambasoft\.com"){return 403;}
	if ($http_referer ~* "^https?://(?:[^/]+\.)?savetubevideo\.com"){return 403;}
	# End Abuse HTTP Referrer Blocking
	# End HackRepair.com Blacklist, http://pastebin.com/u/hackrepair

	# Protect System Files - Security > Settings > System Tweaks > System Files
	location = /wp-admin/install.php { deny all; }
	location = /nginx.conf { deny all; }
	location ~ /\.htaccess$ { deny all; }
	location ~ /readme\.html$ { deny all; }
	location ~ /readme\.txt$ { deny all; }
	location ~ /wp-config.php$ { deny all; }
	location ~ ^/wp-admin/includes/ { deny all; }
	location ~ ^/wp-includes/[^/]+\.php$ { deny all; }
	location ~ ^/wp-includes/js/tinymce/langs/.+\.php$ { deny all; }
	location ~ ^/wp-includes/theme-compat/ { deny all; }
# END iThemes Security - Do not modify or remove this line
