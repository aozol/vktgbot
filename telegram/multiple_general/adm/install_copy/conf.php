<?php


if(!defined('DB_NAME')){
// Данные для подключения к базе
define ('DB_NAME','alexozol_aozo1');
define ('DB_SERVER','localhost');
define ('DB_LOGIN','alexozol_aozo1');
define ('DB_PASS','ozOl1Xol2');

define ('ADM_VK_ID', 2204686);

}


$sql_query="

CREATE TABLE `%prefix%admin` (
  `id` int(11) NOT NULL,
  `login` varchar(20) NOT NULL,
  `pass` varchar(100) NOT NULL,
  `root` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `%prefix%vkAdmin` (
  `vkGroupId` bigint(20) NOT NULL,
  `vkId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `%prefix%admin`
--

INSERT INTO `%prefix%admin` (`id`, `login`, `pass`, `root`) VALUES
(1, '%adm_login%', '%adm_pass%', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%botReply`
--

CREATE TABLE `%prefix%botReply` (
  `vkGroupId` BIGINT(20) UNSIGNED NOT NULL,
  `payloadText` varchar(50) NOT NULL,
  `replyText` text NOT NULL,
  `php` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%db`
--

CREATE TABLE `%prefix%db` (
  `vkId` BIGINT(20) UNSIGNED NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `userSex` int(1) NOT NULL DEFAULT '0',
  `mlistId` int(11) NOT NULL,
  `finishDate` date DEFAULT NULL,
  `unsub` tinyint(1) NOT NULL DEFAULT '0',
  `vkGroupId` BIGINT(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%keyboard`
--

CREATE TABLE `%prefix%keyboard` (
  `vkId` BIGINT(20) UNSIGNED NOT NULL,
  `buttonText` varchar(50) NOT NULL,
  `payloadText` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%mlists`
--

CREATE TABLE `%prefix%mlists` (
  `mlistId` int(11) NOT NULL,
  `mlistName` varchar(50) NOT NULL,
  `isPublic` tinyint(1) NOT NULL DEFAULT '0',
  `isDefault` tinyint(1) NOT NULL DEFAULT '0',
  `vkGroupId` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%promocodes`
--

CREATE TABLE `%prefix%promocodes` (
  `promoCode` varchar(30) NOT NULL,
  `finDate` date DEFAULT NULL,
  `activationsN` int(11) NOT NULL,
  `activatedN` int(11) NOT NULL DEFAULT '0',
  `actionPHP` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%promocodeTemplates`
--

CREATE TABLE `%prefix%promocodeTemplates` (
  `pcTemplateId` int(11) NOT NULL,
  `pcTemplateName` varchar(20) NOT NULL,
  `pcTemplatePHP` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%userState`
--

CREATE TABLE `%prefix%userState` (
  `vkId` BIGINT(20) UNSIGNED NOT NULL,
  `payloadText` varchar(50) NOT NULL,
  `vkGroupId` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%vkApi`
--

CREATE TABLE `%prefix%vkApi` (
  `vkGroupId` BIGINT(20) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `confirmToken` varchar(8) NOT NULL,
  `secret` varchar(25) NOT NULL,
  `admin` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `%prefix%events` (
 `eventId` int(11) NOT NULL,
 `eventName` varchar(100) NOT NULL,
 `eventStart` date NOT NULL,
 `eventStartTime` time NOT NULL,
 `eventFinish` date NOT NULL,
 `eventFinishTime` time NOT NULL,
 `eventPreregStart` date NOT NULL,
 `eventRegStart` date NOT NULL,
 `eventPreregInfo` text NOT NULL,
 `eventPreregPHP` text NOT NULL,
 `eventRegInfo` text NOT NULL,
 `eventRegPHP` text NOT NULL,
  `vkGroupId` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
 PRIMARY KEY (`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `%prefix%events_reg_db` (
 `eventId` int(11) NOT NULL,
 `roleId` int(11) NOT NULL,
 `vkId` BIGINT(20) unsigned NOT NULL,
 `vkGroupId` bigint(20) unsigned NOT NULL,
 `unsub` tinyint(1) NOT NULL DEFAULT '0',
 UNIQUE KEY `eventId` (`eventId`,`roleId`,`vkId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `%prefix%events_roles` (
 `eventId` int(11) NOT NULL,
 `roleId` int(11) NOT NULL,
  `roleName` varchar(100) NOT NULL,
  `roleMessage` text NOT NULL,
  `rolePHP` text NOT NULL,
 UNIQUE KEY `eventId` (`eventId`,`roleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Дамп данных таблицы `%prefix%vkApi`
--

INSERT INTO `%prefix%admin` (`vkGroupId`) VALUES
(-1);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `%prefix%admin`
--
ALTER TABLE `%prefix%admin`
  ADD PRIMARY KEY (`id`);
COMMIT;

ALTER TABLE `%prefix%vkAdmin`
  ADD UNIQUE KEY `vkGroupId` (`vkGroupId`,`vkId`);
COMMIT;
--
-- Индексы таблицы `%prefix%botReply`
--
ALTER TABLE `%prefix%botReply`
  ADD UNIQUE KEY `vkGroupId` (`vkGroupId`,`payloadText`);
COMMIT;
--
-- Индексы таблицы `%prefix%db`
--
ALTER TABLE `%prefix%db`
  ADD UNIQUE KEY `vkId` (`vkId`,`mlistId`,`vkGroupId`);
COMMIT;
--
-- Индексы таблицы `%prefix%keyboard`
--
ALTER TABLE `%prefix%keyboard`
  ADD UNIQUE KEY `vkId` (`vkId`,`buttonText`);
COMMIT;
--
-- Индексы таблицы `%prefix%mlists`
--
ALTER TABLE `%prefix%mlists`
  ADD PRIMARY KEY (`mlistId`);
COMMIT;
--
-- Индексы таблицы `%prefix%promocodes`
--
ALTER TABLE `%prefix%promocodes`
  ADD UNIQUE KEY `promoCode` (`promoCode`,`finDate`);
COMMIT;
--
-- Индексы таблицы `%prefix%promocodeTemplates`
--
ALTER TABLE `%prefix%promocodeTemplates`
  ADD PRIMARY KEY (`pcTemplateId`);
COMMIT;
--
-- Индексы таблицы `%prefix%userState`
--
ALTER TABLE `%prefix%userState`
  ADD UNIQUE KEY `vkId` (`vkId`,  `vkGroupId` );
COMMIT;
--
-- Индексы таблицы `%prefix%vkApi`
--
ALTER TABLE `%prefix%vkApi`
  ADD PRIMARY KEY (`vkGroupId`);
COMMIT;
--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `%prefix%admin`
--
ALTER TABLE `%prefix%admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;
--
-- AUTO_INCREMENT для таблицы `%prefix%promocodeTemplates`
--
ALTER TABLE `%prefix%promocodeTemplates`
  MODIFY `pcTemplateId` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

-- AUTO_INCREMENT для таблицы `%prefix%events`
--
ALTER TABLE `%prefix%events`
  MODIFY `eventId` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
";



?>
 
