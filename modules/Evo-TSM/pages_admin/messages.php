<?php

require_once __DIR__.'/core/functions.php';

$messages = get_contact_messages();
$message_count = get_contact_message_count();

include __DIR__.'/templates/main.php';
include __DIR__.'/pages/messages.php';
