<?php
/**
 * Vues HTML de l’assistant d’installation (aucune logique métier).
 */

/**
 * URL du site devinée à partir de la requête (étape configuration).
 */
function install_guess_default_site_url(): string {
	$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
	$url = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '');
	$uri = $_SERVER['REQUEST_URI'] ?? '/';
	$dir = rtrim(strstr($uri . '?', '?', true) ?: $uri, '/');
	$slash = strrpos($dir, '/');
	$url .= $slash !== false ? substr($dir, 0, $slash) : $dir;

	return $url;
}

function install_render_step_language(array $install_language_options, string $install_locale): string {
	ob_start();
	?>
							<div class="step-content evo-install-step evo-install-step--enter evo-install-step--lang-text-only">
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
											$nameEsc = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
											echo '<li class="evo-install-lang-list__item" role="listitem">';
											$flagHtml = Widgets::countryFlag(install_locale_country_code($locale));
											$langHref = install_lang_switch_url($locale);
											echo '<a href="' . $langHref . '" class="evo-install-lang-option' . ($isSel ? ' is-selected' : '') . '" id="install_lang_' . preg_replace('/[^a-z0-9_-]/i', '_', $locale) . '" aria-current="' . ($isSel ? 'true' : 'false') . '">';
											echo '<span class="evo-install-lang-option__flag" aria-hidden="true">' . $flagHtml . '</span>';
											echo '<span class="evo-install-lang-option__name">' . $nameEsc . '</span>';
											echo '</a></li>';
										}
										?>
									</ul>
								</fieldset>
							</div>
	<?php
	return (string) ob_get_clean();
}

function install_render_step_accept(): string {
	ob_start();
	?>
							<div class="step-content evo-install-step evo-install-step--enter evo-install-step--accept">
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
	<?php
	return (string) ob_get_clean();
}

function install_render_step_syscheck(array $checks): string {
	ob_start();
	?>
							<div class="step-content evo-install-step--checks">
								<div class="step-header">
									<div class="step-header__art step-header__art--check" aria-hidden="true">
										<svg class="step-header__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
											<?= install_lucide_paths('clipboard-check') ?>
										</svg>
									</div>
									<h2 class="step-title"><?= __('checks.step_title') ?></h2>
									<p class="step-description"><?= __('checks.step_description') ?></p>
								</div>

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
	<?php
	return (string) ob_get_clean();
}

function install_render_step_database(array $db_types): string {
	ob_start();
	?>
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
												echo '<option value="' . htmlspecialchars((string) $type, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') . '</option>';
											}
										}
										?>
									</select>
									<input type="hidden" id="db_type_backup" name="db_type_backup" value="<?= htmlspecialchars((string) (@$_POST['db_type'] ?: 'sqlite'), ENT_QUOTES, 'UTF-8') ?>">
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
	<?php
	return (string) ob_get_clean();
}

function install_render_step_config(?array $install_payload_array, string $default_site_url): string {
	ob_start();
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
											<?= install_lucide_paths('settings') ?>
										</svg>
									</div>
									<h2 class="step-title"><?= __('config.step_title') ?></h2>
									<p class="step-description"><?= __('config.step_description') ?></p>
								</div>

								<div class="row">
									<div class="col-md-6">
										<div class="mb-3">
											<label class="form-label" for="sitename"><?= __('config.sitename') ?></label>
											<input type="text" class="form-control" id="sitename" name="name" value="<?= post_e('name', 'Evo-CMS ' . EVO_VERSION) ?>">
										</div>

										<div class="mb-3">
											<label class="form-label" for="siteurl"><?= __('config.siteurl') ?></label>
											<input type="text" class="form-control" id="siteurl" name="url" value="<?= post_e('url', $default_site_url) ?>">
										</div>

										<div class="mb-3">
											<label class="form-label" for="sitemail"><?= __('config.siteemail') ?></label>
											<input type="email" class="form-control" id="sitemail" name="email" placeholder="<?= htmlspecialchars(__('config.email_placeholder'), ENT_QUOTES, 'UTF-8') ?>" value="<?= post_e('email') ?>">
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
	<?php
	return (string) ob_get_clean();
}

function install_render_step_install(string $failed, $done): string {
	ob_start();
	?>
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
	<?php
	return (string) ob_get_clean();
}

/**
 * Contenu principal du formulaire (étapes).
 *
 * @param array<string, mixed> $ctx
 */
function install_render_form_step_content(array $ctx): string {
	$cur_step = (int) $ctx['cur_step'];
	$install_language_options = $ctx['install_language_options'];
	$install_locale = (string) $ctx['install_locale'];
	$checks = $ctx['checks'] ?? [];
	$db_types = $ctx['db_types'];
	$install_payload_array = $ctx['install_payload_array'];
	$failed = (string) ($ctx['failed'] ?? '');
	$done = $ctx['done'] ?? null;
	$default_site_url = (string) $ctx['default_site_url'];

	switch ($cur_step) {
		case STEP_LANGUAGE:
			return install_render_step_language($install_language_options, $install_locale);
		case STEP_ACCEPT:
			return install_render_step_accept();
		case STEP_SYSCHECK:
			return install_render_step_syscheck(is_array($checks) ? $checks : []);
		case STEP_DATABASE:
			return install_render_step_database($db_types);
		case STEP_CONFIG:
			return install_render_step_config(
				is_array($install_payload_array) ? $install_payload_array : null,
				$default_site_url
			);
		case STEP_INSTALL:
			return install_render_step_install($failed, $done);
		default:
			return '';
	}
}

/**
 * Page HTML complète de l’installation.
 *
 * @param array<string, mixed> $v
 */
function install_render_install_page(array $v): string {
	$html_lang = (string) $v['html_lang'];
	$href_bootstrap = (string) $v['href_bootstrap'];
	$href_flags = (string) $v['href_flags'];
	$href_install_css = (string) $v['href_install_css'];
	$href_vendor = (string) $v['href_vendor'];
	$cur_step = (int) $v['cur_step'];
	$warning = (string) ($v['warning'] ?? '');
	$install_locale = (string) $v['install_locale'];
	$install_language_options = $v['install_language_options'];
	$steps = $v['steps'];
	$install_step_dot_icons = $v['install_step_dot_icons'];
	$failed = (string) ($v['failed'] ?? '');
	$done = $v['done'] ?? null;
	$checks = $v['checks'] ?? [];
	$db_types = $v['db_types'];
	$install_payload_array = $v['install_payload_array'];
	$default_site_url = (string) $v['default_site_url'];
	$next_step = (int) $v['next_step'];
	$hide_nav = !empty($v['hide_nav']);
	$payload = $v['payload'] ?? '';
	$install_progress_nav_sidebar = (string) $v['install_progress_nav_sidebar'];
	$install_eta_html_sidebar = (string) $v['install_eta_html_sidebar'];
	$install_eta_html_mobile = (string) $v['install_eta_html_mobile'];
	$pill_tooltip = (string) $v['pill_tooltip'];
	$pill_display_esc = (string) $v['pill_display_esc'];
	$alert_mysql = (string) $v['alert_mysql'];
	$alert_sqlite = (string) $v['alert_sqlite'];

	$bodyClass = 'evo-install' . ($cur_step < 0 ? ' evo-install--blocked' : '') . ($cur_step >= 0 ? ' evo-install--has-sidebar' : '');
	$cardClass = 'evo-install__card' . ($cur_step >= 0 ? ' evo-install__card--split' : '');

	$card_header_icon = $install_step_dot_icons[$cur_step] ?? 'circle';
	$card_header_subtitle = install_card_header_subtitle($cur_step, $failed, $done);

	$ctx = [
		'cur_step' => $cur_step,
		'install_language_options' => $install_language_options,
		'install_locale' => $install_locale,
		'checks' => $checks,
		'db_types' => $db_types,
		'install_payload_array' => $install_payload_array,
		'failed' => $failed,
		'done' => $done,
		'default_site_url' => $default_site_url,
	];

	$payload_hidden = is_array($payload) ? base64_encode(serialize($payload)) : (string) $payload;

	ob_start();
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
    <link href="<?= htmlspecialchars($href_bootstrap, ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <link href="<?= htmlspecialchars($href_flags, ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <link href="<?= htmlspecialchars($href_install_css, ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <script src="<?= htmlspecialchars($href_vendor, ENT_QUOTES, 'UTF-8') ?>"></script>
</head>
<body class="<?= htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') ?>" data-evo-install-step="<?= (int) $cur_step ?>" data-evo-install-js-alert-mysql="<?= htmlspecialchars($alert_mysql, ENT_QUOTES, 'UTF-8') ?>" data-evo-install-js-alert-sqlite="<?= htmlspecialchars($alert_sqlite, ENT_QUOTES, 'UTF-8') ?>"><script>
try { if (localStorage.getItem('evoInstallColorMode') === 'light') { document.body.classList.add('evo-install--light'); } } catch (e) {}
</script>
    <div class="evo-install__ambient" aria-hidden="true">
        <div class="evo-install__ambient-base"></div>
        <div class="evo-install__ambient-glow evo-install__ambient-glow--top"></div>
        <div class="evo-install__ambient-glow evo-install__ambient-glow--tl"></div>
        <div class="evo-install__ambient-glow evo-install__ambient-glow--br"></div>
    </div>
    <div class="evo-install__frame">
        <header class="evo-install__header">
            <div class="evo-install__brand">
                <h1 class="evo-install__product">Evo-CMS</h1>
                <p class="evo-install__subtitle"><?= __('install.subtitle') ?></p>
            </div>
            <div class="evo-install__meta">
                <span class="evo-install__pill" title="<?= $pill_tooltip ?>" aria-label="<?= $pill_display_esc ?>"><?= $pill_display_esc ?></span>
            </div>
        </header>

        <?php if ($cur_step >= 0): ?>
        <div class="evo-install__split-wrap">
        <aside id="evo-install-sidebar" class="evo-install__sidebar evo-install__sidebar--desktop">
                <?= $install_progress_nav_sidebar ?>
                <?= $install_eta_html_sidebar ?>
        </aside>
        <?php endif; ?>

        <main class="evo-install__main">
            <div class="<?= htmlspecialchars($cardClass, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($cur_step >= 0): ?>
                <div class="card-header evo-install__card-header">
                    <span class="evo-install__card-header-icon" aria-hidden="true"><?= install_lucide_icon($card_header_icon, ['class' => 'evo-install__card-header-svg', 'width' => 22, 'height' => 22]) ?></span>
                    <div class="evo-install__card-header-text">
                        <h3 class="evo-install__card-header-title mb-0"><?= htmlspecialchars($steps[$cur_step] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if ($card_header_subtitle !== ''): ?>
                        <p class="evo-install__card-header-subtitle"><?= htmlspecialchars($card_header_subtitle, ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="evo-install__panel">
                    <form method="post" autocomplete="off" id="form-content" class="evo-install__form container">
                        <?php if ($warning !== ''): ?>
                            <div class="alert alert-error">
                                <?= $warning ?>
                            </div>
                        <?php endif; ?>

                        <input type="hidden" name="language" value="<?= htmlspecialchars($install_locale, ENT_QUOTES, 'UTF-8') ?>">

                        <?= install_render_form_step_content($ctx) ?>

						<input type="hidden" name="from_step" value="<?= (int) $cur_step ?>">
						<input type="hidden" name="payload" value="<?= htmlspecialchars($payload_hidden, ENT_QUOTES, 'UTF-8') ?>">

						<?php if (empty($hide_nav)): ?>
						<div class="evo-install__actions" role="group" aria-label="<?= htmlspecialchars(__('install.nav_aria'), ENT_QUOTES, 'UTF-8') ?>">
							<?php if ($cur_step > STEP_LANGUAGE): ?>
							<button type="submit" name="step" value="<?= (int) ($cur_step - 1) ?>" class="evo-install__btn evo-install__btn--back" formnovalidate>
								<?= install_lucide_icon('chevron-left', ['class' => 'evo-install__btn-ico', 'width' => 20, 'height' => 20]) ?>
								<span><?= __('buttons.previous') ?></span>
							</button>
							<?php endif; ?>
							<?php if ($next_step <= max(array_keys($steps))): ?>
							<button type="submit" name="step" value="<?= (int) $next_step ?>" id="install-step-next" class="evo-install__btn evo-install__btn--next"<?= ($next_step >= STEP_CONFIG ? ' onclick="$(\'#form-content\').toggle();"' : '') ?>>
								<span><?= __('buttons.next') ?></span>
								<?= install_lucide_icon('chevron-right', ['class' => 'evo-install__btn-ico', 'width' => 20, 'height' => 20]) ?>
							</button>
							<?php endif; ?>
						</div>
						<?php elseif (isset($done) && $done): ?>
						<div class="evo-install__actions evo-install__actions--solo">
							<button type="submit" name="step" value="<?= (int) STEP_CLEANUP ?>" class="evo-install__btn evo-install__btn--success evo-install__btn--block">
								<?= install_lucide_icon('check', ['class' => 'evo-install__btn-ico', 'width' => 22, 'height' => 22, 'stroke-width' => '2.2']) ?>
								<span><?= __('install.complete') ?></span>
							</button>
						</div>
						<?php endif; ?>
					</form>
					<?php if ($cur_step >= 0): ?>
					<?= $install_eta_html_mobile ?>
					<?php endif; ?>
                </div>
            </div>
        </main>
        <?php if ($cur_step >= 0): ?>
        </div>
        <?php endif; ?>
    </div>
    <script src="<?= htmlspecialchars((string) $v['href_install_js'], ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
	<?php
	return (string) ob_get_clean();
}
