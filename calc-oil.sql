CREATE TABLE IF NOT EXISTS `wp_calc_oils` (`id` int(11) NOT NULL, `name` varchar(50) CHARACTER SET utf8 NOT NULL, `group` enum('b0','pf1','pf2','b1','b2','b3','a1','a2','a3','z') NOT NULL, `acids` text CHARACTER SET utf8 NOT NULL,`iodine` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `wp_calc_oils` ADD PRIMARY KEY (`id`), ADD KEY `type` (`type`);
ALTER TABLE `wp_calc_oils` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
CREATE TABLE IF NOT EXISTS `wp_calc_oil_acids` (`id` int(11) NOT NULL,`name` varchar(100) NOT NULL,`type` enum('polyunsaturated','monounsaturated','saturated') NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `wp_calc_oil_acids` ADD PRIMARY KEY (`id`);
ALTER TABLE `wp_calc_oil_acids` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
INSERT INTO `wp_calc_oil_acids` (`id`, `name`, `type`) VALUES
(1, 'Линолевая 18:2', 'polyunsaturted'),
(2, 'Гамма-линоленовая 18:3', 'polyunsaturted'),
(3, 'Альфа-линоленовая (или линоленовая) 18:3', 'polyunsaturted'),
(4, 'Арахидоновая 20:4', 'polyunsaturted'),
(5, 'Олеиновая 18:1', 'monounsaturated'),
(6, 'Пальмитоолеиновая 16:1', 'monounsaturated'),
(7, 'Эруковая 22:1', 'monounsaturated'),
(8, 'Эйкозановая: гадолеиновая (С20:1)', 'monounsaturated'),
(9, 'Миристолеиновая кислота (С14:1)', 'monounsaturated'),
(10, 'Арахиновая 20:0', 'saturated'),
(11, 'Пальмитиновая 16:0', 'saturated'),
(12, 'Стеариновая 18:0', 'saturated'),
(13, 'Миристиновая  14:0', 'saturated'),
(14, 'Лауриновая 12:0', 'saturated'),
(15, 'Каприловая  8:0', 'saturated'),
(16, 'Каприновая  10:0', 'saturated');


