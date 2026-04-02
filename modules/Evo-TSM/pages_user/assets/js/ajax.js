$(document).ready(function(){

    /**
     * Fonctions utilitaires pour la gestion des boutons
     */
    function disableButton($button, loadingText) {
        $button.prop('disabled', true).text(loadingText);
    }
    
    function enableButton($button, originalText) {
        $button.prop('disabled', false).text(originalText);
    }
    
    function safeRedirect(url, delay) {
        delay = delay || 100;
        console.log('Redirection vers:', url); // Debug
        setTimeout(function() {
            try {
                window.location.href = url;
            } catch (e) {
                console.error('Erreur de redirection:', e);
                // Fallback
                window.location = url;
            }
        }, delay);
    }

    function ajax_call(type, data, callback) {
        data.csrf = csrf;
        $.ajax({
            type: type,
            dataType: 'json',
            url: "index.php?p=Evo-TSM/ajax",
            data: data,
            success: function(response) {
                // Vérifier que la réponse est valide
                if (response && typeof response === 'object') {
                    callback(response);
                } else {
                    callback({success: false, error: "Réponse invalide du serveur"});
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', status, error);
                callback({
                    success: false, 
                    error: "Erreur de connexion: " + (error || "Erreur inconnue")
                });
            }
        });
    }

    function ajax_get(data, callback) {
        ajax_call("GET", data, callback);
    }

    function ajax_post(data, callback) {
        ajax_call("POST", data, callback);
    }

    /*
     *  Create a new ticket
     */

    $("button[name=tcreate]").on('click', function(e){
        e.preventDefault(); // Empêcher la soumission par défaut
        
        var $button = $(this);
        var sujet = $('input[name=tc_subject]').val();
        var content = $('textarea[name=tc_comment]').val();

        // Validation manuelle avant de désactiver le bouton
        if (!sujet || sujet.length < 5) {
            alert("Le sujet doit contenir au moins 5 caractères");
            return;
        }
        if (!content || content.length < 5) {
            alert("La description doit contenir au moins 5 caractères");
            return;
        }

        // Désactiver le bouton pour éviter les soumissions multiples
        disableButton($button, 'Création en cours...');

        // Appel AJAX direct sans validation jQuery
        ajax_post({
            action  : 'create_new_ticket_btn',
            subject : sujet,
            content : content
        },function(data){
            console.log('Réponse AJAX:', data); // Debug
            if(data.success && data.id){
                // Redirection sécurisée
                safeRedirect("?p=support/view&id=" + data.id);
            }else{
                // Réactiver le bouton en cas d'erreur
                enableButton($button, 'Créer un ticket');
                alert(data.error || "Erreur lors de la création du ticket");
            }
        });
    });

    /*
     *  Answer to a ticket
     */

    $("button[name=ticket_comment]").on('click', function(e){
        e.preventDefault(); // Empêcher la soumission par défaut
        
        var $button = $(this);
        var $msg = $('textarea[name=comment]').val();
        var $tid = $('div[id=conversation]').attr("data-id");
        
        // Validation manuelle
        if (!$msg || $msg.length < 1) {
            alert("Veuillez composer votre message");
            return;
        }
        if (!$tid) {
            alert("ID du ticket non trouvé");
            return;
        }
        
        // Désactiver le bouton pour éviter les soumissions multiples
        disableButton($button, 'Envoi en cours...');
        
        // Appel AJAX direct
        ajax_post({
            action: 'send_answer_btn',
            comment   : $msg,
            ticket_id : $tid
        },function(data){
            if(data.success){
                // Redirection sécurisée
                safeRedirect("?p=support/view&id=" + $tid);
            }else{
                // Réactiver le bouton en cas d'erreur
                enableButton($button, 'Répondre');
                console.log(data);
                alert(data.error || "Erreur lors de l'envoi de la réponse");
            }
        });
    });

    /*
     *  Close a ticket
     */

    $("button[name=ticket_close]").on('click', function(){
        var $tid = $('div[id=conversation]').attr("data-id");
        ajax_post({
                action: 'close_ticket_btn',
                ticket_id : $tid
            },function(data){
                if(data.success){
                    $("div[id=commentaire]").remove();
                    $("span[id=ETA]").removeClass('badge-success').addClass('badge-danger').text('Fermé')
                }else{
                    console.log(data);
                }
        });

    });

    /*
     *  Delete ticket from table and set 0 into DB
     */

    $("button[name=delete_ticket]").on('click', function(){
        var $tid = $(this).parents('tr').attr("data-id");
        ajax_post({
                action: 'delete_ticket_btn',
                ticket_id : $tid
            },function(data){
                if(data.success){
                    $("tr[data-id=" + $tid + "]").remove();
                }else{
                    console.log(data);
                }
        });
    });

});