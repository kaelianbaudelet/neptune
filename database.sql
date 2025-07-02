-- Base de données --

-- ! Executer ce script uniquement avec une base de données MySQL/MariaDB, pas POSTGRESQL ou SQLITE ! --

DROP TABLE IF EXISTS `Booking_Guest`;
DROP TABLE IF EXISTS `Booking_Option`;
DROP TABLE IF EXISTS `Booking`;
DROP TABLE IF EXISTS `Invoice_Option`;
DROP TABLE IF EXISTS `Invoice`;
DROP TABLE IF EXISTS `Coupon`;
DROP TABLE IF EXISTS `Employee`;
DROP TABLE IF EXISTS `Room_Image`;
DROP TABLE IF EXISTS `Room_Equipment`;
DROP TABLE IF EXISTS `Equipment`;
DROP TABLE IF EXISTS `Room`;
DROP TABLE IF EXISTS `Option`;
DROP TABLE IF EXISTS `Image`;
DROP TABLE IF EXISTS `Type`;
DROP TABLE IF EXISTS `User`;

CREATE TABLE IF NOT EXISTS `User` (
    `id` char(36) NOT NULL DEFAULT uuid(),
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(255) DEFAULT NULL,
    `commercial_email_consent` boolean NOT NULL DEFAULT 0,
    `type` ENUM('individual', 'company') NOT NULL DEFAULT 'individual',
    `password` varchar(255) NOT NULL,
    `is_active` boolean NOT NULL DEFAULT 0,
    `activation_token` varchar(255) DEFAULT NULL,
    `activation_token_expires_at` datetime DEFAULT NULL,
    `reset_token` varchar(255) DEFAULT NULL,
    `reset_token_expires_at` datetime DEFAULT NULL,
    `role` ENUM('Admin', 'User') NOT NULL DEFAULT 'User',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Image` (
    `id` char(36) NOT NULL DEFAULT uuid(),
    `file_key` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `extension` varchar(4) NOT NULL,
    `description` text DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Type` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Equipment` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `icon` varchar(255) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Room` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `bed_single` int(11) NOT NULL,
    `bed_double` int(11) NOT NULL,
    `capacity` int(11) NOT NULL,
    `available` boolean NOT NULL DEFAULT 1,
    `type_id` int(11) NOT NULL,
    FOREIGN KEY (`type_id`) REFERENCES `Type`(`id`) ON DELETE CASCADE,
    `price` float NOT NULL,
    `discount` float NOT NULL DEFAULT 0,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Room_Equipment` (
    `room_id` int(11) NOT NULL,
    `equipment_id` int(11) NOT NULL,
    FOREIGN KEY (`room_id`) REFERENCES `Room`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`equipment_id`) REFERENCES `Equipment`(`id`) ON DELETE CASCADE,
    PRIMARY KEY (`room_id`, `equipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Room_Image` (
    `room_id` int(11) NOT NULL,
    `image_id` char(36) NOT NULL,
    FOREIGN KEY (`room_id`) REFERENCES `Room`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`image_id`) REFERENCES `Image`(`id`) ON DELETE CASCADE,
    PRIMARY KEY (`room_id`, `image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Option` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) UNIQUE NOT NULL,
    `description` text DEFAULT NULL,
    `price` float NOT NULL,
    `tva` float NOT NULL,
    `image_id` char(36) DEFAULT NULL,
    FOREIGN KEY (`image_id`) REFERENCES `Image`(`id`) ON DELETE CASCADE,
    `per_adult` boolean NOT NULL DEFAULT 0, -- définit si le prix est par adulte
    `per_child` boolean NOT NULL DEFAULT 0, -- définit si le prix est par enfant
    `per_night` boolean NOT NULL DEFAULT 0, -- définit si le prix est par nuit
    `per_quantity` boolean NOT NULL DEFAULT 0, -- définit si le prix est par quantité
    `max_quantity` int(11) DEFAULT NULL, -- définit la quantité maximale si le prix est par quantité
    `quantity_identifier` varchar(255) DEFAULT NULL, -- définit la désignation de la quantité (ex: "animaux") si le prix est par quantité
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Invoice` (
    `id` char(36) NOT NULL DEFAULT uuid(),
    `type` ENUM('invoice', 'credit_note') NOT NULL DEFAULT 'invoice',
    `original_invoice_id` char(36) DEFAULT NULL, -- si c'est un avoir, on stocke l'id de la facture associée pour la comptabilité
    `status` ENUM('pending_payment', 'pending_refund', 'paid', 'refunded') NOT NULL DEFAULT 'pending_payment',
    `user_id` char(36)DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `User`(`id`) ON DELETE SET NULL, -- la facture sera toujours conservée même si l'utilisateur est supprimé
    `total_ttc` float NOT NULL,
    `total_ht` float NOT NULL,
    `total_tva` float NOT NULL,
    `tva` float NOT NULL,
    `coupon_code` varchar(255) DEFAULT NULL,
    `coupon_discount` float DEFAULT 0,
    `discount` float NOT NULL DEFAULT 0,
    `checkin` date NOT NULL,
    `checkout` date NOT NULL,
    `adults` int(11) NOT NULL,
    `children` int(11) NOT NULL,
    `room_name` varchar(255) NOT NULL,
    `room_price` float NOT NULL,
    `tourist_tax` float NOT NULL,
    `billing_address` text NOT NULL,
    `billing_address2` text DEFAULT NULL,
    `billing_city` varchar(255) NOT NULL,
    `billing_postal_code` varchar(255) NOT NULL,
    `billing_country` varchar(255) NOT NULL,
    `billing_phone` varchar(255) NOT NULL,
    `billing_email` varchar(255) NOT NULL,
    `billing_name` varchar(255) NOT NULL,
    `payment_id` varchar(255) DEFAULT NULL, -- on suppose l'id fictif de la transaction sur une api de paiement (ex: Mollie)
    `payment_method` ENUM('credit_card', 'paypal', 'stripe', 'bank_transfer', 'cash', 'check') NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`original_invoice_id`) REFERENCES `Invoice`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Booking` (
    `id` char(36) NOT NULL DEFAULT uuid(),
    `user_id` char(36) DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `User`(`id`) ON DELETE SET NULL,
    `room_id` int(11) NOT NULL,
    FOREIGN KEY (`room_id`) REFERENCES `Room`(`id`),
    `invoice_id` char(36) DEFAULT NULL,
    FOREIGN KEY (`invoice_id`) REFERENCES `Invoice`(`id`) ON DELETE SET NULL,
    `checkin` date NOT NULL,
    `checkout` date NOT NULL,
    `adults` int(11) NOT NULL,
    `children` int(11) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Booking_Option` (
    `booking_id` char(36) NOT NULL,
    `option_id` int(11) NOT NULL,
    `quantity` int(11) NOT NULL DEFAULT 1,
    FOREIGN KEY (`booking_id`) REFERENCES `Booking`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`option_id`) REFERENCES `Option`(`id`) ON DELETE CASCADE,
    PRIMARY KEY (`booking_id`, `option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Booking_Guest` (
    `booking_id` char(36) NOT NULL,
    `name` varchar(255) NOT NULL,
    `type` ENUM('adult', 'child') NOT NULL,
    FOREIGN KEY (`booking_id`) REFERENCES `Booking`(`id`) ON DELETE CASCADE,
    PRIMARY KEY (`booking_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Invoice_Option` (
    `invoice_id` char(36) NOT NULL,
    `option_name` varchar(255) NOT NULL,
    `option_price` float NOT NULL,
    `option_tva` float NOT NULL,
    `per_adult` boolean NOT NULL DEFAULT 0, -- définit si le prix est par adulte
    `per_child` boolean NOT NULL DEFAULT 0, -- définit si le prix est par enfant
    `per_night` boolean NOT NULL DEFAULT 0, -- définit si le prix est par nuit
    `per_quantity` boolean NOT NULL DEFAULT 0, -- définit si le prix est par quantité
    `quantity_identifier` varchar(255) DEFAULT NULL, -- définit la désignation de la quantité (ex: "animaux") si le prix est par quantité
    `quantity` int(11) NOT NULL DEFAULT 1,
    FOREIGN KEY (`invoice_id`) REFERENCES `Invoice`(`id`) ON DELETE CASCADE,
    PRIMARY KEY (`invoice_id`, `option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `discount` int(11) NOT NULL,
  `used` int(11) NOT NULL DEFAULT 0,
  `max_use` int(11) NOT NULL DEFAULT 1,
 `user_id` char(36) DEFAULT NULL,
 FOREIGN KEY (`user_id`) REFERENCES `User`(`id`) ON DELETE SET NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Employee` (
    `id` char(36) NOT NULL DEFAULT uuid(),
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(255) NOT NULL,
    `position` varchar(255) NOT NULL,
    `salary` float NOT NULL,
    `date_of_birth` date NOT NULL,
    `hired_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- billing address table
CREATE TABLE IF NOT EXISTS `Billing_Address` (
    `id` char(36) NOT NULL DEFAULT uuid(),
    `user_id` char(36) DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `User`(`id`) ON DELETE SET NULL,
    `name` varchar(255) NOT NULL,
    `country` varchar(255) NOT NULL,
    `address` text NOT NULL,
    `address2` text DEFAULT NULL,
    `city` varchar(255) NOT NULL,
    `state` varchar(255) DEFAULT NULL,
    `postal_code` varchar(255) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;