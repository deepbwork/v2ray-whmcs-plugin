-- Soft Name: LegendSock
-- Current version: 2.1
-- https://www.v2ray.com

ALTER TABLE `user`
  DROP `id`,
  DROP `switch`,
  DROP `updated_at`,
  DROP `addition`;

ALTER TABLE `user` ADD PRIMARY KEY(`pid`);

ALTER TABLE `user` ADD UNIQUE(`pid`);

ALTER TABLE `user` ADD UNIQUE(`port`);

ALTER TABLE `user` CHANGE `u` `u` BIGINT NOT NULL DEFAULT '0' AFTER `pid`;

ALTER TABLE `user` CHANGE `d` `d` BIGINT NOT NULL DEFAULT '0' AFTER `u`;

ALTER TABLE `user` CHANGE `t` `t` INT NOT NULL DEFAULT '1475769600' AFTER `d`;

ALTER TABLE `user` CHANGE `port` `port` INT NOT NULL DEFAULT '0';

ALTER TABLE `user` CHANGE `passwd` `passwd` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL AFTER `enable`;

ALTER TABLE `user` ADD `obfs` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'plain' AFTER `port`, ADD `method` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aes-256-cfb' AFTER `obfs`, ADD `protocol` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'origin' AFTER `method`;

ALTER TABLE `user` CHANGE `transfer_enable` `transfer_enable` BIGINT NOT NULL DEFAULT '0';

ALTER TABLE `user` CHARSET=utf8mb4;

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