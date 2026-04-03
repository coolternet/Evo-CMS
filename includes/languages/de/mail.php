<?php

return [
	'wrapper' => "Hallo %username%,\n\n%message%\n\n%sitename%\n%siteurl%",

	'forum.topic.subject' => 'Neue Nachricht im Thema %subject%',
	'forum.topic.body' => "%url%",
	
	'friends.request.subject' => '%friend% hat Ihnen eine Freundschaftsanfrage gesendet!',
	'friends.request.body' => "Sie haben eine Freundschaftsanfrage von %friend% erhalten!\n\nMelden Sie sich an, um sie anzunehmen oder abzulehnen.",

	'account.activation.subject' => 'Konto auf %sitename% aktivieren',
	'account.activation.body' => "Folgen Sie diesem Link, um Ihr Konto zu aktivieren: %activation_url%\nMit freundlichen Grüßen,",
	
	'account.reset_password.subject' => 'Passwort vergessen',
	'account.reset_password.body' => "Folgen Sie diesem Link, um Ihr Passwort zurückzusetzen:\n%resetlink%\nMit freundlichen Grüßen,",

	'message.type.0.subject' => "Sie haben eine Nachricht erhalten",
	'message.type.0.body' => "Sie haben eine neue Nachricht von %mailfrom%:\n\n%message%",

	'message.type.1.subject' => "Jemand hat Sie erwähnt",
	'message.type.1.body' => "%mailfrom% hat Sie auf %sitename% erwähnt!\n\n%message%",

	'message.type.2.subject' => "Wichtige Nachricht",
	'message.type.2.body' => "Sie haben eine wichtige Nachricht auf %sitename% erhalten:\n\n%message%",

	'message.type.3.subject' => "Sie haben eine Verwarnung erhalten",
	'message.type.3.body' => "Sie haben eine Verwarnung auf %sitename% erhalten:\n%message%",
];
