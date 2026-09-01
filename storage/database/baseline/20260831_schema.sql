-- MOVES structural baseline (schema only; no application data).
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `access_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `group_name` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_access_permissions_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `access_role_permissions` (
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_access_rp_permission` (`permission_id`),
  CONSTRAINT `fk_access_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `access_permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_access_rp_role` FOREIGN KEY (`role_id`) REFERENCES `access_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `access_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `level` int(11) NOT NULL DEFAULT 10,
  `description` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_access_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `access_user_overrides` (
  `user_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  `effect` enum('allow','deny') NOT NULL,
  `assigned_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `fk_access_uo_permission` (`permission_id`),
  CONSTRAINT `fk_access_uo_permission` FOREIGN KEY (`permission_id`) REFERENCES `access_permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_access_uo_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `access_user_roles` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `assigned_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  KEY `idx_access_user_role` (`role_id`),
  CONSTRAINT `fk_access_ur_role` FOREIGN KEY (`role_id`) REFERENCES `access_roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_access_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `app_accountable` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sub_of` int(11) unsigned NOT NULL,
  `users_id` int(11) unsigned NOT NULL,
  `function` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `administration` date DEFAULT NULL,
  `resignation` date DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'registered' COMMENT 'registered, confirmed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_app_units_app_condominium` (`users_id`,`sub_of`) USING BTREE,
  KEY `FK_app_accountable_app_condominium` (`sub_of`),
  FULLTEXT KEY `fulltext` (`function`),
  CONSTRAINT `FK_app_accountable_app_condominium` FOREIGN KEY (`sub_of`) REFERENCES `app_condominium` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_app_accountable_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `app_address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `corporations_id` int(11) unsigned DEFAULT NULL,
  `condominium_id` int(11) unsigned DEFAULT NULL,
  `users_id` int(11) unsigned DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `code` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `city` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `state` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `district` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `street` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `number` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `complement` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'main' COMMENT 'main, leading',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_app_address_users` (`users_id`) USING BTREE,
  KEY `FK_app_address_app_enterprises` (`condominium_id`) USING BTREE,
  KEY `Index 2` (`corporations_id`,`condominium_id`,`users_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `app_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sub_of` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(15) NOT NULL DEFAULT '',
  `order_by` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sub_of` (`sub_of`) USING BTREE,
  CONSTRAINT `app_categories_ibfk_1` FOREIGN KEY (`sub_of`) REFERENCES `app_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `app_condominium` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sub_of` int(11) unsigned NOT NULL,
  `registry` int(11) unsigned DEFAULT NULL,
  `condominium_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `fantasy_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `document` varchar(14) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `photo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `datebirth` date DEFAULT NULL,
  `obs` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `phone` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `fax` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `whatsapp` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `cell` varchar(11) DEFAULT NULL,
  `send` int(11) NOT NULL DEFAULT 1,
  `despatch` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'all' COMMENT 'e-mail, whatsapp, telegram, carta, all',
  `status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'registered' COMMENT 'registered, confirmed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sub_of` (`sub_of`) USING BTREE,
  FULLTEXT KEY `fulltext` (`fantasy_name`,`email`,`condominium_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `app_corporations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `registry` int(11) unsigned DEFAULT NULL,
  `corporation_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `fantasy_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `document` varchar(14) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `photo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `datebirth` date DEFAULT NULL,
  `phone` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `phone_cell` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `send` int(11) NOT NULL DEFAULT 1,
  `despatch` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'all' COMMENT 'e-mail, whatsapp, telegram, carta, all',
  `status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'registered, confirmed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  FULLTEXT KEY `fulltext` (`corporation_name`,`fantasy_name`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `app_credit_cards` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `brand` varchar(20) NOT NULL DEFAULT '',
  `last_digits` varchar(11) NOT NULL DEFAULT '',
  `cvv` varchar(11) NOT NULL DEFAULT '',
  `hash` varchar(255) NOT NULL DEFAULT '',
  `status` varchar(255) DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `credit_cards_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `app_invoices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(11) unsigned NOT NULL,
  `unitis_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `wallet_id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL,
  `invoice_of` int(11) unsigned DEFAULT NULL,
  `deal_of` int(11) unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(15) NOT NULL DEFAULT '',
  `value` decimal(10,2) NOT NULL,
  `currency` varchar(5) NOT NULL DEFAULT 'BRL',
  `due_at` date NOT NULL,
  `paid_at` date NOT NULL,
  `repeat_when` varchar(10) NOT NULL DEFAULT '',
  `period` varchar(10) NOT NULL DEFAULT 'month',
  `enrollments` int(11) DEFAULT NULL,
  `enrollment_of` int(11) DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'unpaid' COMMENT 'unpaid, paid, deal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `app_user` (`user_id`),
  KEY `app_wallet` (`wallet_id`),
  KEY `app_category` (`category_id`),
  KEY `app_condominium` (`condominium_id`) USING BTREE,
  KEY `app_units` (`unitis_id`) USING BTREE,
  KEY `app_invoice` (`invoice_of`,`deal_of`) USING BTREE,
  KEY `app_invoice_deal` (`deal_of`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `app_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `incident_id` varchar(32) DEFAULT NULL,
  `request_id` varchar(32) DEFAULT NULL,
  `corporations_id` int(11) unsigned DEFAULT NULL,
  `condominium_id` int(11) unsigned DEFAULT NULL,
  `users_id` int(11) unsigned DEFAULT NULL,
  `level` enum('debug','info','notice','warning','error','critical','alert','emergency') NOT NULL DEFAULT 'info',
  `channel` varchar(80) NOT NULL DEFAULT 'application',
  `event_type` varchar(100) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `msg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception_class` varchar(255) DEFAULT NULL,
  `file` varchar(500) DEFAULT NULL,
  `line` int(10) unsigned DEFAULT NULL,
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `context_json` longtext DEFAULT NULL,
  `trace` longtext DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `fingerprint` char(64) DEFAULT NULL,
  `occurrences` int(10) unsigned NOT NULL DEFAULT 1,
  `status` enum('open','resolved','ignored') NOT NULL DEFAULT 'open',
  `first_seen_at` timestamp NULL DEFAULT current_timestamp(),
  `last_seen_at` timestamp NULL DEFAULT current_timestamp(),
  `resolved_by` int(10) unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_app_historic_users` (`users_id`) USING BTREE,
  KEY `Corporations` (`corporations_id`) USING BTREE,
  KEY `enterprises` (`condominium_id`) USING BTREE,
  KEY `idx_app_log_level_status` (`level`,`status`),
  KEY `idx_app_log_channel` (`channel`),
  KEY `idx_app_log_incident` (`incident_id`),
  KEY `idx_app_log_fingerprint` (`fingerprint`),
  KEY `idx_app_log_last_seen` (`last_seen_at`),
  KEY `fk_app_log_resolver` (`resolved_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;

CREATE TABLE `app_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `card_id` int(11) unsigned DEFAULT NULL,
  `subscription_id` int(11) unsigned DEFAULT NULL,
  `transaction` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `orders_user` (`user_id`),
  KEY `orders_credit_card` (`card_id`),
  KEY `orders_subscription` (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `app_owner` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sub_of` int(11) unsigned DEFAULT NULL,
  `units_id` int(11) unsigned DEFAULT NULL,
  `users_id` int(11) unsigned DEFAULT NULL,
  `owner` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'owner' COMMENT 'owner, tenant',
  `status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'confirmed' COMMENT ' confirmed, registered',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `Index 2` (`sub_of`,`units_id`,`users_id`) USING BTREE,
  KEY `FK_app_owner_app_units` (`units_id`),
  KEY `FK_app_owner_users` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `app_plans` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `period` varchar(11) NOT NULL DEFAULT '',
  `period_str` varchar(11) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL,
  `status` varchar(11) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `app_session` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `corporations_id` int(11) unsigned NOT NULL,
  `condominium_id` int(11) unsigned DEFAULT NULL,
  `users_id` int(11) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `corporations` (`corporations_id`) USING BTREE,
  KEY `users` (`users_id`) USING BTREE,
  KEY `enterprises` (`condominium_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `app_subscriptions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `plan_id` int(11) unsigned DEFAULT NULL,
  `card_id` int(11) unsigned DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'active' COMMENT 'active,inactive,past_due,canceled',
  `pay_status` varchar(10) NOT NULL DEFAULT 'active' COMMENT 'active,inactive,past_due,canceled',
  `started` date NOT NULL,
  `due_day` int(2) NOT NULL,
  `next_due` date NOT NULL,
  `last_charge` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `subscription_user` (`user_id`),
  KEY `subscription_card` (`card_id`),
  KEY `subscription_plan` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `app_units` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sub_of` int(11) unsigned DEFAULT NULL,
  `block_id` bigint(20) unsigned DEFAULT NULL,
  `units` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `fraction` decimal(10,6) DEFAULT NULL,
  `area` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_app_units_app_condominium` (`sub_of`),
  FULLTEXT KEY `fulltext` (`units`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `app_wallets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `condominium_id` int(10) unsigned DEFAULT NULL,
  `wallet` varchar(255) NOT NULL DEFAULT '',
  `bank_code` varchar(20) DEFAULT NULL,
  `agency` varchar(20) DEFAULT NULL,
  `account_number` varchar(40) DEFAULT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `free` int(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `wallet_user` (`user_id`),
  KEY `idx_wallet_condominium` (`condominium_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `brief` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(180) NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `townhouse` varchar(180) DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `status` enum('draft','published') NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_brief_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `uri` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'post' COMMENT 'post, page, etc',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `categories_slide` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `uri` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'post' COMMENT 'post, page, etc',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `erp_activity_feed` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned DEFAULT NULL,
  `actor_id` int(10) unsigned DEFAULT NULL,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `summary` varchar(255) NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_erp_activity_scope` (`condominium_id`,`created_at`),
  KEY `idx_erp_activity_entity` (`entity_type`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `erp_approval_steps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` bigint(20) unsigned NOT NULL,
  `condominium_id` int(10) unsigned NOT NULL,
  `sequence_no` smallint(5) unsigned NOT NULL DEFAULT 1,
  `approver_id` int(10) unsigned DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_erp_approval_queue` (`condominium_id`,`status`,`sequence_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `erp_attention_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned DEFAULT NULL,
  `assignee_id` int(10) unsigned DEFAULT NULL,
  `source_type` varchar(60) NOT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `severity` enum('info','warning','critical') NOT NULL DEFAULT 'warning',
  `title` varchar(180) NOT NULL,
  `due_at` datetime DEFAULT NULL,
  `status` enum('open','snoozed','resolved') NOT NULL DEFAULT 'open',
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_erp_attention_queue` (`assignee_id`,`status`,`severity`,`due_at`),
  KEY `idx_erp_attention_condo` (`condominium_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `erp_bank_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned NOT NULL,
  `wallet_id` int(10) unsigned NOT NULL,
  `external_id` varchar(120) DEFAULT NULL,
  `occurred_at` datetime NOT NULL,
  `description` varchar(220) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance` decimal(15,2) DEFAULT NULL,
  `reconciliation_status` enum('pending','matched','ignored') NOT NULL DEFAULT 'pending',
  `matched_entry_id` bigint(20) unsigned DEFAULT NULL,
  `imported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_erp_bank_external` (`wallet_id`,`external_id`),
  KEY `idx_erp_bank_reconcile` (`condominium_id`,`reconciliation_status`,`occurred_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `erp_blocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `code` varchar(40) DEFAULT NULL,
  `floors` int(10) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_erp_block` (`condominium_id`,`name`),
  KEY `idx_erp_block_condo` (`condominium_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `erp_communications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `body` text NOT NULL,
  `channel` set('app','email','whatsapp','notice') NOT NULL DEFAULT 'app',
  `audience` varchar(80) NOT NULL DEFAULT 'all',
  `status` enum('draft','scheduled','sending','sent','cancelled') NOT NULL DEFAULT 'draft',
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_erp_communication_queue` (`status`,`scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `erp_contracts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `number` varchar(80) DEFAULT NULL,
  `starts_at` date NOT NULL,
  `ends_at` date DEFAULT NULL,
  `monthly_amount` decimal(15,2) DEFAULT NULL,
  `notice_days` smallint(5) unsigned NOT NULL DEFAULT 30,
  `status` enum('draft','active','expiring','expired','cancelled') NOT NULL DEFAULT 'active',
  `owner_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_erp_contract_expiry` (`condominium_id`,`status`,`ends_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `erp_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned NOT NULL,
  `entity_type` varchar(60) DEFAULT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `category` varchar(80) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `visibility` enum('internal','managers','residents','public') NOT NULL DEFAULT 'internal',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_erp_document_scope` (`condominium_id`,`category`,`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `erp_financial_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned NOT NULL,
  `unit_id` int(10) unsigned DEFAULT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `wallet_id` int(10) unsigned DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('receivable','payable') NOT NULL,
  `description` varchar(220) NOT NULL,
  `document_number` varchar(80) DEFAULT NULL,
  `competency` date NOT NULL,
  `due_at` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `interest_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','pending_approval','approved','open','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'open',
  `recurrence_rule` varchar(120) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_erp_entry_scope` (`condominium_id`,`type`,`status`,`due_at`),
  KEY `idx_erp_entry_unit` (`unit_id`),
  KEY `idx_erp_entry_supplier` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `erp_meetings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `meeting_type` enum('ordinary','extraordinary','council') NOT NULL DEFAULT 'ordinary',
  `starts_at` datetime NOT NULL,
  `location` varchar(180) DEFAULT NULL,
  `quorum_required` decimal(8,4) DEFAULT NULL,
  `status` enum('draft','called','open','closed','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_erp_meeting_schedule` (`condominium_id`,`status`,`starts_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `erp_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entry_id` bigint(20) unsigned NOT NULL,
  `wallet_id` int(10) unsigned DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_at` datetime NOT NULL,
  `method` varchar(30) NOT NULL DEFAULT 'transfer',
  `reference` varchar(120) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_erp_payment_entry` (`entry_id`),
  KEY `idx_erp_payment_date` (`paid_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `erp_suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_user_id` int(10) unsigned DEFAULT NULL,
  `legal_name` varchar(180) NOT NULL,
  `trade_name` varchar(180) DEFAULT NULL,
  `document` varchar(20) DEFAULT NULL,
  `email` varchar(180) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `category` varchar(80) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `rating` decimal(3,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_erp_supplier_document` (`document`),
  KEY `idx_erp_supplier_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `faq_channels` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `channel` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `faq_questions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) unsigned NOT NULL,
  `question` varchar(255) NOT NULL DEFAULT '',
  `response` text NOT NULL,
  `support_link` varchar(255) DEFAULT NULL,
  `order_by` int(11) unsigned DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `channel_id` (`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `mail_queue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `notification_message_id` int(10) unsigned DEFAULT NULL,
  `proposal_response_id` int(10) unsigned DEFAULT NULL,
  `source_log_id` int(11) unsigned DEFAULT NULL,
  `users_id` int(10) unsigned DEFAULT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `attachments_json` longtext DEFAULT NULL,
  `from_email` varchar(255) NOT NULL DEFAULT '',
  `from_name` varchar(255) NOT NULL DEFAULT '',
  `recipient_email` varchar(255) NOT NULL DEFAULT '',
  `recipient_name` varchar(255) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `scheduled_at` datetime DEFAULT NULL,
  `attempts` smallint(5) unsigned NOT NULL DEFAULT 0,
  `max_attempts` smallint(5) unsigned NOT NULL DEFAULT 3,
  `last_attempt_at` datetime DEFAULT NULL,
  `next_attempt_at` datetime DEFAULT NULL,
  `error_message` varchar(500) DEFAULT NULL,
  `failed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mail_queue_dispatch` (`status`,`scheduled_at`,`next_attempt_at`),
  KEY `idx_mail_queue_message` (`notification_message_id`),
  KEY `idx_mail_queue_user` (`users_id`),
  KEY `idx_mail_queue_source_log` (`source_log_id`,`users_id`),
  KEY `idx_mail_queue_proposal_response` (`proposal_response_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `module_migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_slug` varchar(100) NOT NULL,
  `migration` varchar(190) NOT NULL,
  `checksum` char(64) NOT NULL,
  `batch` int(10) unsigned NOT NULL DEFAULT 1,
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_module_migration` (`module_slug`,`migration`),
  KEY `idx_module_migrations_slug` (`module_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `name` varchar(140) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `available_version` varchar(30) NOT NULL DEFAULT '1.0.0',
  `installed_version` varchar(30) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `is_core` tinyint(1) NOT NULL DEFAULT 0,
  `installed_by` int(10) unsigned DEFAULT NULL,
  `installed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_modules_slug` (`slug`),
  KEY `idx_modules_enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `movesos_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product` enum('web','app','studio','erp','support') NOT NULL DEFAULT 'studio',
  `version` varchar(30) NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('current','archived') NOT NULL DEFAULT 'current',
  `created_by` int(10) unsigned DEFAULT NULL,
  `published_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_movesos_product_version` (`product`,`version`),
  KEY `idx_movesos_version_status` (`status`),
  KEY `idx_movesos_product_status` (`product`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notification_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `audience` varchar(30) NOT NULL DEFAULT 'all',
  `target_user_id` int(10) unsigned DEFAULT NULL,
  `severity` varchar(20) NOT NULL DEFAULT 'info',
  `delivery_channels` varchar(20) NOT NULL DEFAULT 'system',
  `link` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `scheduled_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notification_messages_status` (`status`,`scheduled_at`),
  KEY `idx_notification_messages_sender` (`sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int(10) unsigned DEFAULT NULL,
  `source_log_id` int(11) unsigned DEFAULT NULL,
  `users_id` int(11) unsigned NOT NULL,
  `category` int(11) unsigned NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` text DEFAULT NULL,
  `severity` varchar(20) NOT NULL DEFAULT 'info',
  `link` varchar(255) NOT NULL DEFAULT '',
  `view` int(11) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_notifications_users` (`users_id`,`category`) USING BTREE,
  KEY `FK_notifications_notifications_categories` (`category`),
  KEY `idx_notifications_source_log` (`source_log_id`,`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `notifications_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `uri` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'post' COMMENT 'post, page, etc',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `operation_action_plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `issue_id` int(10) unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('planned','in_progress','completed','cancelled') NOT NULL DEFAULT 'planned',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operation_plan_status` (`status`,`due_at`),
  KEY `fk_operation_plan_issue` (`issue_id`),
  CONSTRAINT `fk_operation_plan_issue` FOREIGN KEY (`issue_id`) REFERENCES `operation_issues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_activity` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `action` varchar(60) NOT NULL,
  `summary` varchar(255) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `payload_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload_json`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operation_activity` (`entity_type`,`entity_id`,`created_at`),
  KEY `idx_operation_activity_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_assets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned NOT NULL,
  `name` varchar(180) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `serial_number` varchar(120) DEFAULT NULL,
  `location` varchar(180) DEFAULT NULL,
  `status` enum('active','maintenance','inactive') NOT NULL DEFAULT 'active',
  `next_maintenance_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operation_asset_status` (`condominium_id`,`status`),
  CONSTRAINT `fk_operation_asset_condo` FOREIGN KEY (`condominium_id`) REFERENCES `operation_condominiums` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(40) NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_size` int(10) unsigned NOT NULL DEFAULT 0,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operation_attachment` (`entity_type`,`entity_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_calendar_participants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `response` enum('pending','accepted','declined') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_calendar_participant` (`event_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_checklist_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `checklist_id` int(10) unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `area` varchar(120) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `asset_id` int(10) unsigned DEFAULT NULL,
  `frequency` enum('every_visit','weekly','biweekly','monthly','quarterly','semiannual','annual','custom') NOT NULL DEFAULT 'every_visit',
  `frequency_rule` varchar(120) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT 1,
  `priority` enum('low','normal','high','critical') NOT NULL DEFAULT 'normal',
  `photo_required_on_failure` tinyint(1) NOT NULL DEFAULT 0,
  `comment_required_on_failure` tinyint(1) NOT NULL DEFAULT 1,
  `position` smallint(5) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_operation_checklist_item` (`checklist_id`,`position`),
  CONSTRAINT `fk_operation_checklist_item` FOREIGN KEY (`checklist_id`) REFERENCES `operation_checklists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_checklists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(180) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `visit_type` varchar(40) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operation_checklist_active` (`active`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(40) NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operation_comment` (`entity_type`,`entity_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_condominiums` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_condominium_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(180) NOT NULL,
  `document` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `state` char(2) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `geofence_radius` smallint(5) unsigned NOT NULL DEFAULT 100,
  `health_score` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `status` enum('implementation','active','inactive') NOT NULL DEFAULT 'implementation',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_operation_condominium_app` (`app_condominium_id`),
  KEY `idx_operation_condo_status` (`status`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_demands` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `protocol` varchar(24) NOT NULL,
  `condominium_id` int(10) unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `status` enum('new','analysis','in_progress','waiting_third_party','waiting_condominium','completed','cancelled') NOT NULL DEFAULT 'new',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `source_type` varchar(40) DEFAULT NULL,
  `source_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_operation_demand_protocol` (`protocol`),
  KEY `idx_operation_demand_queue` (`condominium_id`,`status`,`priority`,`due_at`),
  KEY `idx_operation_demand_assignee` (`assigned_to`,`status`),
  CONSTRAINT `fk_operation_demand_condo` FOREIGN KEY (`condominium_id`) REFERENCES `operation_condominiums` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned NOT NULL,
  `demand_id` int(10) unsigned DEFAULT NULL,
  `supplier_id` int(10) unsigned DEFAULT NULL,
  `ticket_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `category` varchar(100) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_size` int(10) unsigned NOT NULL DEFAULT 0,
  `document_date` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `status` enum('valid','expiring','expired','archived') NOT NULL DEFAULT 'valid',
  `responsible_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operation_document_expiry` (`condominium_id`,`status`,`valid_until`),
  KEY `fk_operation_document_demand` (`demand_id`),
  KEY `fk_operation_document_supplier` (`supplier_id`),
  CONSTRAINT `fk_operation_document_condo` FOREIGN KEY (`condominium_id`) REFERENCES `operation_condominiums` (`id`),
  CONSTRAINT `fk_operation_document_demand` FOREIGN KEY (`demand_id`) REFERENCES `operation_demands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_operation_document_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `operation_suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_issues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned NOT NULL,
  `visit_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','waiting','resolved','cancelled') NOT NULL DEFAULT 'open',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operation_issue_queue` (`status`,`priority`,`due_at`),
  KEY `fk_operation_issue_condo` (`condominium_id`),
  KEY `fk_operation_issue_visit` (`visit_id`),
  CONSTRAINT `fk_operation_issue_condo` FOREIGN KEY (`condominium_id`) REFERENCES `operation_condominiums` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_operation_issue_visit` FOREIGN KEY (`visit_id`) REFERENCES `operation_visits` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_people` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(180) NOT NULL,
  `document` varchar(20) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_operation_person_document` (`document`),
  KEY `idx_operation_person` (`name`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_person_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int(10) unsigned NOT NULL,
  `condominium_id` int(10) unsigned NOT NULL,
  `unit_label` varchar(80) DEFAULT NULL,
  `block_label` varchar(80) DEFAULT NULL,
  `relation_type` enum('resident','owner','tenant','syndic','subsyndic','councillor') NOT NULL,
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operation_person_condo` (`condominium_id`,`relation_type`,`status`),
  KEY `fk_operation_person_link_person` (`person_id`),
  CONSTRAINT `fk_operation_person_link_condo` FOREIGN KEY (`condominium_id`) REFERENCES `operation_condominiums` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_operation_person_link_person` FOREIGN KEY (`person_id`) REFERENCES `operation_people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_quote_offers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quote_id` int(10) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `document_path` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('requested','received','selected','rejected') NOT NULL DEFAULT 'requested',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_operation_quote_supplier` (`quote_id`,`supplier_id`),
  KEY `fk_operation_offer_supplier` (`supplier_id`),
  CONSTRAINT `fk_operation_offer_quote` FOREIGN KEY (`quote_id`) REFERENCES `operation_quotes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_operation_offer_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `operation_suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_quotes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `protocol` varchar(24) NOT NULL,
  `condominium_id` int(10) unsigned NOT NULL,
  `demand_id` int(10) unsigned DEFAULT NULL,
  `visit_id` int(10) unsigned DEFAULT NULL,
  `ticket_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('draft','requested','received','analysis','waiting_approval','approved','rejected','expired') NOT NULL DEFAULT 'draft',
  `valid_until` date DEFAULT NULL,
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_operation_quote_protocol` (`protocol`),
  KEY `idx_operation_quote_queue` (`condominium_id`,`status`,`valid_until`),
  KEY `fk_operation_quote_demand` (`demand_id`),
  KEY `fk_operation_quote_visit` (`visit_id`),
  CONSTRAINT `fk_operation_quote_condo` FOREIGN KEY (`condominium_id`) REFERENCES `operation_condominiums` (`id`),
  CONSTRAINT `fk_operation_quote_demand` FOREIGN KEY (`demand_id`) REFERENCES `operation_demands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_operation_quote_visit` FOREIGN KEY (`visit_id`) REFERENCES `operation_visits` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_relations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_type` varchar(40) NOT NULL,
  `source_id` int(10) unsigned NOT NULL,
  `target_type` varchar(40) NOT NULL,
  `target_id` int(10) unsigned NOT NULL,
  `relation_type` varchar(60) NOT NULL DEFAULT 'related',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_operation_relation` (`source_type`,`source_id`,`target_type`,`target_id`,`relation_type`),
  KEY `idx_operation_relation_target` (`target_type`,`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_resident_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('submitted','reviewing','voting','approved','planned','rejected') NOT NULL DEFAULT 'submitted',
  `votes` int(10) unsigned NOT NULL DEFAULT 0,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operation_request_status` (`condominium_id`,`status`),
  CONSTRAINT `fk_operation_request_condo` FOREIGN KEY (`condominium_id`) REFERENCES `operation_condominiums` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_suppliers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `legal_name` varchar(180) NOT NULL,
  `trade_name` varchar(180) DEFAULT NULL,
  `document` varchar(20) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `contact_name` varchar(160) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `state` char(2) DEFAULT NULL,
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_operation_supplier_document` (`document`),
  KEY `idx_operation_supplier` (`category`,`status`,`trade_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned NOT NULL,
  `demand_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `task_type` enum('task','meeting','deadline','assembly','inspection') NOT NULL DEFAULT 'task',
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `starts_at` datetime DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operation_task_day` (`assigned_to`,`status`,`due_at`),
  KEY `fk_operation_task_condo` (`condominium_id`),
  KEY `fk_operation_task_demand` (`demand_id`),
  CONSTRAINT `fk_operation_task_condo` FOREIGN KEY (`condominium_id`) REFERENCES `operation_condominiums` (`id`),
  CONSTRAINT `fk_operation_task_demand` FOREIGN KEY (`demand_id`) REFERENCES `operation_demands` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_visit_agenda_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `visit_id` int(10) unsigned NOT NULL,
  `source_type` varchar(40) NOT NULL,
  `source_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','normal','high','critical') NOT NULL DEFAULT 'normal',
  `status` enum('pending','discussed','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `position` smallint(5) unsigned NOT NULL DEFAULT 0,
  `generated_automatically` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_visit_agenda_source` (`visit_id`,`source_type`,`source_id`),
  KEY `idx_visit_agenda` (`visit_id`,`status`,`priority`,`position`),
  CONSTRAINT `fk_visit_agenda_visit` FOREIGN KEY (`visit_id`) REFERENCES `operation_visits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_visit_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `visit_id` int(10) unsigned NOT NULL,
  `event_type` varchar(60) NOT NULL,
  `summary` varchar(255) NOT NULL,
  `details_json` longtext DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_visit_event_timeline` (`visit_id`,`occurred_at`),
  CONSTRAINT `fk_visit_event_visit` FOREIGN KEY (`visit_id`) REFERENCES `operation_visits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_visit_evidence` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `visit_id` int(10) unsigned NOT NULL,
  `visit_item_id` int(10) unsigned DEFAULT NULL,
  `issue_id` int(10) unsigned DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_size` int(10) unsigned NOT NULL DEFAULT 0,
  `caption` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_visit_evidence` (`visit_id`,`visit_item_id`),
  KEY `fk_visit_evidence_item` (`visit_item_id`),
  CONSTRAINT `fk_visit_evidence_item` FOREIGN KEY (`visit_item_id`) REFERENCES `operation_visit_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_visit_evidence_visit` FOREIGN KEY (`visit_id`) REFERENCES `operation_visits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_visit_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `visit_id` int(10) unsigned NOT NULL,
  `checklist_item_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `area` varchar(120) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `priority` enum('low','normal','high','critical') NOT NULL DEFAULT 'normal',
  `photo_required_on_failure` tinyint(1) NOT NULL DEFAULT 0,
  `comment_required_on_failure` tinyint(1) NOT NULL DEFAULT 1,
  `result` enum('pending','conforming','attention','nonconforming','not_applicable') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `checked_by` int(10) unsigned DEFAULT NULL,
  `checked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_operation_visit_item` (`visit_id`,`result`),
  KEY `fk_operation_visit_item_template` (`checklist_item_id`),
  CONSTRAINT `fk_operation_visit_item_template` FOREIGN KEY (`checklist_item_id`) REFERENCES `operation_checklist_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_operation_visit_item_visit` FOREIGN KEY (`visit_id`) REFERENCES `operation_visits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_visit_outcomes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `visit_id` int(10) unsigned NOT NULL,
  `issue_id` int(10) unsigned DEFAULT NULL,
  `outcome_type` enum('issue','demand','ticket','task','record') NOT NULL,
  `outcome_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_visit_outcome` (`visit_id`,`outcome_type`,`outcome_id`),
  CONSTRAINT `fk_visit_outcome_visit` FOREIGN KEY (`visit_id`) REFERENCES `operation_visits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_visit_participants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `visit_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `person_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(180) DEFAULT NULL,
  `role` varchar(80) DEFAULT NULL,
  `presence_status` enum('invited','confirmed','present','absent') NOT NULL DEFAULT 'invited',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_visit_participant` (`visit_id`,`presence_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_visit_sync_queue` (
  `id` char(36) NOT NULL,
  `visit_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `device_id` varchar(180) DEFAULT NULL,
  `operation_type` varchar(60) NOT NULL,
  `payload_json` longtext NOT NULL,
  `status` enum('pending','processing','synced','failed') NOT NULL DEFAULT 'pending',
  `attempts` smallint(5) unsigned NOT NULL DEFAULT 0,
  `last_error` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `synced_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_visit_sync` (`visit_id`,`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `operation_visits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned NOT NULL,
  `demand_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `objective` text DEFAULT NULL,
  `visit_type` enum('periodic','management','inspection','meeting','follow_up','technical','emergency','implementation','maintenance') NOT NULL DEFAULT 'inspection',
  `scheduled_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `recurrence_rule` varchar(120) DEFAULT NULL,
  `recurrence_parent_id` int(10) unsigned DEFAULT NULL,
  `recurrence_key` varchar(80) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `checkin_latitude` decimal(10,7) DEFAULT NULL,
  `checkin_longitude` decimal(10,7) DEFAULT NULL,
  `checkin_accuracy` decimal(8,2) DEFAULT NULL,
  `checkin_device` varchar(255) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `checkout_latitude` decimal(10,7) DEFAULT NULL,
  `checkout_longitude` decimal(10,7) DEFAULT NULL,
  `checkout_accuracy` decimal(8,2) DEFAULT NULL,
  `checkout_device` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `signature_required` tinyint(1) NOT NULL DEFAULT 0,
  `signature_name` varchar(180) DEFAULT NULL,
  `signature_path` varchar(500) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_operation_visit_recurrence` (`recurrence_parent_id`,`recurrence_key`),
  KEY `idx_operation_visit_schedule` (`status`,`scheduled_at`),
  KEY `fk_operation_visit_condo` (`condominium_id`),
  CONSTRAINT `fk_operation_visit_condo` FOREIGN KEY (`condominium_id`) REFERENCES `operation_condominiums` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `author` int(11) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `uri` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `video` varchar(50) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'draft' COMMENT 'post, draft, trash ',
  `post_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`author`) USING BTREE,
  FULLTEXT KEY `full_text` (`content`,`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `author` int(11) unsigned DEFAULT NULL,
  `category` int(11) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `uri` varchar(255) NOT NULL,
  `subtitle` text NOT NULL,
  `content` text NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `video` varchar(50) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'draft' COMMENT 'post, draft, trash ',
  `post_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category`),
  KEY `user_id` (`author`),
  FULLTEXT KEY `full_text` (`title`,`subtitle`,`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `proposal_responses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proposal_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `template_type` enum('syndic','administrator') NOT NULL DEFAULT 'syndic',
  `subject` varchar(255) NOT NULL,
  `introduction` text NOT NULL,
  `scope` text NOT NULL,
  `commercial_terms` text NOT NULL,
  `payment_terms` varchar(500) DEFAULT NULL,
  `valid_until` date NOT NULL,
  `notes` text DEFAULT NULL,
  `pdf_path` varchar(500) DEFAULT NULL,
  `status` enum('draft','generated','queued','sent','failed') NOT NULL DEFAULT 'draft',
  `queued_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_proposal_responses_proposal` (`proposal_id`,`created_at`),
  KEY `idx_proposal_responses_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `proposals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `protocol` varchar(32) NOT NULL,
  `name` varchar(180) NOT NULL,
  `email` varchar(180) NOT NULL,
  `whatsapp` varchar(30) NOT NULL,
  `condominium` varchar(180) NOT NULL,
  `units` int(10) unsigned NOT NULL,
  `profile` varchar(40) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('new','contacted','qualified','proposal_sent','won','lost','archived') NOT NULL DEFAULT 'new',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `request_hash` char(64) DEFAULT NULL,
  `consent_at` datetime DEFAULT NULL,
  `contacted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_proposals_protocol` (`protocol`),
  KEY `idx_proposals_status_created` (`status`,`created_at`),
  KEY `idx_proposals_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `report_access` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `users` int(11) NOT NULL DEFAULT 1,
  `views` int(11) NOT NULL DEFAULT 1,
  `pages` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `report_online` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) unsigned DEFAULT NULL,
  `ip` varchar(50) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `agent` varchar(255) NOT NULL DEFAULT '',
  `pages` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mode` int(11) DEFAULT 1,
  `site_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_desc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_photo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_logo_svg` varchar(255) DEFAULT NULL,
  `site_icon` varchar(255) DEFAULT NULL,
  `site_favicon` varchar(255) DEFAULT NULL,
  `site_lang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'pt_BR',
  `site_domain` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_domain_ssl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_domain_off` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_street` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_whatsapp` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_number` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_complement` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_city` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_state` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `site_district` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `view_theme` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `view_support` varchar(255) DEFAULT 'support_by_moves',
  `view_app` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `view_erp` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `view_admin` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `view_mail` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `view_upkeep` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'moves_upkeep',
  `upload_dir` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'storage',
  `upload_image` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'images',
  `upload_file` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'files',
  `upload_media` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'medias',
  `image_cache` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'cache',
  `image_size` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '2000',
  `image_jpg` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '75',
  `image_png` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '5',
  `mail_host` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mail_port` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mail_user` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mail_pass` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mail_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mail_address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mail_suport` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mail_lang` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mail_html` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'true',
  `mail_auth` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'true',
  `mail_secure` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mail_charset` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `pay_mode` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'test',
  `pay_live` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `pay_test` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `pay_back` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `social_tw_creator` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `social_tw_publisher` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `social_fb_app` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `social_fb_page` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `social_fb_author` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `social_google_page` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `social_google_author` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `social_instagram_page` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `social_youtube_page` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `social_linkedin_page` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `timezone_set` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `access_studio` tinyint(1) NOT NULL DEFAULT 1,
  `access_erp` tinyint(1) NOT NULL DEFAULT 1,
  `access_app` tinyint(1) NOT NULL DEFAULT 1,
  `access_site` tinyint(1) NOT NULL DEFAULT 1,
  `access_support` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `slides` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `author` int(11) unsigned DEFAULT NULL,
  `category` int(11) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `uri` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'draft' COMMENT 'post, draft, trash ',
  `position` varchar(20) NOT NULL DEFAULT 'left' COMMENT 'left, center, right',
  `post_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `category_id` (`category`) USING BTREE,
  KEY `user_id` (`author`) USING BTREE,
  FULLTEXT KEY `full_text` (`content`,`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `studio_calendar_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `recurrence_rule` varchar(120) DEFAULT NULL,
  `reminder_minutes` smallint(5) unsigned DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `type` enum('meeting','task','deadline','support') NOT NULL DEFAULT 'meeting',
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `operation_entity_type` varchar(40) DEFAULT NULL,
  `operation_entity_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_calendar_range` (`starts_at`,`ends_at`),
  KEY `idx_calendar_assigned` (`assigned_to`),
  KEY `fk_calendar_created_user` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `studio_support_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL,
  `body` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_support_template_user` (`created_by`),
  CONSTRAINT `fk_support_template_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `studio_support_ticket_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(10) unsigned NOT NULL,
  `message_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_size` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ticket_attachment` (`ticket_id`,`created_at`),
  KEY `fk_ticket_attachment_message` (`message_id`),
  KEY `fk_ticket_attachment_user` (`user_id`),
  CONSTRAINT `fk_ticket_attachment_message` FOREIGN KEY (`message_id`) REFERENCES `studio_support_ticket_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ticket_attachment_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `studio_support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ticket_attachment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `studio_support_ticket_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `event_type` varchar(40) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ticket_event` (`ticket_id`,`created_at`),
  KEY `fk_ticket_event_user` (`user_id`),
  CONSTRAINT `fk_ticket_event_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `studio_support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ticket_event_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `studio_support_ticket_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ticket_message` (`ticket_id`,`created_at`),
  KEY `fk_ticket_message_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `studio_support_tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `condominium_id` int(10) unsigned DEFAULT NULL,
  `demand_id` int(10) unsigned DEFAULT NULL,
  `protocol` char(12) NOT NULL,
  `subject` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `area` enum('general','technical','financial') NOT NULL DEFAULT 'general',
  `category` varchar(100) DEFAULT NULL,
  `tags` varchar(500) DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','waiting_customer','resolved','closed') NOT NULL DEFAULT 'open',
  `requester_id` int(10) unsigned DEFAULT NULL,
  `requester_person_id` int(10) unsigned DEFAULT NULL,
  `requester_name` varchar(160) DEFAULT NULL,
  `requester_email` varchar(190) DEFAULT NULL,
  `access_token` char(64) DEFAULT NULL,
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `team` varchar(100) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `due_at` datetime NOT NULL,
  `first_response_at` datetime DEFAULT NULL,
  `work_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_support_ticket_protocol` (`protocol`),
  UNIQUE KEY `uq_support_ticket_access_token` (`access_token`),
  KEY `idx_support_ticket_status` (`status`,`priority`,`created_at`),
  KEY `idx_support_ticket_requester` (`requester_id`),
  KEY `idx_support_ticket_assignee` (`assigned_to`),
  KEY `fk_ticket_created_user` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `support_article_votes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL,
  `vote` enum('yes','no') NOT NULL,
  `visitor_hash` char(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_support_vote` (`article_id`,`visitor_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `support_articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `uri` varchar(190) NOT NULL,
  `summary` varchar(320) NOT NULL,
  `content` longtext NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `views` int(10) unsigned NOT NULL DEFAULT 0,
  `helpful_yes` int(10) unsigned NOT NULL DEFAULT 0,
  `helpful_no` int(10) unsigned NOT NULL DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_support_article_uri` (`uri`),
  KEY `idx_support_article_category` (`category_id`,`status`,`published_at`),
  KEY `fk_support_article_author` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `support_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL,
  `uri` varchar(140) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(80) NOT NULL DEFAULT 'help-circle-outline',
  `position` int(10) unsigned NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_support_category_uri` (`uri`),
  KEY `idx_support_category_status` (`status`,`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `system_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `users_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(30) NOT NULL,
  `entity` varchar(100) NOT NULL,
  `entity_id` varchar(50) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `context_json` longtext DEFAULT NULL,
  `severity` varchar(20) NOT NULL DEFAULT 'info',
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_entity` (`entity`,`entity_id`),
  KEY `idx_audit_user` (`users_id`),
  KEY `idx_audit_created` (`created_at`),
  KEY `idx_audit_severity` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `system_protected_users` (
  `user_id` int(10) unsigned NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_protected_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL DEFAULT '',
  `last_name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `document` varchar(11) NOT NULL DEFAULT '',
  `document_rg` varchar(16) DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `level` int(11) NOT NULL DEFAULT 1,
  `forget` varchar(255) DEFAULT NULL,
  `genre` varchar(10) NOT NULL DEFAULT 'uninformed' COMMENT 'uninformed, male, female, other, null',
  `datebirth` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `doc_cpf` varchar(255) DEFAULT NULL,
  `doc_rg` varchar(255) DEFAULT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `phone_cell` varchar(11) DEFAULT NULL,
  `send` int(11) NOT NULL DEFAULT 1,
  `despatch` varchar(255) NOT NULL DEFAULT 'all' COMMENT 'e-mail, whatsapp, telegram, letter, all',
  `status` varchar(50) NOT NULL DEFAULT 'registered' COMMENT 'registered, confirmed',
  `privacy` varchar(50) DEFAULT 'reject' COMMENT 'reject, accept',
  `session_condo` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `email` (`document`,`email`) USING BTREE,
  KEY `idx_users_session_condo` (`session_condo`),
  FULLTEXT KEY `full_text` (`document`,`first_name`,`last_name`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

SET FOREIGN_KEY_CHECKS=1;
