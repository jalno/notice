CREATE TABLE `notice_notes` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`view` varchar(255) NOT NULL,
	`type` varchar(15) NOT NULL,
	`create_at` int(11) DEFAULT NULL,
	`start_time` tinyint(4) DEFAULT NULL,
	`end_time` tinyint(4) DEFAULT NULL,
	`expire_at` int(11) DEFAULT NULL,
	`title` varchar(100) NOT NULL,
	`content` text NOT NULL,
	`status` tinyint(4) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `notice_notes_params` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`note` int(11) NOT NULL,
	`name` varchar(255) NOT NULL,
	`value` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `note` (`note`),
	CONSTRAINT `notice_notes_params_ibfk_1` FOREIGN KEY (`note`) REFERENCES `notice_notes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `notice_notes_users` (
	`note` int(11) NOT NULL,
	`user` int(11) NOT NULL,
	`closed` tinyint(4) NOT NULL,
	UNIQUE KEY `note` (`note`,`user`),
	KEY `user` (`user`),
	CONSTRAINT `notice_notes_users_ibfk_1` FOREIGN KEY (`note`) REFERENCES `notice_notes` (`id`) ON DELETE CASCADE,
	CONSTRAINT `notice_notes_users_ibfk_2` FOREIGN KEY (`user`) REFERENCES `userpanel_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notice_notes_usertypes` (
	`note` int(11) NOT NULL,
	`type` int(11) NOT NULL,
	UNIQUE KEY `note` (`note`,`type`),
	KEY `type` (`type`),
	CONSTRAINT `notice_notes_usertypes_ibfk_1` FOREIGN KEY (`note`) REFERENCES `notice_notes` (`id`) ON DELETE CASCADE,
	CONSTRAINT `notice_notes_usertypes_ibfk_2` FOREIGN KEY (`type`) REFERENCES `userpanel_usertypes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;