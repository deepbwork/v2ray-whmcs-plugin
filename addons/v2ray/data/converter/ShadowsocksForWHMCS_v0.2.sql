-- Soft Name: LegendSock
-- Current version: 2.1
-- https://www.v2ray.com

ALTER TABLE `user` CHARSET=utf8mb4;

ALTER TABLE `user`
  DROP `id`,
  DROP `switch`;

ALTER TABLE `user` CHANGE `pid` `pid` INT NOT NULL;

ALTER TABLE `user` DROP PRIMARY KEY, ADD PRIMARY KEY(`pid`);

ALTER TABLE `user` CHANGE `t` `t` INT(11) NOT NULL DEFAULT '1475769600' AFTER `d`;

ALTER TABLE `user` CHANGE `port` `port` INT(11) NOT NULL DEFAULT '0' AFTER `t`;

ALTER TABLE `user` CHANGE `enable` `enable` TINYINT(4) NOT NULL DEFAULT '1' AFTER `port`;

ALTER TABLE `user` CHANGE `passwd` `passwd` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL AFTER `enable`;

ALTER TABLE `user` ADD `obfs` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'plain' AFTER `port`, ADD `method` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aes-256-cfb' AFTER `obfs`, ADD `protocol` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'origin' AFTER `method`;

ALTER TABLE `user` CHANGE `u` `u` BIGINT(20) NOT NULL DEFAULT '0';

ALTER TABLE `user` CHANGE `d` `d` BIGINT(20) NOT NULL DEFAULT '0';

ALTER TABLE `user` CHANGE `transfer_enable` `transfer_enable` BIGINT(20) NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `chart` (
  `pid` int(11) NOT NULL,
  `upload` text NOT NULL,
  `download` text NOT NULL,
  `date` int(11) NOT NULL DEFAULT '1475769600'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `setting` (
  `pid` int(11) NOT NULL,
  `mail` tinyint(4) NOT NULL DEFAULT '0',
  `addition` bigint(20) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '1475769600'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `chart`
  ADD PRIMARY KEY (`pid`),
  ADD UNIQUE KEY `pid` (`pid`);

ALTER TABLE `setting`
  ADD PRIMARY KEY (`pid`),
  ADD UNIQUE KEY `pid` (`pid`);

