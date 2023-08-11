<?php

//добавление поля администратора и роли root-администратора
mysqli_query($dblink,"ALTER TABLE `".DBP."admin` ADD `root` BOOLEAN NOT NULL DEFAULT TRUE  AFTER `pass`");
mysqli_query($dblink,"ALTER TABLE `".DBP."vkApi` ADD `admin` INT NOT NULL DEFAULT '1' AFTER `secret`;");
mysqli_query($dblink,"ALTER TABLE `".DBP."vkApi` ADD `vkGroupPHP` TEXT NOT NULL DEFAULT ''  AFTER `admin`;");
mysqli_query($dblink,"ALTER TABLE `".DBP."vkApi` CHANGE `secret` `secret` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
mysqli_query($dblink,"ALTER TABLE `".DBP."mlists` CHANGE `mlistName` `mlistName` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");

//добавление таблицы для нового администраторского интерфейса с авторизацией стандартными средствами вк
mysqli_query($dblink,"CREATE TABLE IF NOT EXISTS `".DBP."vkAdmin` (
  `vkGroupId` int(11) NOT NULL,
  `vkId` int(11) NOT NULL,
  UNIQUE KEY `vkGroupId` (`vkGroupId`,`vkId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

//Функционал цепочек писем
mysqli_query($dblink,"CREATE TABLE IF NOT EXISTS `".DBP."eventTypes` ( `eventTypeId` INT NOT NULL AUTO_INCREMENT , `eventTypeName` VARCHAR(50) NOT NULL , PRIMARY KEY (`eventTypeId`)) ENGINE = InnoDB");

mysqli_query($dblink,"INSERT INTO `".DBP."eventTypes` (`eventTypeId`, `eventTypeName`) VALUES ('1', 'Подписка на список'), ('2', 'Отправка сообщения'), ('3', 'Отправка сообщения из цепочки')");

mysqli_query($dblink,"CREATE TABLE IF NOT EXISTS `".DBP."message_log` ( `messageId` INT NOT NULL AUTO_INCREMENT , `messageText` TEXT NOT NULL , `messagePHP` TEXT NOT NULL , PRIMARY KEY (`messageId`)) ENGINE = InnoDB");

mysqli_query($dblink,"CREATE TABLE IF NOT EXISTS `".DBP."events_log` ( `vkId` INT NOT NULL , `mlistId` INT NOT NULL , `dateTime` DATETIME NOT NULL , `eventTypeId` INT NOT NULL , `eventDataId` INT NOT NULL ) ENGINE = InnoDB");

mysqli_query($dblink,"CREATE TABLE IF NOT EXISTS `".DBP."chains` ( `chainId` INT NOT NULL AUTO_INCREMENT , `chainName`  VARCHAR(50) , PRIMARY KEY (`chainId`)) ENGINE = InnoDB");

mysqli_query($dblink,"CREATE TABLE IF NOT EXISTS `".DBP."chains_to_mlists` ( `chainId` INT NOT NULL , `mlistId` INT NOT NULL ) ENGINE = InnoDB");

mysqli_query($dblink,"ALTER TABLE `".DBP."chains_to_mlists` ADD UNIQUE (`chainId`, `mlistId`)");

mysqli_query($dblink,"CREATE TABLE IF NOT EXISTS `".DBP."chainMessages` ( `messageId` INT NOT NULL AUTO_INCREMENT , `chainId` INT NOT NULL , `messageText` TEXT NOT NULL , `messagePHP` TEXT NOT NULL , `preEventTypeId` INT NOT NULL , `preEventId` INT NOT NULL , `minutesToWait` INT NOT NULL , `timeToSend` TIME NOT NULL , `isActive` BOOLEAN NOT NULL , PRIMARY KEY (`messageId`)) ENGINE = InnoDB");



//echo 'MYSQL!!!!!!!!!!!!!!!!!!!!!!';

?>
