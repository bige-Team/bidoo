CREATE TABLE `auction_tracking` (
	`name` VARCHAR(80) NOT NULL,
	`id` INT(11) NULL DEFAULT NULL,
	`analized` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'analized and inserted into users_ranking',
	`assigned` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'assigned to a child process',
	`terminated` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'auction terminated',
	PRIMARY KEY (`name`),
	UNIQUE INDEX `id` (`id`)
)
COMMENT='Tiene traccia delle tabelle già analizzate'
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;
