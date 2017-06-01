/* Part-DB SQL table creation
 *
 * Version: see Version variable
 */

SET @VERSION = "1.0";

DROP TABLE IF EXISTS `info`;
CREATE TABLE `info` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY UNIQUE,
  `key` NVARCHAR(64) NOT NULL,
  `value` NVARCHAR(256)
);
INSERT INTO `info` (`key`,`value`) VALUES ('version',@VERSION);

-- phpMyAdmin SQL Dump
-- version 4.4.15.8
-- https://www.phpmyadmin.net
--
-- Host: sql54.your-server.de
-- Erstellungszeit: 01. Apr 2017 um 21:10
-- Server-Version: 5.5.54-0+deb8u1
-- PHP-Version: 5.3.10-1ubuntu3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `partdb`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL UNIQUE,
  `name` mediumtext NOT NULL,
  `parentnode` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `datasheets`
--

DROP TABLE IF EXISTS `datasheets`;
CREATE TABLE IF NOT EXISTS `datasheets` (
  `id` int(11) NOT NULL UNIQUE,
  `part_id` int(11) NOT NULL DEFAULT '0',
  `datasheeturl` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `footprints`
--

DROP TABLE IF EXISTS `footprints`;
CREATE TABLE IF NOT EXISTS `footprints` (
  `id` int(11) NOT NULL UNIQUE,
  `name` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `parts`
--

DROP TABLE IF EXISTS `parts`;
CREATE TABLE IF NOT EXISTS `parts` (
  `id` int(11) NOT NULL UNIQUE,
  `id_category` int(11) NOT NULL DEFAULT '0',
  `name` mediumtext NOT NULL,
  `instock` int(11) NOT NULL DEFAULT '0',
  `mininstock` int(11) NOT NULL DEFAULT '0',
  `comment` mediumtext NOT NULL,
  `id_footprint` int(11) NOT NULL DEFAULT '0',
  `id_storeloc` int(11) NOT NULL DEFAULT '0',
  `id_supplier` int(11) NOT NULL DEFAULT '0',
  `supplierpartnr` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pending_orders`
--

DROP TABLE IF EXISTS `pending_orders`;
CREATE TABLE IF NOT EXISTS `pending_orders` (
  `id` int(11) NOT NULL UNIQUE,
  `part_id` int(11) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `t` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pictures`
--

DROP TABLE IF EXISTS `pictures`;
CREATE TABLE IF NOT EXISTS `pictures` (
  `id` int(11) NOT NULL UNIQUE,
  `part_id` int(11) NOT NULL DEFAULT '0' COMMENT 'ID of part this image belongs to',
  `pict_fname` varchar(255) NOT NULL DEFAULT '' COMMENT 'Picture filename',
  `pict_width` int(11) NOT NULL DEFAULT '0',
  `pict_height` int(11) NOT NULL DEFAULT '0',
  `pict_type` enum('P','T') NOT NULL DEFAULT 'P' COMMENT '`P` is full picture, `T` is thumbnail',
  `tn_obsolete` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Is 1 if thumbnail is outdated and must be regenerated',
  `tn_t` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Generation timestamp of thumbnail',
  `tn_pictid` int(11) NOT NULL DEFAULT '0' COMMENT 'Picture ID this thumbnail belongs to',
  `pict_masterpict` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Is 1 if this picture is used as grid icon for the referenced part'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `preise`
--

DROP TABLE IF EXISTS `prices`;
CREATE TABLE IF NOT EXISTS `prices` (
  `id` int(11) NOT NULL UNIQUE,
  `part_id` int(11) NOT NULL DEFAULT '0',
  `ma` smallint(6) NOT NULL DEFAULT '0' COMMENT '?',
  `price` decimal(6,2) NOT NULL DEFAULT '0.00',
  `t` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Timestamp at which this price info was updated'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `storeloc`
--

DROP TABLE IF EXISTS `storeloc`;
CREATE TABLE IF NOT EXISTS `storeloc` (
  `id` int(11) unsigned NOT NULL UNIQUE,
  `name` tinytext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int(11) NOT NULL UNIQUE,
  `name` tinytext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parentnode` (`parentnode`);

--
-- Indizes für die Tabelle `datasheets`
--
ALTER TABLE `datasheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `part_id` (`part_id`);

--
-- Indizes für die Tabelle `footprints`
--
ALTER TABLE `footprints`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_storeloc` (`id_storeloc`),
  ADD KEY `id_category` (`id_category`);

--
-- Indizes für die Tabelle `pending_orders`
--
ALTER TABLE `pending_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `part_id` (`part_id`);

--
-- Indizes für die Tabelle `pictures`
--
ALTER TABLE `pictures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pict_type` (`pict_type`);

--
-- Indizes für die Tabelle `prices`
--
ALTER TABLE `prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `part_id` (`part_id`),
  ADD KEY `ma` (`ma`);

--
-- Indizes für die Tabelle `storeloc`
--
ALTER TABLE `storeloc`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `datasheets`
--
ALTER TABLE `datasheets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `footprints`
--
ALTER TABLE `footprints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `parts`
--
ALTER TABLE `parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `pending_orders`
--
ALTER TABLE `pending_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `pictures`
--
ALTER TABLE `pictures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `preise`
--
ALTER TABLE `prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `storeloc`
--
ALTER TABLE `storeloc`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
