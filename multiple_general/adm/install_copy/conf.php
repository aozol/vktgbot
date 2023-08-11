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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `vkGroupId` int(11) NOT NULL,
  `payloadText` varchar(50) NOT NULL,
  `replyText` text NOT NULL,
  `php` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%db`
--

CREATE TABLE `%prefix%db` (
  `vkId` int(11) NOT NULL,
  `userSex` int(1) NOT NULL DEFAULT '0',
  `mlistId` int(11) NOT NULL,
  `finishDate` date DEFAULT NULL,
  `unsub` tinyint(1) NOT NULL DEFAULT '0',
  `vkGroupId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%keyboard`
--

CREATE TABLE `%prefix%keyboard` (
  `vkId` int(11) NOT NULL,
  `buttonText` varchar(50) NOT NULL,
  `payloadText` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%mlists`
--

CREATE TABLE `%prefix%mlists` (
  `mlistId` int(11) NOT NULL,
  `mlistName` varchar(50) NOT NULL,
  `isPublic` tinyint(1) NOT NULL DEFAULT '0',
  `isDefault` tinyint(1) NOT NULL DEFAULT '0',
  `vkGroupId` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%promocodeTemplates`
--

CREATE TABLE `%prefix%promocodeTemplates` (
  `pcTemplateId` int(11) NOT NULL,
  `pcTemplateName` varchar(20) NOT NULL,
  `pcTemplatePHP` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%userState`
--

CREATE TABLE `%prefix%userState` (
  `vkId` int(11) NOT NULL,
  `payloadText` varchar(50) NOT NULL,
  `vkGroupId` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `%prefix%vkApi`
--

CREATE TABLE `%prefix%vkApi` (
  `vkGroupId` int(10) NOT NULL,
  `token` varchar(255) NOT NULL,
  `confirmToken` varchar(8) NOT NULL,
  `secret` varchar(25) NOT NULL,
  `admin` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

--
-- Индексы таблицы `%prefix%botReply`
--
ALTER TABLE `%prefix%botReply`
  ADD UNIQUE KEY `vkGroupId` (`vkGroupId`,`payloadText`);

--
-- Индексы таблицы `%prefix%db`
--
ALTER TABLE `%prefix%db`
  ADD UNIQUE KEY `vkId` (`vkId`,`mlistId`,`vkGroupId`);

--
-- Индексы таблицы `%prefix%keyboard`
--
ALTER TABLE `%prefix%keyboard`
  ADD UNIQUE KEY `vkId` (`vkId`,`buttonText`);

--
-- Индексы таблицы `%prefix%mlists`
--
ALTER TABLE `%prefix%mlists`
  ADD PRIMARY KEY (`mlistId`);

--
-- Индексы таблицы `%prefix%promocodes`
--
ALTER TABLE `%prefix%promocodes`
  ADD UNIQUE KEY `promoCode` (`promoCode`,`finDate`);

--
-- Индексы таблицы `%prefix%promocodeTemplates`
--
ALTER TABLE `%prefix%promocodeTemplates`
  ADD PRIMARY KEY (`pcTemplateId`);

--
-- Индексы таблицы `%prefix%userState`
--
ALTER TABLE `%prefix%userState`
  ADD UNIQUE KEY `vkId` (`vkId`,  `vkGroupId` );

--
-- Индексы таблицы `%prefix%vkApi`
--
ALTER TABLE `%prefix%vkApi`
  ADD PRIMARY KEY (`vkGroupId`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `%prefix%admin`
--
ALTER TABLE `%prefix%admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `%prefix%promocodeTemplates`
--
ALTER TABLE `%prefix%promocodeTemplates`
  MODIFY `pcTemplateId` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
";



?>
 
