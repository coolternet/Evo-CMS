/**
 * Evo-TSM JavaScript Module Unifié
 * ================================
 * 
 * Module JavaScript complet pour la gestion de l'interface utilisateur,
 * des actions sur les tickets et de la gestion du tableau.
 * 
 * @author EvoCMS Team
 * @version 2.0.0
 * @since 2024
 */

'use strict';

// ============================================================================
// MODULE PRINCIPAL EvoTSM
// ============================================================================

var EvoTSM = {
    /**
     * Initialiser le module Evo-TSM
     * @description Point d'entrée principal pour l'initialisation de l'interface
     * @returns {void}
     */
    init: function() {
        this.initCacheStatus();
        this.initChart();
        this.initRatingSystem();
        this.initTicketsTable();
    },

    /**
     * Initialiser le statut du cache
     * @description Met à jour l'interface avec les données du cache
     * @returns {void}
     */
    initCacheStatus: function() {
        const cacheStatus = document.getElementById('cache-status');
        const cacheEntries = document.getElementById('cache-entries');
        const cacheStatusBadge = document.getElementById('cache-status-badge');
        const cachePerformance = document.getElementById('cache-performance');
        
        // Vérifier que les données du cache sont disponibles
        if (typeof window.cacheData === 'undefined') {
            console.warn('EvoTSM: Données du cache non disponibles');
            return;
        }
        
        const data = window.cacheData;
        
        // Mettre à jour l'alerte principale
        if (cacheStatus) {
            cacheStatus.innerHTML = `<i class="${data.icon} text-${data.color}"></i> ${data.message}`;
            const alertElement = cacheStatus.parentElement.parentElement;
            alertElement.className = `alert alert-${data.color} d-flex justify-content-between align-items-center`;
        }
        
        // Mettre à jour le widget de cache
        if (cacheEntries) {
            cacheEntries.textContent = data.entries || 0;
        }
        
        if (cacheStatusBadge) {
            cacheStatusBadge.textContent = data.status === 'active' ? 'Actif' : 'Inactif';
            cacheStatusBadge.className = `text-${data.color}`;
        }
        
        if (cachePerformance) {
            cachePerformance.textContent = data.status === 'active' ? 'Optimisé' : 'Standard';
            cachePerformance.className = `text-${data.color}`;
        }
    },

    /**
     * Initialiser le graphique Chart.js
     * @description Crée un graphique en donut pour les statistiques des tickets
     * @returns {void}
     */
    initChart: function() {
        // Vérifier que Chart.js est disponible
        if (typeof Chart === 'undefined') {
            console.warn('EvoTSM: Chart.js non disponible');
            return;
        }
        
        const ctx = document.getElementById('myChart');
        if (!ctx) {
            // Pas d'erreur si le canvas n'existe pas - c'est normal sur certaines pages
            return;
        }
        
        // Charger les données du graphique via AJAX
        this.loadChartData().then(chartData => {
            this.createChart(ctx, chartData);
        }).catch(error => {
            console.error('EvoTSM: Erreur lors du chargement des données du graphique:', error);
            // Utiliser les données par défaut si disponibles
            if (typeof window.ticketChartData !== 'undefined') {
                this.createChart(ctx, window.ticketChartData);
            } else {
                console.warn('EvoTSM: Aucune donnée de graphique disponible');
            }
        });
    },

    /**
     * Charger les données du graphique via AJAX
     * @returns {Promise}
     */
    loadChartData: function() {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('action', 'get_chart_data');

            fetch('/modules/Evo-TSM/ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    resolve(data);
                } else {
                    throw new Error(data.error || 'Erreur lors du chargement des données');
                }
            })
            .catch(reject);
        });
    },

    /**
     * Créer le graphique avec les données fournies
     * @param {HTMLElement} ctx - Élément canvas
     * @param {Object} chartData - Données du graphique
     */
    createChart: function(ctx, chartData) {
        try {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Ouverts', 'Fermés', 'Non assignés'],
                    datasets: [{
                        data: [
                            chartData.open || 0,
                            chartData.close || 0,
                            chartData.unassigned || 0
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        title: {
                            display: true,
                            text: 'Statistiques des Tickets',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    },
                    elements: {
                        arc: {
                            borderWidth: 2
                        }
                    }
                }
            });
        } catch (error) {
            console.error('EvoTSM: Erreur lors de la création du graphique', error);
        }
    },

    /**
     * Initialiser le système de notation
     * @description Initialise les étoiles de notation si jQuery et le plugin rating sont disponibles
     * @returns {void}
     */
    initRatingSystem: function() {
        // Vérifier que jQuery et le plugin rating sont disponibles
        if (typeof $ === 'undefined' || typeof $.fn.rating === 'undefined') {
            console.warn('EvoTSM: jQuery ou plugin rating non disponible');
            return;
        }
        
        // Initialiser les étoiles de notation avec retry
        function initRating() {
            try {
                $('.rating').rating({
                    showClear: false,
                    showCaption: false,
                    size: 'sm'
                });
                console.log('EvoTSM: Système de notation initialisé');
            } catch (error) {
                console.error('EvoTSM: Erreur lors de l\'initialisation du système de notation', error);
            }
        }
        
        // Essayer d'initialiser immédiatement
        initRating();
        
        // Retry après un délai si l'initialisation a échoué
        setTimeout(function() {
            if ($('.rating').length > 0 && !$('.rating').hasClass('rating-initialized')) {
                initRating();
            }
        }, 500);
    },

    /**
     * Initialiser le gestionnaire de tableau des tickets
     * @description Initialise le système de gestion du tableau des tickets
     * @returns {void}
     */
    initTicketsTable: function() {
        if (typeof window.ticketsTableConfig !== 'undefined') {
            TicketsTable.init(window.ticketsTableConfig.currentState);
        } else {
            TicketsTable.init('opened');
        }
    },

    /**
     * Rafraîchir le statut du cache
     * @description Met à jour l'interface avec les dernières données du cache
     * @returns {void}
     */
    refreshCacheStatus: function() {
        console.log('EvoTSM: Rafraîchissement du statut du cache');
        this.initCacheStatus();
    },

    /**
     * Obtenir les statistiques du cache
     * @description Récupère les données de configuration du cache
     * @returns {Object|null} Données du cache ou null si non disponibles
     */
    getCacheStats: function() {
        if (typeof window.cacheData !== 'undefined') {
            return window.cacheData;
        }
        return null;
    },

    /**
     * Vérifier si le cache est actif
     * @description Détermine si le système de cache est opérationnel
     * @returns {boolean} true si le cache est actif, false sinon
     */
    isCacheActive: function() {
        const data = this.getCacheStats();
        return data && data.status === 'active';
    },

    /**
     * Initialisation de fallback si les données ne sont pas disponibles
     * @description Affiche un message d'erreur si les données du cache sont indisponibles
     * @returns {void}
     */
    initCacheStatusFallback: function() {
        const cacheStatus = document.getElementById('cache-status');
        if (cacheStatus) {
            cacheStatus.innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i> Erreur de vérification';
            const alertElement = cacheStatus.parentElement.parentElement;
            alertElement.className = 'alert alert-warning d-flex justify-content-between align-items-center';
        }
        console.warn('EvoTSM: Mode fallback activé - données du cache non disponibles');
    }
};

// ============================================================================
// GESTIONNAIRE DE TABLEAU DES TICKETS
// ============================================================================

var TicketsTable = {
    currentState: 'opened',
    refreshInterval: null,
    isLoading: false,
    lastUpdate: null,
    isRefreshing: false,

    /**
     * Initialiser le gestionnaire de tableau
     * @param {string} initialState - État initial des tickets
     */
    init: function(initialState = 'opened') {
        this.currentState = initialState;
        this.setupAutoRefresh();
        this.loadTicketsData();
        console.log('EvoTSM: TicketsTable initialisé pour l\'état:', initialState);
    },

    /**
     * Configurer l'actualisation automatique
     */
    setupAutoRefresh: function() {
        // Nettoyer l'intervalle existant
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }

        // Actualiser toutes les 30 secondes pour un affichage plus dynamique
        this.refreshInterval = setInterval(() => {
            if (!this.isRefreshing && !this.isLoading) {
                this.loadTicketsData();
            }
        }, 30000); // 30 secondes

        console.log('EvoTSM: Actualisation automatique configurée (30s)');
    },

    /**
     * Démarrer l'actualisation automatique
     */
    startAutoRefresh: function() {
        this.setupAutoRefresh();
        console.log('EvoTSM: Actualisation automatique démarrée');
    },

    /**
     * Arrêter l'actualisation automatique
     */
    stopAutoRefresh: function() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
            console.log('EvoTSM: Actualisation automatique arrêtée');
        }
    },

    /**
     * Basculer l'actualisation automatique
     */
    toggleAutoRefresh: function() {
        if (this.refreshInterval) {
            this.stopAutoRefresh();
        } else {
            this.startAutoRefresh();
        }
    },

    /**
     * Charger les données des tickets via AJAX
     */
    loadTicketsData: function() {
        if (this.isRefreshing) {
            console.log('EvoTSM: Actualisation déjà en cours, ignorée');
            return;
        }

        this.isRefreshing = true;
        this.showLoadingIndicator();

        const formData = new FormData();
        formData.append('action', 'get_tickets_data');
        formData.append('state', this.currentState);

        fetch('/modules/Evo-TSM/direct-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                this.updateTable(data.tickets);
                console.log('EvoTSM: Données des tickets mises à jour');
            } else {
                throw new Error(data.error || 'Erreur lors du chargement des données');
            }
        })
        .catch(error => {
            console.error('EvoTSM: Erreur lors du chargement des tickets:', error);
            this.showError('Erreur lors du chargement des données');
        })
        .finally(() => {
            this.isRefreshing = false;
            this.hideLoadingIndicator();
        });
    },

    /**
     * Mettre à jour le tableau avec les nouvelles données
     * @param {Array} tickets - Données des tickets
     */
    updateTable: function(tickets) {
        const tbody = document.querySelector('#tickets-table tbody');
        if (!tbody) return;

        // Animation de transition
        tbody.style.opacity = '0.7';
        tbody.style.transition = 'opacity 0.3s ease';

        // Vider le tableau
        tbody.innerHTML = '';

        if (tickets.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td colspan="6">
                    <div class="alert alert-primary text-center">${this.getNoTicketsMessage()}</div>
                </td>
            `;
            tbody.appendChild(row);
        } else {
            // Ajouter les nouvelles lignes avec animation
            tickets.forEach((ticket, index) => {
                const row = this.createTicketRow(ticket);
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                row.style.transition = 'all 0.3s ease';
                tbody.appendChild(row);
                
                // Animation d'apparition décalée
                setTimeout(() => {
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 50);
            });
        }

        // Restaurer l'opacité
        setTimeout(() => {
            tbody.style.opacity = '1';
        }, 300);
    },

    /**
     * Créer une ligne de ticket
     * @param {Object} ticket - Données du ticket
     * @returns {HTMLElement} Ligne du tableau
     */
    createTicketRow: function(ticket) {
        const row = document.createElement('tr');
        
        const state = this.currentState;
        const dateField = state === 'closed' ? 'close_date' : 'create_date';
        const dateValue = ticket[dateField] || 'N/A';
        
        row.innerHTML = `
            <th><input type="checkbox" value="${ticket.id}" class="ticket-checkbox"></th>
            <td><a href="/admin/?page=user_view&id=${ticket.sid}" target="_blank">${ticket.account}</a></td>
            <td><a href="?p=Evo-TSM/view&id=${ticket.id}">${ticket.subject || 'Sans sujet'}</a></td>
            <td>${dateValue}</td>
            <td>${ticket.moderator || 'Non assigné'}</td>
            <td>
                <div class="btn-group" role="group">
                    <a href="?p=Evo-TSM/view&id=${ticket.id}" class="btn btn-sm btn-outline-primary" title="Voir le ticket">
                        <i class="fa fa-eye"></i>
                    </a>
                    ${this.getActionButtons(ticket.id, state)}
                </div>
            </td>
        `;
        
        return row;
    },

    /**
     * Obtenir les boutons d'action selon l'état
     * @param {number} ticketId - ID du ticket
     * @param {string} state - État du ticket
     * @returns {string} HTML des boutons
     */
    getActionButtons: function(ticketId, state) {
        if (state === 'closed') {
            return `
                <button class="btn btn-sm btn-outline-warning" onclick="reopenTicket(${ticketId})" title="Rouvrir le ticket">
                    <i class="fa fa-unlock"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTicket(${ticketId})" title="Supprimer le ticket">
                    <i class="fa fa-trash"></i>
                </button>
            `;
        } else {
            return `
                <button class="btn btn-sm btn-outline-success" onclick="closeTicket(${ticketId})" title="Fermer le ticket">
                    <i class="fa fa-lock"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTicket(${ticketId})" title="Supprimer le ticket">
                    <i class="fa fa-trash"></i>
                </button>
            `;
        }
    },

    /**
     * Obtenir le message "Aucun ticket"
     * @returns {string} Message approprié
     */
    getNoTicketsMessage: function() {
        const messages = {
            'opened': 'Aucun ticket ouvert',
            'closed': 'Aucun ticket fermé',
            'critical': 'Aucun ticket critique',
            'unassigned': 'Aucun ticket non assigné'
        };
        return messages[this.currentState] || 'Aucun ticket trouvé';
    },


    /**
     * Mettre à jour l'heure de dernière actualisation
     */
    updateLastRefreshTime: function() {
        if (!this.lastUpdate) return;
        
        const timeElement = document.querySelector('.last-refresh-time');
        if (timeElement) {
            const timeString = this.lastUpdate.toLocaleTimeString();
            timeElement.textContent = `Dernière mise à jour: ${timeString}`;
        } else {
            // Créer l'élément s'il n'existe pas
            const header = document.querySelector('.card-header .d-flex');
            if (header) {
                const timeElement = document.createElement('small');
                timeElement.className = 'last-refresh-time text-muted';
                timeElement.textContent = `Dernière mise à jour: ${this.lastUpdate.toLocaleTimeString()}`;
                header.appendChild(timeElement);
            }
        }
    },



    /**
     * Afficher l'indicateur de chargement
     */
    showLoadingIndicator: function() {
        const tbody = document.querySelector('#tickets-table tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Chargement...</span>
                        </div>
                        <p class="mt-2">Chargement des tickets...</p>
                    </td>
                </tr>
            `;
        }
    },

    /**
     * Masquer l'indicateur de chargement
     */
    hideLoadingIndicator: function() {
        // L'indicateur sera remplacé par les données
    },

    /**
     * Afficher un message d'erreur
     * @param {string} message - Message d'erreur
     */
    showError: function(message) {
        const tbody = document.querySelector('#tickets-table tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6">
                        <div class="alert alert-danger text-center">
                            <i class="fa fa-exclamation-triangle"></i> ${message}
                            <button class="btn btn-sm btn-outline-danger ms-2" onclick="location.reload()">
                                <i class="fa fa-refresh"></i> Réessayer
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
        
        // Afficher une notification toast
        this.showNotification(message, 'error');
    },

    /**
     * Afficher une notification
     * @param {string} message - Message à afficher
     * @param {string} type - Type de notification (success, error, info, warning)
     */
    showNotification: function(message, type = 'info') {
        // Créer l'élément de notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        const icon = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-triangle',
            'info': 'fa-info-circle',
            'warning': 'fa-exclamation-circle'
        }[type] || 'fa-info-circle';
        
        notification.innerHTML = `
            <i class="fa ${icon}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Supprimer automatiquement après 5 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    },

    /**
     * Démarrer l'actualisation automatique
     */
    startAutoRefresh: function() {
        this.setupAutoRefresh();
        console.log('EvoTSM: Actualisation automatique démarrée');
    },

    /**
     * Arrêter l'actualisation automatique
     */
    stopAutoRefresh: function() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
        console.log('EvoTSM: Actualisation automatique arrêtée');
    }
};

// ============================================================================
// ACTIONS SUR LES TICKETS
// ============================================================================

/**
 * Fermer un ticket
 * @param {number} ticketId - ID du ticket à fermer
 */
function closeTicket(ticketId) {
    if (confirm('Êtes-vous sûr de vouloir fermer ce ticket ?')) {
        performTicketAction('close_ticket_btn', ticketId, 'fermeture');
    }
}

/**
 * Supprimer un ticket
 * @param {number} ticketId - ID du ticket à supprimer
 */
function deleteTicket(ticketId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce ticket ? Cette action est irréversible.')) {
        performTicketAction('delete_ticket_btn', ticketId, 'suppression');
    }
}

/**
 * Rouvrir un ticket
 * @param {number} ticketId - ID du ticket à rouvrir
 */
function reopenTicket(ticketId) {
    if (confirm('Êtes-vous sûr de vouloir rouvrir ce ticket ?')) {
        performTicketAction('reopen_ticket_btn', ticketId, 'réouverture');
    }
}

/**
 * Marquer un ticket comme résolu
 * @param {number} ticketId - ID du ticket à marquer
 */
function markTicketSolved(ticketId) {
    if (confirm('Marquer ce ticket comme résolu ?')) {
        performTicketAction('mark_solved_btn', ticketId, 'marquage comme résolu');
    }
}

/**
 * Marquer un ticket comme non résolu
 * @param {number} ticketId - ID du ticket à marquer
 */
function markTicketUnsolved(ticketId) {
    if (confirm('Marquer ce ticket comme non résolu ?')) {
        performTicketAction('mark_unsolved_btn', ticketId, 'marquage comme non résolu');
    }
}

/**
 * Marquer un ticket comme critique
 * @param {number} ticketId - ID du ticket à marquer
 */
function markTicketCritical(ticketId) {
    if (confirm('Marquer ce ticket comme critique ?')) {
        performTicketAction('mark_critical_btn', ticketId, 'marquage comme critique');
    }
}

/**
 * Marquer un ticket comme normal
 * @param {number} ticketId - ID du ticket à marquer
 */
function markTicketNormal(ticketId) {
    if (confirm('Marquer ce ticket comme normal ?')) {
        performTicketAction('mark_normal_btn', ticketId, 'marquage comme normal');
    }
}

/**
 * Effectuer une action sur un ticket via AJAX
 * @param {string} action - Action à effectuer
 * @param {number} ticketId - ID du ticket
 * @param {string} actionName - Nom de l'action pour l'affichage
 */
function performTicketAction(action, ticketId, actionName) {
    const button = event.currentTarget;
    disableButton(button, actionName + '...');

    const formData = new FormData();
    formData.append('action', action);
    formData.append('ticket_id', ticketId);

    fetch('/modules/Evo-TSM/ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('success', `Ticket ${actionName} avec succès`);
            
            // Actions spécifiques selon le type d'action
            if (action === 'delete_ticket_btn') {
                removeTicketRow(ticketId);
            } else if (action === 'close_ticket_btn') {
                updateTicketRowState(ticketId, 'closed');
            } else {
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        } else {
            throw new Error(data.error || `Erreur lors de la ${actionName} du ticket`);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', `Erreur lors de la ${actionName} du ticket: ${error.message}`);
        enableButton(button);
    });
}

/**
 * Désactiver un bouton pendant l'action
 * @param {HTMLElement} button - Bouton à désactiver
 * @param {string} loadingText - Texte à afficher pendant le chargement
 */
function disableButton(button, loadingText) {
    button.setAttribute('data-original-html', button.innerHTML);
    button.disabled = true;
    button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${loadingText}`;
}

/**
 * Réactiver un bouton après l'action
 * @param {HTMLElement} button - Bouton à réactiver
 */
function enableButton(button) {
    button.disabled = false;
    button.innerHTML = button.getAttribute('data-original-html') || button.innerHTML;
}

/**
 * Supprimer une ligne du tableau par ID de ticket
 * @param {number} ticketId - ID du ticket à supprimer
 */
function removeTicketRow(ticketId) {
    const tbody = document.querySelector('#tickets-table tbody');
    if (!tbody) return;

    const rows = tbody.querySelectorAll('tr');
    rows.forEach(row => {
        const checkbox = row.querySelector(`input[value="${ticketId}"]`);
        if (checkbox) {
            row.style.transition = 'opacity 0.3s ease';
            row.style.opacity = '0';
            setTimeout(() => {
                row.remove();
                updateTicketCounter();
            }, 300);
        }
    });
}

/**
 * Mettre à jour l'état d'une ligne de ticket
 * @param {number} ticketId - ID du ticket
 * @param {string} newState - Nouvel état
 */
function updateTicketRowState(ticketId, newState) {
    const tbody = document.querySelector('#tickets-table tbody');
    if (!tbody) return;

    const rows = tbody.querySelectorAll('tr');
    rows.forEach(row => {
        const checkbox = row.querySelector(`input[value="${ticketId}"]`);
        if (checkbox) {
            const currentState = getCurrentTicketState();
            if (currentState === 'opened' && newState === 'closed') {
                removeTicketRow(ticketId);
            } else {
                updateRowState(row, newState);
            }
        }
    });
}

/**
 * Mettre à jour l'état d'une ligne
 * @param {HTMLElement} row - Ligne du tableau
 * @param {string} state - Nouvel état
 */
function updateRowState(row, state) {
    const stateCell = row.cells[4];
    if (stateCell) {
        stateCell.innerHTML = getStateText(state);
    }

    const actionCell = row.cells[5];
    if (actionCell) {
        const ticketId = row.querySelector('input[type="checkbox"]').value;
        actionCell.innerHTML = getActionButtons(ticketId, state);
    }
}

/**
 * Obtenir le texte de l'état
 * @param {string} state - État du ticket
 * @returns {string} - Texte de l'état
 */
function getStateText(state) {
    const states = {
        'opened': 'Answered',
        'closed': 'Closed',
        'critical': '<span class="badge badge-danger">Critical</span>',
        'unassigned': 'Unassigned'
    };
    return states[state] || 'Unknown';
}

/**
 * Obtenir les boutons d'action selon l'état
 * @param {number} ticketId - ID du ticket
 * @param {string} state - État du ticket
 * @returns {string} - HTML des boutons
 */
function getActionButtons(ticketId, state) {
    if (state === 'closed') {
        return `
            <div class="btn-group" role="group">
                <a href="?p=Evo-TSM/view&id=${ticketId}" class="btn btn-sm btn-outline-primary" title="Voir le ticket">
                    <i class="fa fa-eye"></i>
                </a>
                <button class="btn btn-sm btn-outline-warning" onclick="reopenTicket(${ticketId})" title="Rouvrir le ticket">
                    <i class="fa fa-unlock"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTicket(${ticketId})" title="Supprimer le ticket">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;
    } else {
        return `
            <div class="btn-group" role="group">
                <a href="?p=Evo-TSM/view&id=${ticketId}" class="btn btn-sm btn-outline-primary" title="Voir le ticket">
                    <i class="fa fa-eye"></i>
                </a>
                <button class="btn btn-sm btn-outline-success" onclick="closeTicket(${ticketId})" title="Fermer le ticket">
                    <i class="fa fa-lock"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTicket(${ticketId})" title="Supprimer le ticket">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;
    }
}

/**
 * Obtenir l'état actuel des tickets affichés
 * @returns {string} - État actuel
 */
function getCurrentTicketState() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('state') || 'opened';
}


/**
 * Afficher une notification
 * @param {string} type - Type de notification (success, error, warning, info)
 * @param {string} message - Message à afficher
 */
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    notification.innerHTML = `
        <i class="fa fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-supprimer après 5 secondes
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// ============================================================================
// INITIALISATION AUTOMATIQUE
// ============================================================================

/**
 * Auto-initialisation du module Evo-TSM
 * @description Initialise automatiquement le module au chargement de la page
 * @returns {void}
 */
function autoInit() {
    if (typeof window.cacheData !== 'undefined') {
        EvoTSM.init();
    } else {
        setTimeout(function() {
            if (typeof window.cacheData !== 'undefined') {
                EvoTSM.init();
            } else {
                console.warn('EvoTSM: Données du cache non disponibles après délai');
                EvoTSM.initCacheStatusFallback();
            }
        }, 100);
    }
}

// Initialisation selon l'état du DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoInit);
} else {
    autoInit();
}

// Compatibilité avec l'ancien code jQuery
$(document).ready(function() {
    if (typeof EvoTSM !== 'undefined' && EvoTSM.initRatingSystem) {
        EvoTSM.initRatingSystem();
    }
});