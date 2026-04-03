<?php

return [
	'wrapper' => "Hola %username%,\n\n%message%\n\n%sitename%\n%siteurl%",

	'forum.topic.subject' => 'Nuevo mensaje en el hilo %subject%',
	'forum.topic.body' => "%url%",
	
	'friends.request.subject' => '¡%friend% le ha enviado una solicitud de amistad!',
	'friends.request.body' => "¡Ha recibido una solicitud de amistad de %friend%!\n\nConéctese para aceptarla o rechazarla.",

	'account.activation.subject' => 'Active su cuenta en %sitename%',
	'account.activation.body' => "Siga el siguiente enlace para activar su cuenta %activation_url%\nAtentamente,",
	
	'account.reset_password.subject' => 'Contraseña olvidada',
	'account.reset_password.body' => "Siga el siguiente enlace para restablecer su contraseña:\n%resetlink%\nAtentamente,",

	'message.type.0.subject' => "Ha recibido un mensaje",
	'message.type.0.body' => "Ha recibido un nuevo mensaje de %mailfrom%:\n\n%message%",

	'message.type.1.subject' => "Alguien le ha mencionado",
	'message.type.1.body' => "¡%mailfrom% le ha mencionado en %sitename%!\n\n%message%",

	'message.type.2.subject' => "Mensaje importante",
	'message.type.2.body' => "Ha recibido un mensaje importante en %sitename%:\n\n%message%",

	'message.type.3.subject' => "Ha recibido una advertencia",
	'message.type.3.body' => "Ha recibido una advertencia en %sitename%:\n%message%",
];
