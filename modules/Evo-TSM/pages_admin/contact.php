<?php

    $title = "Incident report or make a suggestion";

    $agent_check = \DB::Get("SELECT last_user_agent FROM {users} WHERE id = :uid",[':uid' => App::getCurrentUser()->id]);

    $useragent = $agent_check['last_user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '';

    $phpver = explode('-', phpversion());

    // Traitement du formulaire
    $message_sent = false;
    $error_message = '';

    if ($_POST) {
        // Cache l'utilisateur actuel pour éviter les appels répétitifs
        $current_user = App::getCurrentUser();
        
        // Logs de debug uniquement en mode développement
        if (defined('EVO_DEBUG') && EVO_DEBUG) {
        }
        
        // Validation centralisée du formulaire
        $validation = validate_contact_form($_POST);
        
        if (!$validation['valid']) {
            $error_message = implode(' ', $validation['errors']);
            log_ticket_error('warning', 'Contact form validation failed', $validation['errors']);
        } else {
            // Utiliser les données nettoyées
            $subject = $validation['cleaned_data']['subject'];
            $message = $validation['cleaned_data']['message'];
            $followup = $validation['cleaned_data']['followup'];
            // Préparer l'email
            $email_subject = "[Contact Ticket System] " . $subject;
            
            $email_body = "Nouveau message de contact depuis le système de tickets\n\n";
            $email_body .= "De: " . $current_user->username . " (" . $current_user->email . ")\n";
            $email_body .= "Date: " . date("Y-m-d H:i:s") . "\n";
            $email_body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Inconnue') . "\n";
            $email_body .= "User Agent: " . ($useragent ?: 'Inconnu') . "\n\n";
            $email_body .= "Sujet: " . $subject . "\n\n";
            $email_body .= "Message:\n" . $message . "\n\n";
            
            if ($followup) {
                $email_body .= "L'utilisateur souhaite un suivi.\n";
            }

            // Récupérer l'email du créateur du plugin
            $creator_email = get_module_author_email('Evo-TSM');
            
            // Vérifier que l'email est valide
            if (!filter_var($creator_email, FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Email du créateur invalide.';
                if (defined('EVO_DEBUG') && EVO_DEBUG) {
                }
            } else {
                
                // Essayer d'abord avec SendPrivateMessage si l'utilisateur existe
                $user_exists = \DB::Get("SELECT id FROM {users} WHERE email = ?", [$creator_email]);
                
                if ($user_exists && is_array($user_exists) && isset($user_exists['id'])) {
                    // L'utilisateur existe, utiliser SendPrivateMessage
                    try {
                        $result = SendPrivateMessage($user_exists['id'], $email_subject, $email_body);
                        
                        if ($result) {
                            $message_sent = true;
                        } else {
                            $error_message = 'Erreur lors de l\'envoi de l\'email via le système interne.';
                        }
                    } catch (Exception $e) {
                        $error_message = 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage();
                    }
                } else {
                    // L'utilisateur n'existe pas, essayer d'envoyer par mail() natif puis sauvegarder
                    error_log("Contact Form: User not found in database, trying native mail() then saving to database");
                    
                    $email_sent = false;
                    
                    // Essayer d'envoyer l'email avec le système EvoCMS
                    try {
                        error_log("Contact Form: Using EvoCMS App::sendmail() method");
                        
                        // Vérifier la configuration email d'EvoCMS
                        $mail_method = App::getConfig('mail.send_method');
                        $from_email = App::getConfig('email');
                        $site_name = App::getConfig('name');
                        
                        error_log("Contact Form: EvoCMS config - method: " . ($mail_method ?: 'not set') . ", from: " . ($from_email ?: 'not set') . ", site: " . ($site_name ?: 'not set'));
                        
                        if (!$from_email) {
                            error_log("Contact Form Error: EvoCMS email configuration not set (App::getConfig('email') is empty)");
                            error_log("Contact Form: Falling back to native mail() function");
                            
                            // Fallback vers mail() natif
                            $headers = "From: " . App::getCurrentUser()->email . "\r\n";
                            $headers .= "Reply-To: " . App::getCurrentUser()->email . "\r\n";
                            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                            $headers .= "X-Mailer: EvoCMS Ticket System\r\n";
                            
                            $old_error_reporting = error_reporting();
                            error_reporting(0);
                            
                            $email_result = mail($creator_email, $email_subject, $email_body, $headers);
                            
                            error_reporting($old_error_reporting);
                            
                            error_log("Contact Form: Native mail() fallback result - " . ($email_result ? 'SUCCESS' : 'FAILED'));
                            
                            if ($email_result) {
                                $email_sent = true;
                                error_log("Contact Form: Email sent successfully via native mail() fallback to " . $creator_email);
                            } else {
                                error_log("Contact Form Error: Native mail() fallback failed for " . $creator_email);
                            }
                        } else {
                            $email_result = App::sendmail($creator_email, $email_subject, $email_body, '', $error);
                            
                            error_log("Contact Form: EvoCMS sendmail result - " . ($email_result ? 'SUCCESS' : 'FAILED'));
                            
                            if ($email_result) {
                                $email_sent = true;
                                error_log("Contact Form: Email sent successfully via EvoCMS to " . $creator_email);
                            } else {
                                error_log("Contact Form Error: EvoCMS sendmail failed for " . $creator_email);
                                if ($error) {
                                    error_log("Contact Form Error Details: " . $error);
                                    
                                    // Détecter l'erreur spécifique de la fonction mail
                                    if (strpos($error, 'Could not instantiate mail function') !== false) {
                                        $error_message = 'La fonction d\'envoi d\'email de PHP n\'est pas disponible sur ce serveur.';
                                        error_log("Contact Form: Detected 'Could not instantiate mail function' error");
                                    } elseif (strpos($error, 'SMTP') !== false) {
                                        $error_message = 'Erreur de configuration SMTP. Vérifiez les paramètres d\'email dans l\'administration.';
                                        error_log("Contact Form: Detected SMTP configuration error");
                                    } else {
                                        $error_message = 'Erreur lors de l\'envoi de l\'email: ' . $error;
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                    }
                    
                    // Sauvegarder le message dans tous les cas (pour traçabilité)
                    try {
                        $result = \DB::Insert('tss_contact_messages', [
                            'user_id' => App::getCurrentUser()->id,
                            'username' => App::getCurrentUser()->username,
                            'user_email' => App::getCurrentUser()->email,
                            'subject' => $subject,
                            'message' => $message,
                            'followup' => $followup,
                            'user_agent' => $useragent,
                            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                            'created_date' => date("Y-m-d H:i:s")
                        ]);
                        
                        if ($result) {
                            $message_sent = true;
                            error_log("Contact Form: Message saved successfully to database with ID " . \DB::$insert_id);
                            
                            if ($email_sent) {
                                error_log("Contact Form: Both email sent and message saved successfully");
                            } else {
                                error_log("Contact Form: Message saved but email failed to send");
                            }
                        } else {
                            $error_message = 'Erreur lors de la sauvegarde du message.';
                            error_log("Contact Form Error: Failed to save message to database");
                        }
                    } catch (Exception $e) {
                        $error_message = 'Erreur lors de la sauvegarde du message: ' . $e->getMessage();
                    }
                }
            }
        }
    }

    include  __DIR__.'/templates/main.php';
    
    include  __DIR__.'/pages/contact.php';