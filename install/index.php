<?php
/*
 * Evo-CMS Installer
 */
if (!version_compare(PHP_VERSION, '8.0.0', '>=')) {
	die('EVO-CMS requires PHP 8.0 or greater. Installed: ' . PHP_VERSION);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
date_default_timezone_set('UTC');

require_once '../includes/definitions.php';
require_once '../includes/Database/database.php';
require_once '../includes/Evo/Lang.php';
require_once '../includes/Evo/Translator.php';
require_once '../includes/functions.php';
require_once '../includes/app.php';
require_once ROOT_DIR . '/includes/widgets.php';

function post_e($key, $default = null) {
	if (isset($_POST[$key])) {
		return htmlentities($_POST[$key]);
	}
	return $default;
}

/**
 * Code pays ISO (2 lettres) pour Widgets::countryFlag() à partir du dossier langue.
 */
function install_locale_country_code(string $localeId): string {
	static $map = [
		'english' => 'GB',
		'en' => 'GB',
		'french' => 'FR',
		'fr' => 'FR',
		'german' => 'DE',
		'deutsch' => 'DE',
		'spanish' => 'ES',
		'italian' => 'IT',
		'portuguese' => 'PT',
		'brazilian' => 'BR',
		'dutch' => 'NL',
		'russian' => 'RU',
		'polish' => 'PL',
		'ukrainian' => 'UA',
		'czech' => 'CZ',
		'slovak' => 'SK',
		'hungarian' => 'HU',
		'romanian' => 'RO',
		'bulgarian' => 'BG',
		'greek' => 'GR',
		'turkish' => 'TR',
		'swedish' => 'SE',
		'norwegian' => 'NO',
		'danish' => 'DK',
		'finnish' => 'FI',
		'icelandic' => 'IS',
		'irish' => 'IE',
		'welsh' => 'GB',
		'scottish' => 'GB',
		'catalan' => 'ES',
		'basque' => 'ES',
		'japanese' => 'JP',
		'chinese' => 'CN',
		'korean' => 'KR',
		'vietnamese' => 'VN',
		'thai' => 'TH',
		'indonesian' => 'ID',
		'malay' => 'MY',
		'hindi' => 'IN',
		'bengali' => 'BD',
		'arabic' => 'SA',
		'hebrew' => 'IL',
		'persian' => 'IR',
		'urdu' => 'PK',
		'punjabi' => 'IN',
		'tamil' => 'IN',
		'filipino' => 'PH',
		'swahili' => 'KE',
		'afrikaans' => 'ZA',
		'latvian' => 'LV',
		'lithuanian' => 'LT',
		'estonian' => 'EE',
		'slovenian' => 'SI',
		'croatian' => 'HR',
		'serbian' => 'RS',
		'macedonian' => 'MK',
		'albanian' => 'AL',
		'georgian' => 'GE',
		'armenian' => 'AM',
		'azerbaijani' => 'AZ',
		'kazakh' => 'KZ',
		'uzbek' => 'UZ',
		'mongolian' => 'MN',
	];
	$lc = strtolower($localeId);
	if (isset($map[$lc])) {
		return $map[$lc];
	}
	if (strlen($localeId) === 2 && ctype_alpha($localeId)) {
		return strtoupper($localeId);
	}
	return '';
}

/**
 * URL de la même page avec ?lang= pour changer la langue (sans JavaScript).
 */
function install_lang_switch_url(string $locale): string {
	$uri = $_SERVER['REQUEST_URI'] ?? '/';
	$path = strtok($uri, '?');
	if ($path === false || $path === '') {
		$path = '/';
	}
	$params = $_GET;
	$params['lang'] = $locale;
	$query = http_build_query($params);
	return htmlspecialchars($path . ($query !== '' ? '?' . $query : ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Chemins internes des icônes Lucide (viewBox 0 0 24 24, stroke — lucide.dev).
 */
function install_lucide_paths(string $name): string {
	static $paths = [
		'languages' => '<path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="m22 22-5-5"/><path d="M12 12h3"/>',
		'earth' => '<path d="M21.54 15H17a2 2 0 0 0-2 2v4.54"/><path d="M7 3.34V5a3 3 0 0 0 3 3a2 2 0 0 1 2 2c0 1.1.9 2 2 2a2 2 0 0 0 2-2c0-1.1.9-2 2-2h3.17"/><path d="M11 21.95V18a2 2 0 0 0-2-2a2 2 0 0 1-2-2v-1a2 2 0 0 0-2-2H2.05"/><circle cx="12" cy="12" r="10"/>',
		'file-text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
		'clipboard-check' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>',
		'database' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/>',
		'sliders-horizontal' => '<line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="4" y2="5"/><line x1="8" x2="8" y1="12" y2="13"/><line x1="16" x2="16" y1="20" y2="21"/>',
		'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>',
		'flag' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" x2="4" y1="22" y2="15"/>',
		'facebook' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
		'globe' => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
		'github' => '<path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>',
		'circle-check' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
		'circle-x' => '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>',
		'check' => '<path d="M20 6 9 17l-5-5"/>',
		'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
		'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
		'link' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
		'circle' => '<circle cx="12" cy="12" r="10"/>',
	];
	return $paths[$name] ?? $paths['circle'];
}

/**
 * SVG Lucide complet (traits 2, arrondis).
 */
function install_lucide_icon(string $name, array $attrs = []): string {
	$inner = install_lucide_paths($name);
	$class = isset($attrs['class']) ? htmlspecialchars((string) $attrs['class'], ENT_QUOTES, 'UTF-8') : '';
	$w = isset($attrs['width']) ? (int) $attrs['width'] : 24;
	$h = isset($attrs['height']) ? (int) $attrs['height'] : 24;
	$sw = isset($attrs['stroke-width']) ? (string) $attrs['stroke-width'] : '2';
	$ariaHidden = array_key_exists('aria-hidden', $attrs) ? $attrs['aria-hidden'] : true;
	$aria = $ariaHidden ? ' aria-hidden="true"' : '';
	$cls = $class !== '' ? ' class="' . $class . '"' : '';
	return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . htmlspecialchars($sw, ENT_QUOTES, 'UTF-8') . '" stroke-linecap="round" stroke-linejoin="round"' . $cls . $aria . '>' . $inner . '</svg>';
}

$install_locale_ids = [];
foreach (glob(ROOT_DIR . '/includes/languages/*', GLOB_ONLYDIR) ?: [] as $_evo_lang_dir) {
	$install_locale_ids[] = basename($_evo_lang_dir);
}
$requested_locale = '';
if (!empty($_POST['language'])) {
	$requested_locale = trim((string) $_POST['language']);
} elseif (!empty($_GET['lang'])) {
	$requested_locale = trim((string) $_GET['lang']);
}
$install_locale = in_array($requested_locale, $install_locale_ids, true) ? $requested_locale : 'french';

Evo\Lang::setTranslator(
	new Evo\Translator($install_locale, ['english'], ROOT_DIR . '/includes/languages', 'install')
);

const STEP_LANGUAGE = 0;
const STEP_ACCEPT = 1;
const STEP_SYSCHECK = 2;
const STEP_DATABASE = 3;
const STEP_CONFIG   = 4;
const STEP_INSTALL  = 5;
const STEP_CLEANUP  = 6;
const STEP_ABORT    = -1;

$steps = [
	STEP_LANGUAGE => __('steps.language'),
	STEP_ACCEPT   => __('steps.acceptance'),
	STEP_SYSCHECK => __('steps.checks'),
	STEP_DATABASE => __('steps.database'),
	STEP_CONFIG   => __('steps.config'),
	STEP_INSTALL  => __('steps.install'),
	STEP_CLEANUP  => __('steps.finished'),
];

/** Icônes Lucide (install_lucide_paths) — une par étape du fil, hors états terminé / erreur. */
$install_step_dot_icons = [
	STEP_LANGUAGE => 'earth',
	STEP_ACCEPT   => 'file-text',
	STEP_SYSCHECK => 'clipboard-check',
	STEP_DATABASE => 'database',
	STEP_CONFIG   => 'sliders-horizontal',
	STEP_INSTALL  => 'download',
	STEP_CLEANUP  => 'flag',
];

$next_step = $cur_step = isset($_POST['step']) ? (int)$_POST['step'] : 0;
$from_step = isset($_POST['from_step']) ? (int)$_POST['from_step'] : 0;
$payload = isset($_POST['payload']) ? $_POST['payload'] : '';
$warning = $failed = '';

$available_drivers = Database::AvailableDrivers();
$db_types = array_intersect_key(['sqlite' => 'SQLite3', 'mysql' => 'MySQL'], array_flip($available_drivers));

// Fallback si aucun driver n'est détecté
if (empty($db_types)) {
    $db_types = ['sqlite' => 'SQLite3'];
}
$locales = Evo\Lang::getLocales(true, true);
/* Toutes les langues présentes dans includes/languages/ (liste exhaustive pour l’étape Langue) */
$install_language_options = [];
foreach ($install_locale_ids as $_lid) {
	$_idx = ROOT_DIR . '/includes/languages/' . $_lid . '/index.php';
	if (is_readable($_idx)) {
		$_pack = include $_idx;
		if (is_array($_pack)) {
			$install_language_options[$_lid] = $_pack['native_name'] ?? $_pack['name'] ?? $_lid;
		} else {
			$install_language_options[$_lid] = $_lid;
		}
	} else {
		$install_language_options[$_lid] = $_lid;
	}
}
asort($install_language_options, SORT_NATURAL | SORT_FLAG_CASE);
$html_lang = $install_locale === 'english' ? 'en' : ($install_locale === 'french' ? 'fr' : 'en');

if (file_exists('../config.php') && $cur_step != STEP_CLEANUP) {
	$warning = __('already_installed');
	$hide_nav = true;
	$cur_step = -1;
}

try {
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

switch($cur_step) {
	case STEP_LANGUAGE:
		unset($_SESSION['install_disclaimer_accepted']);
		$next_step = STEP_ACCEPT;
		break;

	case STEP_ACCEPT:
		$next_step = STEP_SYSCHECK;
		break;

	case STEP_SYSCHECK:
		if (empty($_POST['install_accept']) && empty($_SESSION['install_disclaimer_accepted'])) {
			$warning = __('acceptance.required');
			$cur_step = STEP_ACCEPT;
			break;
		}
		if (!empty($_POST['install_accept'])) {
			$_SESSION['install_disclaimer_accepted'] = true;
		}

		$checks[] = [__('checks.min_php', ['%version%' => '8.0']), $ok[] = version_compare(PHP_VERSION, '8.0.0', '>=')];
		$checks[] = [__('checks.writable_root'), $ok[] = is_writable('../')];
		$checks[] = [__('checks.writable_upload'), $ok[] = is_writable('../upload/')];
		$checks[] = [__('checks.pdo_available'), $ok[] = !empty($db_types)];
		$checks[] = [__('checks.sessions_available'), $ok[] = session_status() === PHP_SESSION_ACTIVE];

		/* Le cms peut fonctionner de façon limitée sans ces conditions: */
		$checks[] = [__('checks.ext_gd'), $ok[] = function_exists('imagecreatetruecolor')];
		$checks[] = [__('checks.ext_zip'), $ok[] = class_exists('ZipArchive')];

		// Toujours afficher l'étape, ne pas passer automatiquement
		$hide_nav = in_array(false, $ok);
		$next_step = STEP_DATABASE;
		break;

	case STEP_DATABASE:
		if (empty($_SESSION['install_disclaimer_accepted'])) {
			$warning = __('acceptance.required');
			$cur_step = STEP_ACCEPT;
			break;
		}

		// Forcer une valeur par défaut si db_type n'est pas défini
		if (!isset($_POST['db_type']) || empty($_POST['db_type'])) {
			if (isset($_POST['db_type_backup']) && !empty($_POST['db_type_backup'])) {
				$_POST['db_type'] = $_POST['db_type_backup'];
			} else {
				$_POST['db_type'] = 'sqlite';
			}
		}
		
		if (!isset($db_types[$_POST['db_type']])) {
			$warning = "Type de base de données invalide: " . $_POST['db_type'];
			break;
		}

		// Arrivée depuis l'étape précédente (ex. vérifications) : `step` pointe déjà vers cette étape
		// mais le POST ne contient pas encore les champs DB — ne pas valider ni tenter la connexion.
		if ($from_step !== STEP_DATABASE) {
			$next_step = STEP_DATABASE;
			break;
		}
		
		// Validation des champs requis
		$db_type = strtolower(trim($_POST['db_type']));
		if ($db_type === 'mysql') {
			if (empty($_POST['db_host'])) {
				$warning = "L'hôte MySQL est requis";
				break;
			}
			if (empty($_POST['db_user'])) {
				$warning = "L'utilisateur MySQL est requis";
				break;
			}
			if (empty($_POST['db_name'])) {
				$warning = "Le nom de la base de données MySQL est requis";
				break;
			}
		} else if ($db_type === 'sqlite') {
			if (empty($_POST['db_name'])) {
				$warning = "Le nom de la base de données SQLite est requis (valeur reçue: '" . (isset($_POST['db_name']) ? $_POST['db_name'] : 'NOT SET') . "')";
				break;
			}
		}

		$payload = [$_POST['db_host'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_name'], $_POST['db_prefix'], $_POST['db_type']];

		try {
			require '../includes/Database/db.'.strtolower($_POST['db_type']).'.php';
			$db_type = strtolower(trim($_POST['db_type']));
			
			if (empty($db_type)) {
				throw new Exception("Type de base de données non spécifié");
			}
			
			$db_file = '../includes/Database/db.' . $db_type . '.php';
			
			if (!file_exists($db_file)) {
				throw new Exception("Fichier de base de données non trouvé: " . $db_file);
			}
			
			require $db_file;
			
			Db::Connect($_POST['db_host'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_name'], $_POST['db_prefix']);

			if (Db::TableExists('users')) {
				$warning = __('database.not_empty');
			}
			$next_step = STEP_CONFIG;
		} catch (Exception $e) {
			$warning = "Erreur de connexion à la base de données: " . $e->getMessage();
		}
		break;

	case STEP_CONFIG:
		if (empty($_SESSION['install_disclaimer_accepted'])) {
			$warning = __('acceptance.required');
			$cur_step = STEP_ACCEPT;
			break;
		}

		if (isset($_POST['email'], $_POST['admin'], $_POST['admin_pass'], $_POST['url'], $_POST['name'], $_POST['payload'])) {
			try {
				if (!preg_match('#https?://.+#', $_POST['url']))
					$warning .= __('config.bad_url') . '<br>';
				if (!preg_match('#^.+@.+\..+$#', $_POST['email']))
					$warning .= __('config.bad_email') . '<br>';
				if (empty($_POST['admin']))
					$warning .= __('config.bad_username') . '<br>';
				if (empty($_POST['admin_pass']) || empty($_POST['admin_pass_confirm']))
					$warning .= __('config.bad_password1') . '<br>';
				elseif ($_POST['admin_pass_confirm'] !== $_POST['admin_pass'])
					$warning .= __('config.bad_password2') . '<br>';

				if ($warning) break;
			} catch (Exception $e) {
				$warning = "Erreur de validation: " . $e->getMessage();
				break;
			}

			$db = unserialize(base64_decode($_POST['payload']));
			$_POST['url'] = trim($_POST['url'], '/');
			try {
				require '../includes/Database/db.'.strtolower($db[5]).'.php';

				Db::Connect($db[0], $db[1], $db[2], $db[3], $db[4]);

				$cur_step = STEP_INSTALL;
				$hide_nav = true;

				$db_version = 1;

				Db::CreateTable('banlist', [
								'id' 				=> 'increment',
								'type' 				=> 'string|16',
								'rule' 				=> 'string|128',
								'reason' 			=> 'string',
								'created'			=> 'integer',
								'expires'			=> ['integer', 0],
				], false, true);
				Db::AddIndex('banlist', 'index', ['type', 'rule']);
				Db::AddIndex('banlist', 'index', ['expires']);



				Db::CreateTable('comments', [
								'id' 				=> 'increment',
								'page_id' 			=> 'integer',
								'user_id' 			=> 'integer',
								'message' 			=> 'text',
								'posted' 			=> 'integer',
								'poster_ip' 		=> 'string',
								'poster_name' 		=> ['string', null],
								'poster_email' 		=> ['string', null],
								'state' 			=> ['integer', 0],
				], false, true);



				Db::CreateTable('files', [
								'id' 				=> 'increment',
								'web_id'			=> 'string|8',
								'name' 				=> 'string|128',
								'caption' 			=> 'string',
								'description'       => ['text', null],
								'path' 				=> 'string|191',
								'thumbs' 			=> ['text', null],
								'type' 				=> 'string',
								'mime_type' 		=> 'string',
								'size' 				=> 'integer',
								'md5' 				=> 'string',
								'poster' 			=> 'integer',
								'posted' 			=> 'integer',
								'origin' 			=> ['string', null],
								'hits' 				=> ['integer', 0],
				], false, true);
				Db::AddIndex('files', 'index', ['web_id']);
				Db::AddIndex('files', 'index', ['path']);



				Db::CreateTable('files_rel', [
								'file_id' 			=> 'integer',
								'rel_id' 			=> 'integer',
								'rel_type' 			=> 'string|128',
				], false, true);
				Db::AddIndex('files_rel', 'unique', ['file_id', 'rel_id', 'rel_type']);



				Db::CreateTable('forums', [
								'id' 				=> 'increment',
								'cat' 				=> 'integer',
								'priority' 			=> 'integer',
								'name' 				=> 'string',
								'description' 		=> 'string',
								'icon' 				=> 'string',
								'num_topics' 		=> ['integer', 0],
								'num_posts' 		=> ['integer', 0],
								'last_topic_id' 	=> ['integer', null],
								'redirect' 			=> ['string', null],
				], false, true);



				Db::CreateTable('forums_cat', [
								'id' 				=> 'increment',
								'name' 				=> 'string',
								'priority' 			=> 'integer',
				], false, true);



				Db::CreateTable('forums_posts', [
								'id' 				=> 'increment',
								'topic_id' 			=> 'integer',
								'poster_id' 		=> 'integer',
								'poster' 			=> 'string',
								'poster_ip' 		=> 'string',
								'message' 			=> 'longtext',
								'posted' 			=> 'integer',
								'edited' 			=> ['integer', 0],
								'user_agent' 		=> 'string',
								'attached_files'	=> ['text', null],
				], false, true);
				Db::AddIndex('forums_posts', 'index', ['topic_id']);



				Db::CreateTable('forums_topics', [
								'id' 				=> 'increment',
								'forum_id' 			=> 'integer',
								'poster_id' 		=> 'integer',
								'poster' 			=> 'string',
								'subject' 			=> 'string',
								'first_post_id' 	=> 'integer',
								'first_post' 		=> 'integer',
								'last_post_id' 		=> 'integer',
								'last_post' 		=> 'integer',
								'last_poster' 		=> 'string',
								'last_poster_id'	=> 'integer',
								'num_posts' 		=> ['integer', 0],
								'num_views' 		=> ['integer', 0],
								'sticky' 			=> ['integer', 0],
								'closed' 			=> ['integer', 0],
								'redirect' 			=> ['string', null],
				], false, true);
				Db::AddIndex('forums_topics', 'index', ['forum_id']);



				Db::CreateTable('friends', [
								'id' 				=> 'increment',
								'u_id' 				=> 'integer',
								'f_id' 				=> 'integer',
								'state' 			=> ['integer', 0]
				], false, true);
				Db::AddIndex('friends', 'unique', ['u_id', 'f_id']);



				Db::CreateTable('groups', [
								'id' 				=> 'increment',
								'name' 				=> 'string',
								'role'	 			=> ['string', null],
								'internal'	 		=> ['string', null],
								'color' 			=> 'string',
								'priority' 			=> ['integer', 100]
				], false, true);



				Db::CreateTable('history', [
								'id' 				=> 'increment',
								'e_uid' 			=> 'integer',
								'a_uid' 			=> 'integer',
								'ip' 				=> 'string',
								'type' 				=> 'string',
								'timestamp'		 	=> 'integer',
								'event' 			=> 'text',
				], false, true);



				Db::CreateTable('mailbox', [
								'id' 				=> 'increment',
								'reply' 			=> 'integer',
								's_id' 				=> 'integer',
								'r_id' 				=> 'integer',
								'type' 				=> 'tinyint',
								'sujet' 			=> 'string',
								'message' 			=> 'text',
								'posted' 			=> 'integer',
								'viewed' 			=> ['integer', null],
								'deleted_rcv' 		=> ['integer', 0],
								'deleted_snd' 		=> ['integer', 0],
				], false, true);



				Db::CreateTable('menu', [
								'id' 				=> 'increment',
								'parent' 			=> 'integer',
								'priority' 			=> 'integer',
								'name' 				=> 'string',
								'icon' 				=> 'string',
								'link' 				=> 'string',
								'visibility'		=> ['integer', 0],
				], false, true);



				Db::CreateTable('newsletter', [
								'id' 				=> 'increment',
								'author' 			=> 'integer',
								'groups' 			=> 'string',
								'subject' 			=> 'string',
								'message' 			=> 'text',
								'date_sent'			=> 'integer',
								'mail_sent'			=> ['integer', 0],
								'mail_failed'		=> ['integer', 0],
				], false, true);



				Db::CreateTable('pages', [
								'page_id' 			=> 'increment',
								'type' 				=> 'string|64',
								'slug' 				=> 'string|128',
								'image' 			=> 'string',
								'redirect'			=> ['string', ''],
								'category'			=> ['string|128', ''],
								'pub_date' 			=> 'integer',
								'pub_rev' 			=> 'integer',
								'display_toc' 		=> 'tinyint',
								'allow_comments' 	=> 'tinyint',
								'revisions' 		=> 'integer',
								'comments' 			=> ['integer', 0],
								'views' 			=> ['integer', 0],
								'sticky' 			=> ['integer', 0],
				], false, true);
				Db::AddIndex('pages', 'index', ['type']);
				Db::AddIndex('pages', 'index', ['slug']);
				Db::AddIndex('pages', 'index', ['category']);
				Db::AddIndex('pages', 'index', ['sticky']);



				Db::CreateTable('pages_revs', [
								'id' 				=> 'increment',
								'page_id' 			=> 'integer',
								'revision' 			=> 'integer',
								'posted' 			=> 'integer',
								'author' 			=> 'integer',
								'status' 			=> 'string|64',
								'title' 			=> 'string',
								'slug' 				=> 'string|128',
								'content'	 		=> 'text',
								'format'			=> ['string|64', 'html'],
								'extra'				=> ['text', null],
								'attached_files'	=> ['text', null],
				], false, true);
				Db::AddIndex('pages_revs', 'index', ['page_id', 'revision']);
				Db::AddIndex('pages_revs', 'index', ['slug']);



				Db::CreateTable('permissions', [
								'name' 				=> 'string|128',
								'group_id' 			=> 'integer',
								'related_id' 		=> ['integer', -1],
								'value' 			=> 'integer',
				], false, true);
				Db::AddIndex('permissions', 'primary key', ['name', 'group_id', 'related_id']);
				Db::AddIndex('permissions', 'index', ['group_id']);


				Db::CreateTable('reports', [
								'id' 				=> 'increment',
								'user_id' 			=> 'integer',
								'type' 				=> 'string',
								'rel_id' 			=> 'integer',
								'reason' 			=> 'text',
								'reported' 			=> 'integer',
								'deleted' 			=> ['integer', 0],
								'user_ip' 			=> 'string',
				], false, true);



				Db::CreateTable('servers', [
								'id' 				=> 'increment',
								'type' 				=> 'string|32',
								'name' 				=> 'string|96',
								'address' 		    => 'string|255',
								'password' 		    => 'string|255',
								'status_code' 		=> ['integer', 0],
								'status_data' 		=> ['string', null],
								'status_time' 		=> ['integer', 0],
								'poll_interval' 	=> ['integer', 0],
								'additional_settings'=>'text',
				], false, true);



				Db::CreateTable('settings', [
								'name' 				=> ['string|128', null, Db::PRIMARY],
								'value' 			=> ['text', null],
								'default_value'		=> ['text', null],
				], false, true);



				Db::CreateTable('subscriptions', [
								'user_id' 			=> 'integer',
								'type' 				=> 'string|128',
								'rel_id' 			=> 'integer',
								'email' 			=> 'string',
				], false, true);
				Db::AddIndex('subscriptions', 'primary key', ['user_id', 'type', 'rel_id']);



				Db::CreateTable('users', [
								'id' 				=> 'increment',
								'group_id' 			=> 'integer',
								'username' 			=> 'string|128',
								'email' 			=> 'string|128',
								'password' 			=> 'string',
								'login_type'			=> ['string', 'normal'],
								'locked' 			=> ['integer', 0],
								'newsletter' 			=> ['integer', 1],
								'discuss' 			=> ['integer', 0],
								'registered' 			=> 'integer',
								'activity' 			=> ['integer', 0],
								'timezone' 			=> ['string', null],
								'login_key' 			=> ['string', null],
								'reset_key' 			=> ['string', null],
								'raf' 				=> ['string', null],
								'raf_token' 			=> ['string', null],
								'registration_ip'		=> ['string', null],
								'last_ip'			=> ['string', null],
								'last_user_agent'		=> ['string', null],
								'country' 			=> ['string', null],
								'avatar' 			=> ['string', null],
								'ingame' 			=> ['string', null],
								'website' 			=> ['string', null],
								'social' 			=> ['text'  , null],
								'about' 			=> ['text'  , null],
								'extra' 			=> ['text'  , null],
								'num_posts' 			=> ['integer', 0],
								'num_thanks'			=> ['integer', 0],
								'profile_views'			=> ['integer', 0],
				], false, true);
				Db::AddIndex('users', 'unique', ['username']);
				Db::AddIndex('users', 'unique', ['email']);

				// ========================================
				// SYSTÈME DE SAUVEGARDES EVO-CMS
				// ========================================
				// Cette table gère toutes les sauvegardes du système :
				// - Sauvegardes manuelles créées via l'interface admin
				// - Sauvegardes automatiques programmées
				// - Métadonnées complètes pour chaque sauvegarde
				// - Suivi des utilisateurs et des dates
				// - Vérification d'intégrité via checksums
				// ========================================
				Db::CreateTable('backups', [
					'id' 				=> 'increment',					// ID unique auto-incrémenté
					'filename' 			=> 'string|255',				// Nom du fichier de sauvegarde
					'type' 				=> 'string|32',					// Type: web, sql, full, config
					'size' 				=> 'integer',					// Taille du fichier en octets
					'compression_level'		=> ['integer', 6],			// Niveau de compression (0-9)
					'exclude_files'		=> ['text', null],				// Fichiers exclus (séparés par \n)
					'created_by'		=> 'integer',					// ID de l'utilisateur créateur
					'created_at'		=> 'integer',					// Timestamp de création
					'status'			=> ['string|32', 'completed'],	// Statut: completed, failed, in_progress
					'description'		=> ['text', null],				// Description optionnelle
					'file_path'			=> 'string|255',				// Chemin complet du fichier
					'checksum'			=> ['string|64', null],			// Checksum MD5 du fichier
				], false, true);
				
				// Index pour optimiser les requêtes
				Db::AddIndex('backups', 'index', ['type']);			// Recherche par type
				Db::AddIndex('backups', 'index', ['created_at']);		// Tri par date
				Db::AddIndex('backups', 'index', ['created_by']);		// Recherche par utilisateur
				Db::AddIndex('backups', 'index', ['status']);			// Filtrage par statut
				Db::AddIndex('backups', 'index', ['filename']);		// Recherche par nom de fichier

				Db::Insert('settings', [
					['name' => 'name', 'value' => post_e('name', '')],
					['name' => 'email', 'value' => post_e('email', '')],
					['name' => 'url', 'value' => post_e('url', '/')],
					['name' => 'language', 'value' => post_e('language', 'french')],
					['name' => 'cookie.name', 'value' => 'evo_'.random_hash(8)],
					['name' => 'database.version', 'value' => DATABASE_VERSION],
					['name' => 'install.version', 'value' => EVO_VERSION],
					['name' => 'install.time', 'value' => time()],
					
					// ========================================
					// PARAMÈTRES DE SAUVEGARDES AUTOMATIQUES
					// ========================================
					// Configuration du système de sauvegardes automatiques
					// Ces paramètres permettent de programmer des sauvegardes
					// récurrentes sans intervention manuelle
					// ========================================
					['name' => 'backup.auto.enabled', 'value' => '0'],					// Activation (0=désactivé, 1=activé)
					['name' => 'backup.auto.type', 'value' => 'full'],					// Type: web, sql, full, config
					['name' => 'backup.auto.frequency', 'value' => 'daily'],				// Fréquence: daily, weekly, monthly
					['name' => 'backup.auto.time', 'value' => '02:00'],					// Heure d'exécution (HH:MM)
					['name' => 'backup.auto.retention', 'value' => '30'],				// Rétention en jours
					['name' => 'backup.auto.compression', 'value' => '6'],				// Niveau compression (0-9)
					['name' => 'backup.auto.exclude', 'value' => '*.log,cache/*,temp/*,backups/*'],	// Fichiers à exclure
					['name' => 'backup.auto.last_run', 'value' => '0'],					// Timestamp dernière exécution
					['name' => 'backup.auto.next_run', 'value' => '0'],					// Timestamp prochaine exécution
					['name' => 'backup.auto.max_size', 'value' => '1073741824'],			// Taille max (1GB en octets)
					['name' => 'backup.auto.email_notifications', 'value' => '1'],		// Notifications email (0=non, 1=oui)
					['name' => 'backup.auto.email_on_success', 'value' => '0'],			// Email en cas de succès
					['name' => 'backup.auto.email_on_failure', 'value' => '1'],			// Email en cas d'échec
				]);

				Db::Insert('menu', [
					['parent' => 0, 'priority' => 0, 'name' => 'Navigation', 'icon' => '', 'link' => ''],
					['parent' => 1, 'priority' => 0, 'name' => 'Accueil', 'icon' => 'fas fa-home', 'link' => 'index'],
					['parent' => 1, 'priority' => 0, 'name' => 'Forums', 'icon' => 'fas fa-list-ul', 'link' => 'forums'],
					['parent' => 1, 'priority' => 0, 'name' => 'Membres', 'icon' => 'fas fa-users', 'link' => 'users'],
					['parent' => 1, 'priority' => 0, 'name' => 'Téléchargements', 'icon' => 'fas fa-download', 'link' => 'downloads'],
					['parent' => 1, 'priority' => 0, 'name' => 'Contact', 'icon' => 'fas fa-envelope', 'link' => 'contact'],
				]);

				Db::Insert('groups', [
					['id' => 1, 'name' => 'Administrateur', 'internal' => 'Administrator', 'role' => 'administrator', 'color' => '3', 'priority' => 1],
					['id' => 2, 'name' => 'Modérateur', 'internal' => 'Moderator', 'role' => 'moderator', 'color' => '2', 'priority' => 2],
					['id' => 3, 'name' => 'Membre', 'internal' => 'Member', 'role' => 'member', 'color' => '1', 'priority' => 3],
					['id' => 4, 'name' => 'Invité', 'internal' => 'Guest', 'role' => 'guest', 'color' => '0', 'priority' => 4],
				]);

				$groups = [
					'admin' => ['id' => 1],
					'mod'   => ['id' => 2],
					'user'  => ['id' => 3, 'ignore' => ['user.staff']],
					'guest' => ['id' => 4, 'force' => ['comment_send']],
				];

				// Définir les permissions par défaut si elles n'existent pas
				if (!isset($_permissions)) {
					$_permissions = [];
				}
				
				foreach($_permissions as $group => $sections) {
					foreach(array_filter($sections, 'is_array') as $section) {
						foreach(array_keys($section) as $priv) {
							$key = $group.'.'.$priv;
							foreach($groups as $g) {
								if ($g['id'] <= $groups[$group]['id'] && (empty($g['ignore']) || !in_array($key, $g['ignore']))) {
									$inserts[] = ['name' => $key, 'group_id' => $g['id'], 'value' => 1];
								}
							}
						}
					}
				}

				foreach($groups as $g) {
					if (!empty($g['force'])) {
						foreach($g['force'] as $perm) {
							$inserts[] = ['name' => $perm, 'group_id' => $g['id'], 'value' => 1];
						}
					}
				}

				if ($inserts) {
					Db::Insert('permissions', $inserts);
				}

				Db::Insert('users', [
					[
						'id' => 1,
						'username' => $_POST['admin'],
						'group_id' => 1,
						'password' => password_hash($_POST['admin_pass'], PASSWORD_DEFAULT),
						'email' => $_POST['email'],
						'locked' => 0,
						'registered' => time()
					],
					[
						'id' => 0,
						'username' => 'guest',
						'group_id' => 4,
						'password' => '',
						'email' => '',
						'locked' => 1,
						'registered' => time()
					],
				]);
				Db::Update('users', ['id' => 0], ['username' => 'guest']); // For MySQL


				foreach(glob('updates/*.php') as $migration) { // Applying incremental updates
					if ((include $migration) === false) {
						throw new exception('Migration ' . $migration . ' failed');
					}
				}

				$db = array_map('addslashes', $db);

				$config = "<?php\n".
							"\$db_host = '{$db[0]}'; \n".
							"\$db_user = '{$db[1]}'; \n".
							"\$db_pass = '{$db[2]}'; \n".
							"\$db_name = '{$db[3]}'; \n".
							"\$db_prefix = '{$db[4]}'; \n".
							"\$db_type = '{$db[5]}'; \n".
							"\n".
							"// Debug mode active les options de dévelopement.\n".
							"\$debug_mode = false; \n".
							"\n".
							"// Préserve les erreurs PHP dans un fichier log.\n".
							"\$error_log = false; \n".
							"\n".
							"// Safe mode permets de désactiver tous les plugins et SSL.\n".
							"\$safe_mode = false; \n";

				file_put_contents('../config.php', $config);

				$done = true;
			} catch (Exception $e) {
				$failed  = 'Erreur SQL: ' . $e->getMessage() . '<br>';
				$failed .= 'Requete: '. end(Db::$queries)['query'];
			}

			if (isset($_POST['report']) && EVO_REPORT_EMAIL) {
				$status = isset($done) ? 'Réussie' : 'Échouée:';
				$report = "Rapport d'installation du " . date('Y-m-d H:i:s') . ":\n\n".
						  "Status:      $status $failed\n".
						  "Database:    ". Db::DriverName() . ' ' . Db::ServerVersion() . "\n" .
						  "Version CMS: " . EVO_VERSION . " - " . EVO_BUILD . "\n" .
						  "Version PHP: " . PHP_VERSION . "\n" .
						  "Serveur Web: " . $_SERVER['SERVER_SOFTWARE'] . "\n" .
						  "\n" .
						  "URL du CMS:  " . $_POST['url'] . "\n" .
						  "Email admin: " . $_POST['email'] . "\n" .
						  "User Agent:  " . $_SERVER['HTTP_USER_AGENT'];

				@mail(EVO_REPORT_EMAIL, 'Rapport d\'installation', mb_convert_encoding($report, 'ISO-8859-1', 'UTF-8'));
			}
		}
		break;

	case STEP_CLEANUP:
		App::init();
		App::sessionStart(1);
		header('Location: ../admin');
		@rename(__DIR__, __DIR__.'.'.random_hash(8));
		exit;
}

} catch (Exception $e) {
	$warning = "Erreur lors de l'installation: " . $e->getMessage();
	$cur_step = STEP_CONFIG; // Revenir à l'étape de configuration
}

$install_payload_array = null;
if (is_array($payload)) {
	$install_payload_array = $payload;
} elseif (!empty($payload) && is_string($payload)) {
	$install_payload_array = @unserialize(base64_decode($payload));
}
if (!is_array($install_payload_array) || count($install_payload_array) < 6) {
	$install_payload_array = null;
}

// Préfixes d'URL absolus : compatibles (1) URL sans slash final, (2) projet dans un sous-dossier
// (ex. http://localhost/EvoCMS-1/install/) — sinon /assets/... pointe hors du CMS dans le navigateur.
$__install_uri = '';
$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$installDir = realpath(__DIR__);
if ($docRoot !== false && $installDir !== false) {
	$docNorm = rtrim(str_replace('\\', '/', $docRoot), '/');
	$instNorm = str_replace('\\', '/', $installDir);
	if (strlen($instNorm) >= strlen($docNorm) && strncasecmp($instNorm, $docNorm, strlen($docNorm)) === 0) {
		$rel = trim(substr($instNorm, strlen($docNorm)), '/');
		if ($rel !== '') {
			$__install_uri = '/' . $rel;
		}
	}
}
if ($__install_uri === '') {
	$__script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
	$__install_uri = rtrim(dirname($__script_name), '/');
}
$__cms_uri = dirname($__install_uri);
if ($__cms_uri === '/' || $__cms_uri === '\\' || $__cms_uri === '.' || $__cms_uri === '') {
	$__evo_assets = '';
} else {
	$__evo_assets = rtrim(str_replace('\\', '/', $__cms_uri), '/');
}
$__href_bootstrap = htmlspecialchars($__evo_assets . '/assets/css/bootstrap.min.css', ENT_QUOTES, 'UTF-8');
$__href_vendor = htmlspecialchars($__evo_assets . '/assets/js/vendor.js', ENT_QUOTES, 'UTF-8');
$__href_install_css = htmlspecialchars($__install_uri . '/assets/style.css', ENT_QUOTES, 'UTF-8');
$__href_flags_css = htmlspecialchars($__install_uri . '/assets/flags.css', ENT_QUOTES, 'UTF-8');

/** Liens par défaut du projet (Evolution-Network) — barre latérale installation */
$install_eta_site_url = 'http://www.evolution-network.ca';
$install_eta_github_repo = 'https://github.com/coolternet/Unofficial_Files_CMS';
$install_eta_links = [
	[
		'href' => 'https://www.facebook.com/profile.php?id=100064090205432',
		'label' => 'Facebook',
		'title' => 'Facebook',
		'lucide' => 'facebook',
	],
	[
		'href' => $install_eta_site_url,
		'label' => 'Site Web',
		'title' => 'Evolution-Network',
		'lucide' => 'globe',
	],
	[
		'href' => $install_eta_github_repo,
		'label' => 'GitHub',
		'title' => 'GitHub',
		'lucide' => 'github',
	],
];

$install_pill_display = 'v' . EVO_VERSION . '-' . EVO_BUILD;
$install_pill_display_esc = htmlspecialchars($install_pill_display, ENT_QUOTES, 'UTF-8');
$install_pill_tooltip = htmlspecialchars(
	$install_pill_display . "\n" .
	EVO_RELEASEDATE . "\n" .
	EVO_BUILDDATE . "\n" .
	__('install.pill_db_schema') . ' ' . (int) DATABASE_VERSION .
	(EVO_UPDATE_URL !== '' ? "\n" . __('install.pill_update') . ' ' . EVO_UPDATE_URL : '') .
	(EVO_REPORT_EMAIL !== '' ? "\n" . __('install.pill_report') . ' ' . EVO_REPORT_EMAIL : ''),
	ENT_QUOTES,
	'UTF-8'
);

?>
<!doctype html>
<html lang="<?= htmlspecialchars($html_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars(__('install.page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <style id="evo-install-critical">.evo-install__steps{list-style:none;margin:0;padding:0}</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= $__href_bootstrap ?>" rel="stylesheet">
    <link href="<?= $__href_flags_css ?>" rel="stylesheet">
    <link href="<?= $__href_install_css ?>" rel="stylesheet">
    <script src="<?= $__href_vendor ?>"></script>
</head>
<body class="evo-install<?= $cur_step < 0 ? ' evo-install--blocked' : '' ?><?= $cur_step >= 0 ? ' evo-install--has-sidebar' : '' ?>" data-evo-install-step="<?= (int) $cur_step ?>">
    <div class="evo-install__ambient" aria-hidden="true">
        <div class="evo-install__ambient-base"></div>
        <div class="evo-install__ambient-glow evo-install__ambient-glow--tl"></div>
        <div class="evo-install__ambient-glow evo-install__ambient-glow--br"></div>
    </div>
    <div class="evo-install__frame">
        <header class="evo-install__header">
            <div class="evo-install__brand">
                <div class="evo-install__brand-text">
                    <h1 class="evo-install__product">Evo-CMS</h1>
                    <p class="evo-install__subtitle"><?= __('install.subtitle') ?></p>
                </div>
            </div>
            <div class="evo-install__meta">
                <span class="evo-install__pill" title="<?= $install_pill_tooltip ?>" aria-label="<?= $install_pill_display_esc ?>"><?= $install_pill_display_esc ?></span>
            </div>
        </header>

        <main class="evo-install__main">
            <div class="evo-install__card<?= $cur_step >= 0 ? ' evo-install__card--split' : '' ?>">
                <?php if ($cur_step >= 0): ?>
                <aside class="evo-install__sidebar">
                <nav class="evo-install__progress" aria-label="<?= htmlspecialchars(__('install.nav_aria'), ENT_QUOTES, 'UTF-8') ?>">
                    <ol class="evo-install__steps">
                    <?php
                    $progress_clickable = ($cur_step >= 0 && $cur_step < STEP_INSTALL);
                    foreach ($steps as $step => $tag) {
                        $isActive = $cur_step == $step;
                        $isCompleted = $cur_step > $step;
                        $hasError = $isActive && !empty($warning);

                        $liClass = 'evo-install__step';
                        if ($isActive) {
                            $liClass .= ' is-active';
                        } elseif ($isCompleted) {
                            $liClass .= ' is-done';
                        } else {
                            $liClass .= ' is-todo';
                        }
                        if ($hasError) {
                            $liClass .= ' has-error';
                        }

                        $tagHtml = htmlentities($tag, ENT_COMPAT, 'UTF-8');
                        $dot_icon = $install_step_dot_icons[$step] ?? 'circle';

                        ob_start();
                        echo '<span class="evo-install__dot">';
                        if ($isCompleted) {
                            echo install_lucide_icon('check', ['class' => 'evo-install__check', 'width' => 24, 'height' => 24]);
                        } elseif ($hasError) {
                            echo '<span class="evo-install__dot-x">!</span>';
                        } else {
                            echo install_lucide_icon($dot_icon, ['class' => 'evo-install__dot-icon', 'width' => 24, 'height' => 24]);
                        }
                        echo '</span>';
                        echo '<span class="evo-install__step-label">' . $tagHtml . '</span>';
                        $stepBody = ob_get_clean();

                        echo '<li class="' . $liClass . '">';
                        echo '<span class="evo-install__step-track" aria-hidden="true"></span>';
                        if ($isCompleted && $progress_clickable) {
                            echo '<button type="submit" form="form-content" name="step" value="' . (int) $step . '" class="evo-install__step-nav" title="' . htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') . '">';
                            echo $stepBody;
                            echo '</button>';
                        } else {
                            echo $stepBody;
                        }
                        echo '</li>';
                    }
                    ?>
                    </ol>
                </nav>
                <div class="evo-install__eta">
                    <?php if (!empty($install_eta_links)): ?>
                    <nav class="evo-install__eta-social" aria-label="<?= htmlspecialchars(__('install.social_nav_aria'), ENT_QUOTES, 'UTF-8') ?>">
                        <?php foreach ($install_eta_links as $eta_link):
                            $eta_href = $eta_link['href'];
                            $is_skype = str_starts_with($eta_href, 'skype:');
                            $eta_lucide = $eta_link['lucide'] ?? 'link';
                            ?>
                        <a class="evo-install__eta-social-link" href="<?= htmlspecialchars($eta_href, ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($eta_link['title'], ENT_QUOTES, 'UTF-8') ?>"<?php if (!$is_skype): ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>><?= install_lucide_icon($eta_lucide, ['class' => 'evo-install__eta-ico', 'width' => 16, 'height' => 16]) ?><span class="evo-install__eta-social-text"><?= htmlspecialchars($eta_link['label'], ENT_QUOTES, 'UTF-8') ?></span></a>
                        <?php endforeach; ?>
                    </nav>
                    <?php endif; ?>
                    <p class="evo-install__eta-copyright"><?= htmlspecialchars(str_replace('%year%', (string) date('Y'), __('install.copyright')), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                </aside>
                <?php endif; ?>

                <div class="evo-install__panel">
                <div class="evo-install__body">
                    <form method="post" autocomplete="off" id="form-content" class="evo-install__form">
                        <?php if (!empty($warning)): ?>
                            <div class="alert alert-error">
                                <?= $warning ?>
                            </div>
                        <?php endif; ?>
                        
                        <input type="hidden" name="language" value="<?= htmlspecialchars($install_locale, ENT_QUOTES, 'UTF-8') ?>">
                        
                        <?php if ($cur_step == STEP_LANGUAGE): ?>
                            <div class="step-content evo-install-step evo-install-step--enter">
                                <div class="step-header">
                                    <div class="step-header__art step-header__art--lang" aria-hidden="true">
                                        <?= install_lucide_icon('earth', ['class' => 'step-header__svg', 'width' => 72, 'height' => 72]) ?>
                                    </div>
                                    <h2 class="step-title"><?= __('language.title') ?></h2>
                                    <p class="step-description"><?= __('language.description') ?></p>
                                </div>
                                
                                <fieldset class="evo-install-lang-fieldset mb-3">
                                    <legend class="form-label"><?= htmlspecialchars(__('language.field_label'), ENT_QUOTES, 'UTF-8') ?></legend>
                                    <ul class="evo-install-lang-list" role="list">
                                        <?php
                                        foreach ($install_language_options as $locale => $name) {
                                            $isSel = $locale === $install_locale;
                                            $localeEsc = htmlspecialchars($locale, ENT_QUOTES, 'UTF-8');
                                            $nameEsc = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                                            echo '<li class="evo-install-lang-list__item" role="listitem">';
                                            $flagHtml = Widgets::countryFlag(install_locale_country_code($locale));
                                            $langHref = install_lang_switch_url($locale);
                                            echo '<a href="' . $langHref . '" class="evo-install-lang-option' . ($isSel ? ' is-selected' : '') . '" id="install_lang_' . preg_replace('/[^a-z0-9_-]/i', '_', $locale) . '" aria-current="' . ($isSel ? 'true' : 'false') . '">';
                                            echo '<span class="evo-install-lang-option__flag" aria-hidden="true">' . $flagHtml . '</span>';
                                            echo '<span class="evo-install-lang-option__name">' . $nameEsc . '</span>';
                                            echo '<span class="evo-install-lang-option__id">' . $localeEsc . '</span>';
                                            echo '</a></li>';
                                        }
                                        ?>
                                    </ul>
                                </fieldset>
                            </div>
                        <?php elseif ($cur_step == STEP_ACCEPT): ?>
                            <div class="step-content evo-install-step evo-install-step--enter">
                                <div class="step-header">
                                    <div class="step-header__art step-header__art--accept" aria-hidden="true">
                                        <svg class="step-header__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <?= install_lucide_paths('file-text') ?>
                                        </svg>
                                    </div>
                                    <h2 class="step-title"><?= htmlspecialchars(__('acceptance.title'), ENT_QUOTES, 'UTF-8') ?></h2>
                                    <p class="step-description"><?= htmlspecialchars(__('acceptance.description'), ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                                <div class="evo-install-acceptance">
                                    <div class="evo-install-acceptance__text">
                                        <?= nl2br(htmlspecialchars(__('acceptance.body'), ENT_QUOTES, 'UTF-8'), false) ?>
                                    </div>
                                </div>
                                <div class="evo-install-acceptance__field form-check">
                                    <input class="form-check-input" type="checkbox" name="install_accept" id="install_accept" value="1" required<?= !empty($_POST['install_accept']) ? ' checked' : '' ?>>
                                    <label class="form-check-label" for="install_accept"><?= htmlspecialchars(__('acceptance.checkbox_label'), ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                            </div>
                        <?php elseif ($cur_step == STEP_SYSCHECK): ?>
                            <div class="step-content">
                                <div class="step-header">
                                    <div class="step-header__art step-header__art--check" aria-hidden="true">
                                        <svg class="step-header__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <?= install_lucide_paths('clipboard-check') ?>
                                        </svg>
                                    </div>
                                    <h2 class="step-title"><?= __('checks.step_title') ?></h2>
                                    <p class="step-description"><?= __('checks.step_description') ?></p>
                                </div>
                                
                                <div class="alert alert-info mb-6"><?= __('checks.legend') ?></div>
                                
                                <div class="checks-list">
                                    <?php
                                    foreach ($checks as $check) {
                                        $isSuccess = $check[1];
                                        $checkClass = $isSuccess ? 'success' : 'error';
                                        $statusText = $isSuccess ? __('checks.status_ok') : __('checks.status_error');
                                        
                                        echo '<div class="check-item ' . $checkClass . '">';
                                        if ($isSuccess) {
                                            echo '<div class="check-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="check-icon__svg" aria-hidden="true"><circle cx="12" cy="12" r="10" class="check-icon__stroke"/><path class="check-icon__stroke" d="m9 12 2 2 4-4"/></svg></div>';
                                        } else {
                                            echo '<div class="check-icon">' . install_lucide_icon('circle-x', ['class' => 'check-icon__svg', 'width' => 18, 'height' => 18]) . '</div>';
                                        }
                                        echo '<div class="check-text">' . htmlentities($check[0], ENT_COMPAT, 'UTF-8') . '</div>';
                                        echo '<div class="check-status">' . $statusText . '</div>';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php elseif ($cur_step == STEP_DATABASE): ?>
                            <div class="step-content evo-install-step evo-install-step--enter">
                                <div class="step-header">
                                    <div class="step-header__art step-header__art--db" aria-hidden="true">
                                        <svg class="step-header__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <?= install_lucide_paths('database') ?>
                                        </svg>
                                    </div>
                                    <h2 class="step-title"><?= __('database.step_title') ?></h2>
                                    <p class="step-description"><?= __('database.step_description') ?></p>
                                </div>
                                
                                <div class="alert alert-info mb-6 db-alert">
                                    <?= __('database.sqlite_legend') ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label" for="type"><?= __('database.db_type_label') ?></label>
                                    <select class="form-select" id="type" name="db_type" required>
                                        <?php
                                        if (empty($db_types)) {
                                            echo '<option value="sqlite">' . htmlspecialchars(__('database.sqlite_default'), ENT_QUOTES, 'UTF-8') . '</option>';
                                        } else {
                                            $defaultType = @$_POST['db_type'] ?: 'sqlite';
                                            foreach ($db_types as $type => $label) {
                                                $selected = ($type == $defaultType) ? ' selected="selected"' : '';
                                                echo '<option value="' . $type . '"' . $selected . '>' . $label . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                    <input type="hidden" id="db_type_backup" name="db_type_backup" value="<?= @$_POST['db_type'] ?: 'sqlite' ?>">
                                </div>
                                
                                <div class="row db-fields-container">
                                    <div class="col-md-6 mysql db-field mb-3">
                                        <label class="form-label" for="host"><?= __('database.host') ?></label>
                                        <input type="text" class="form-control" id="host" name="db_host" value="<?= post_e('db_host', 'localhost') ?>">
                                    </div>
                                    
                                    <div class="col-md-6 sqlite mysql db-field mb-3">
                                        <label class="form-label" for="dbname"><?= __('database.name') ?></label>
                                        <input type="text" class="form-control" id="dbname" name="db_name" value="<?= post_e('db_name', 'db-' . substr(md5(uniqid()), 0, 6) . '.sqlite') ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mysql db-field mb-3">
                                        <label class="form-label" for="username"><?= __('database.username') ?></label>
                                        <input type="text" class="form-control" id="username" name="db_user" value="<?= post_e('db_user') ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mysql db-field mb-3">
                                        <label class="form-label" for="password"><?= __('database.password') ?></label>
                                        <input type="password" class="form-control" id="password" name="db_pass" value="<?= post_e('db_pass') ?>">
                                    </div>
                                    
                                    <div class="col-md-6 sqlite mysql db-field mb-3">
                                        <label class="form-label" for="prefixe"><?= __('database.prefix') ?></label>
                                        <input type="text" class="form-control" id="prefixe" name="db_prefix" value="<?= post_e('db_prefix', 'evo_') ?>">
                                    </div>
                                </div>
                            </div>
                                
							<script>
								$(function() {
									function updateFormFields() {
										var selectedType = $('#type').val();
										var container = $('.db-fields-container');
										var alert = $('.db-alert');
										
										// Synchroniser le champ caché
										$('#db_type_backup').val(selectedType);
										
										// Cacher tous les champs de base de données
										$('.db-field').hide();
										$('.' + selectedType).show();
										
										// Gérer le layout des colonnes
										if (selectedType == 'mysql') {
											container.addClass('mysql-layout');
											alert.hide(); // Masquer l'alerte pour MySQL
										} else {
											container.removeClass('mysql-layout');
											alert.show(); // Afficher l'alerte pour SQLite
										}
										
										if (selectedType == 'sqlite') {
											// Générer un nom de base de données unique côté client seulement si vide
											if (!$('#dbname').val()) {
												var randomId = Math.random().toString(36).substr(2, 6);
												$('#dbname').val('db-' + randomId + '.sqlite');
											}
											$('#prefixe').val('');
										} else {
											if (selectedType == 'mysql') {
												$('#dbname').val('');
												$('#prefixe').val('evo_');
											}
										}
									}
									
									// Intercepter la soumission du formulaire
									$('#form-content').on('submit', function(e) {
										var selectedType = $('#type').val();
										
										// S'assurer que le nom de base de données est généré pour SQLite
										if (selectedType == 'sqlite' && !$('#dbname').val()) {
											var randomId = Math.random().toString(36).substr(2, 6);
											$('#dbname').val('db-' + randomId + '.sqlite');
										}
										
										// Validation des champs requis
										if (selectedType == 'mysql') {
											var host = $('#host').val().trim();
											var user = $('#username').val().trim();
											var dbname = $('#dbname').val().trim();
											
											if (!host || !user || !dbname) {
												e.preventDefault();
												alert('Veuillez remplir tous les champs requis pour MySQL (Host, Utilisateur, Nom de la base de données)');
												return false;
											}
										} else if (selectedType == 'sqlite') {
											var dbname = $('#dbname').val().trim();
											console.log('SQLite dbname value:', dbname);
											if (!dbname) {
												e.preventDefault();
												alert('Veuillez remplir le nom de la base de données SQLite');
												return false;
											}
										}
										
										// S'assurer que tous les champs nécessaires sont visibles avant soumission
										$('.db-field').hide();
										$('.' + selectedType).show();
										
										// Forcer la visibilité des champs requis
										if (selectedType == 'mysql') {
											$('.mysql').show();
										} else {
											$('.sqlite').show();
										}
										
										// Synchroniser le champ caché
										$('#db_type_backup').val(selectedType);
										
										console.log('Form submitted with type:', selectedType);
										console.log('Visible fields:', $('.db-field:visible').length);
									});
									
									$('#type').bind('change blur keyup', updateFormFields);
									
									// Initialiser les champs au chargement de la page
									updateFormFields();
								});
							</script>
                        <?php elseif ($cur_step == STEP_CONFIG): ?>
                            <?php
								$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
								$url = $scheme.'://'.$_SERVER['HTTP_HOST'];
								$dir = rtrim(strstr($_SERVER['REQUEST_URI'].'?', '?', true), '/');
								$slash = strrpos($dir, '/');
								$url .= $slash !== false ? substr($dir, 0, $slash) : $dir;
                            ?>
                            <?php if ($install_payload_array): ?>
                            <input type="hidden" name="db_type" value="<?= htmlspecialchars((string) $install_payload_array[5], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="db_type_backup" value="<?= htmlspecialchars((string) $install_payload_array[5], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="db_host" value="<?= htmlspecialchars((string) $install_payload_array[0], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="db_user" value="<?= htmlspecialchars((string) $install_payload_array[1], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="db_pass" value="<?= htmlspecialchars((string) $install_payload_array[2], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="db_name" value="<?= htmlspecialchars((string) $install_payload_array[3], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="db_prefix" value="<?= htmlspecialchars((string) $install_payload_array[4], ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                            <div class="step-content row evo-install-step evo-install-step--enter">
                                <div class="step-header">
                                    <div class="step-header__art step-header__art--cfg" aria-hidden="true">
                                        <svg class="step-header__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <circle cx="14" cy="4" r="2" fill="currentColor" opacity="0.45" class="step-header__knob"/>
                                            <?= install_lucide_paths('sliders-horizontal') ?>
                                        </svg>
                                    </div>
                                    <h2 class="step-title"><?= __('config.step_title') ?></h2>
                                    <p class="step-description"><?= __('config.step_description') ?></p>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="sitename"><?= __('config.sitename') ?></label>
                                            <input type="text" class="form-control" id="sitename" name="name" value="<?= post_e('name', 'Evo-CMS '.EVO_VERSION) ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="siteurl"><?= __('config.siteurl') ?></label>
                                            <input type="text" class="form-control" id="siteurl" name="url" value="<?= post_e('url', $url) ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="sitemail"><?= __('config.siteemail') ?></label>
                                            <input type="email" class="form-control" id="sitemail" name="email" placeholder="example@domain.com" value="<?= post_e('email') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="sitelogin"><?= __('config.username') ?></label>
                                            <input type="text" class="form-control" id="sitelogin" name="admin" value="<?= post_e('admin', 'admin') ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="sitepass"><?= __('config.password') ?></label>
                                            <input type="password" class="form-control" id="sitepass" name="admin_pass" value="<?= post_e('admin_pass') ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="sitepass2"><?= __('config.password_confirm') ?></label>
                                            <input type="password" class="form-control" id="sitepass2" name="admin_pass_confirm" value="<?= post_e('admin_pass_confirm') ?>" placeholder="<?= htmlspecialchars(__('config.password_confirm_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                </div>

                                <?php if (EVO_REPORT_EMAIL): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <input type="checkbox" name="report" id="report" value="1" checked>
                                        <?= __('config.report') ?>
                                    </label>
                                </div>
                                <?php endif ?>
                            </div>
                        <?php elseif ($cur_step == STEP_INSTALL): ?>
                            <div class="step-content">                                
                                <?php if ($failed): ?>
                                    <div class="alert alert-error">
                                        <h6><?= __('install.failed') ?></h6>
                                        <span><?= __('install.failed_legend') ?></span>
                                        <p><?= $failed ?></p>
                                    </div>
                                <?php elseif ($done): ?>
                                    <div class="alert alert-success">
                                        <h6><?= __('install.success') ?></h6>
                                        <span><?= __('install.success_legend') ?></span>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><?= __('config.siteurl') ?></label>
                                                <div class="form-control" style="background: var(--system-background-secondary); color: var(--system-label);">
                                                    <?= $_POST['url'] ?>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label"><?= __('config.adminurl') ?></label>
                                                <div class="form-control" style="background: var(--system-background-secondary); color: var(--system-label);">
                                                    <?= $_POST['url'] ?>/admin
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><?= __('config.username') ?></label>
                                                <div class="form-control" style="background: var(--system-background-secondary); color: var(--system-label);">
                                                    <?= $_POST['admin'] ?>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label"><?= __('config.password') ?></label>
                                                <div class="form-control" style="background: var(--system-background-secondary); color: var(--system-label);">
                                                    <?= $_POST['admin_pass'] ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

						<input type="hidden" name="from_step" value="<?= $cur_step ?>">
						<input type="hidden" name="payload" value="<?= is_array($payload) ? base64_encode(serialize($payload)) : $payload ?>">

						<?php if (empty($hide_nav)): ?>
						<div class="evo-install__actions" role="group" aria-label="<?= htmlspecialchars(__('install.nav_aria'), ENT_QUOTES, 'UTF-8') ?>">
							<?php if ($cur_step > STEP_LANGUAGE): ?>
							<button type="submit" name="step" value="<?= (int) ($cur_step - 1) ?>" class="evo-install__btn evo-install__btn--back">
								<?= install_lucide_icon('chevron-left', ['class' => 'evo-install__btn-ico', 'width' => 20, 'height' => 20]) ?>
								<span><?= __('buttons.previous') ?></span>
							</button>
							<?php endif; ?>
							<?php if ($next_step <= max(array_keys($steps))): ?>
							<button type="submit" name="step" value="<?= $next_step ?>" id="install-step-next" class="evo-install__btn evo-install__btn--next"<?= ($next_step >= STEP_CONFIG ? ' onclick="$(\'#form-content\').toggle();"' : '') ?>>
								<span><?= __('buttons.next') ?></span>
								<?= install_lucide_icon('chevron-right', ['class' => 'evo-install__btn-ico', 'width' => 20, 'height' => 20]) ?>
							</button>
							<?php endif; ?>
						</div>
						<?php elseif (isset($done) && $done): ?>
						<div class="evo-install__actions evo-install__actions--solo">
							<button type="submit" name="step" value="<?= STEP_CLEANUP ?>" class="evo-install__btn evo-install__btn--success evo-install__btn--block">
								<?= install_lucide_icon('check', ['class' => 'evo-install__btn-ico', 'width' => 22, 'height' => 22, 'stroke-width' => '2.2']) ?>
								<span><?= __('install.complete') ?></span>
							</button>
						</div>
						<?php endif; ?>
					</form>
                </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        $(function() {
            // Initialize Bootstrap 5 tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>