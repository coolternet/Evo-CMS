-- Index pour optimiser les performances du module Evo-TSM
-- À exécuter une seule fois après l'installation du module

-- Index sur les colonnes les plus utilisées dans les requêtes
CREATE INDEX IF NOT EXISTS idx_tss_ticket_assignation ON tss_ticket(assignation);
CREATE INDEX IF NOT EXISTS idx_tss_ticket_close_date ON tss_ticket(close_date);
CREATE INDEX IF NOT EXISTS idx_tss_ticket_level ON tss_ticket(level);
CREATE INDEX IF NOT EXISTS idx_tss_ticket_sid ON tss_ticket(sid);
CREATE INDEX IF NOT EXISTS idx_tss_ticket_create_date ON tss_ticket(create_date);

-- Index sur les tables de contenu
CREATE INDEX IF NOT EXISTS idx_tss_rates_tid ON tss_rates(tid);
CREATE INDEX IF NOT EXISTS idx_tss_rates_score ON tss_rates(score);
CREATE INDEX IF NOT EXISTS idx_tss_content_tid ON tss_content(tid);
CREATE INDEX IF NOT EXISTS idx_tss_content_send_date ON tss_content(send_date);

-- Index sur les messages de contact
CREATE INDEX IF NOT EXISTS idx_tss_contact_messages_created_date ON tss_contact_messages(created_date);
CREATE INDEX IF NOT EXISTS idx_tss_contact_messages_user_id ON tss_contact_messages(user_id);

-- Index composites pour les requêtes complexes
CREATE INDEX IF NOT EXISTS idx_ticket_assignation_close ON tss_ticket(assignation, close_date);
CREATE INDEX IF NOT EXISTS idx_ticket_level_close ON tss_ticket(level, close_date);
CREATE INDEX IF NOT EXISTS idx_ticket_sid_create ON tss_ticket(sid, create_date);

-- Index pour les requêtes d'administration
CREATE INDEX IF NOT EXISTS idx_tss_admin_notes_tid ON tss_admin_notes(tid);
CREATE INDEX IF NOT EXISTS idx_tss_admin_notes_assignation ON tss_admin_notes(assignation);
