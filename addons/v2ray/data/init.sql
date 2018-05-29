-- Soft Name: LegendSock
-- Current version: 2.1
-- https://www.legendsock.com

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

CREATE TABLE IF NOT EXISTS `user` (
  `pid` int(11) NOT NULL,
  `v2ray_uuid` varchar(64) NOT NULL,
  `v2ray_alter_id` int(11) NOT NULL DEFAULT '2',
  `v2ray_level` int(11) NOT NULL DEFAULT '0',
  `u` bigint(20) NOT NULL DEFAULT '0',
  `d` bigint(20) NOT NULL DEFAULT '0',
  `t` int(11) NOT NULL DEFAULT '1475769600',
  `enable` tinyint(4) NOT NULL DEFAULT '1',
  `transfer_enable` bigint(20) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `chart`
  ADD PRIMARY KEY (`pid`),
  ADD UNIQUE KEY `pid` (`pid`);

ALTER TABLE `setting`
  ADD PRIMARY KEY (`pid`),
  ADD UNIQUE KEY `pid` (`pid`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`pid`),
  ADD UNIQUE KEY `pid` (`pid`),
  ADD UNIQUE KEY `port` (`v2ray_uuid`);