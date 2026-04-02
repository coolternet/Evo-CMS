<!DOCTYPE html>
<html lang="<?= html_encode(App::getConfig('language') ?: 'fr') ?>">

<head>
	<?php App::renderTemplate('head.php', $variables); ?>
	<link href="<?= App::getAsset('css/bootstrap.min.css') ?>" rel="stylesheet">
	<link href="<?= App::getAsset('css/website.css') ?>" rel="stylesheet">
	<link href="<?= App::getAsset('css/theme.css') ?>" rel="stylesheet">
</head>

<body class="tpl-main website theme-default <?= trim($_body_class) ?>">
	<div id="header">
		<div class="logo">
			<a href="<?= App::getURL('/') ?>">
			<?= '<img src="'.App::getAsset(App::getConfig('theme.logo') ?: '/img/logo.png'). '" alt="'.html_encode(App::getConfig('name')).'">'; ?>
			</a>
		</div>
		<div class="links">
			<?php foreach(Evo\Social::getProviders(false) as $social => [$name, $icon, $regex]): ?>
				<?php if ($link = App::getConfig("social.$social")): ?>
				<a href="<?= html_encode($link) ?>"><i class="social-link social-<?= $social ?> fab <?= $icon ?>" title="<?= $name ?>"></i></a>
				<?php endif; ?>
			<?php endforeach; ?>
			<a href="<?= App::getURL('feed')?>"><i class="social-link social-rss fas fa-rss" title="<?= __("main.rss") ?>"></i></a>
		</div>
	</div>
	<div class="clearfix"></div>
	<div id="wrapper">
		<div id="sidebar">
			<div id="zone_login">
			<?php if (has_permission()) { ?>
				<div class="avatar_container"><?= get_avatar(App::getCurrentUser()) ?></div>
				<div class="welcome">Bienvenue <?= html_encode(App::getCurrentUser()->username) ?></div>
				<?php App::renderTemplate('userdropdown.php', $variables); ?>
			<?php } else { ?>
				<div class="text-center">
					<div class="btn-group">
						<a href="<?= App::getURL('login') ?>" class="btn btn-primary"><i class="fa fa-user"></i> <?= __('main.login') ?></a>
						<a href="<?= App::getURL('register') ?>" class="btn btn-success"><i class="fa fa-pencil-alt"></i> <?= __('main.join') ?></a>
					</div>
				</div>
			<?php } ?>
			</div>
			<div id="menu">
				<?= Widgets::menu(); ?>
			</div>
			<div id="notifications"></div>
		</div>

		<div id="page">
			<div class="alerts">
			<?php
				if (!empty($_success)) {
					echo '<div class="alert alert-success alert-dismissible auto-dismiss"><button type="button" class="btn-close"
					data-bs-dismiss="alert" aria-label="Close"></button>'.$_success.'</div>';
				}
				if (!empty($_warning)) {
					echo '<div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close"
					data-bs-dismiss="alert" aria-label="Close"></button>'.$_warning.'</div>';
				}
				if (!empty($_notice)) {
					echo '<div class="alert alert-warning alert-dismissible"><button type="button" class="btn-close"
					data-bs-dismiss="alert" aria-label="Close"></button>'.$_notice.'</div>';
				}
			?>
			</div>
			<?= $_content ?>
		</div>

	<?php App::renderTemplate('footer.php', $variables); ?>

	</div><!-- wrapper -->
</body>
</html>
