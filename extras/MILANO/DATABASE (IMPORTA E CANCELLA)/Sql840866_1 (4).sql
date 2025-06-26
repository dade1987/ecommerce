-- phpMyAdmin SQL Dump
-- version 3.4.7.1
-- http://www.phpmyadmin.net
--
-- Host: 62.149.150.200
-- Generato il: Dic 29, 2015 alle 22:48
-- Versione del server: 5.5.45
-- Versione PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `Sql840866_1`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `bolle_resi`
--

CREATE TABLE IF NOT EXISTS `bolle_resi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bolla` longtext COLLATE utf8_unicode_ci NOT NULL,
  `destinatario` text COLLATE utf8_unicode_ci NOT NULL,
  `mittente` text COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `db_fatture`
--

CREATE TABLE IF NOT EXISTS `db_fatture` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fattura` longtext COLLATE latin1_general_ci NOT NULL,
  `negozio` text COLLATE latin1_general_ci NOT NULL,
  `numero_fattura` int(11) NOT NULL,
  `data` text COLLATE latin1_general_ci NOT NULL,
  `intestatario` text COLLATE latin1_general_ci NOT NULL,
  `visualizzato` tinyint(1) NOT NULL DEFAULT '0',
  `totale_fattura` text COLLATE latin1_general_ci NOT NULL,
  `importo_pagato` float NOT NULL DEFAULT '0',
  `anno` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=924 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `db_fidelity_card`
--

CREATE TABLE IF NOT EXISTS `db_fidelity_card` (
  `numero` bigint(13) NOT NULL AUTO_INCREMENT,
  `numero_vecchio` text NOT NULL,
  `nome` text NOT NULL,
  `cognome` text NOT NULL,
  `negozio_riferimento` text NOT NULL,
  `telefono` text NOT NULL,
  `email` text NOT NULL,
  `punti` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `numero` (`numero`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5028 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `db_ordini`
--

CREATE TABLE IF NOT EXISTS `db_ordini` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` text NOT NULL,
  `negozio` text NOT NULL,
  `data` text NOT NULL,
  `intestatario` text NOT NULL,
  `totale` text NOT NULL,
  `visualizzato` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `db_scontrini`
--

CREATE TABLE IF NOT EXISTS `db_scontrini` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negozio` text NOT NULL,
  `dati_chiusura` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=82 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `ddt`
--

CREATE TABLE IF NOT EXISTS `ddt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL,
  `ddt` longtext COLLATE utf8_unicode_ci NOT NULL,
  `mittente` text COLLATE utf8_unicode_ci NOT NULL,
  `negozio` text COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `visualizzato` tinyint(1) NOT NULL DEFAULT '0',
  `tipo` text COLLATE utf8_unicode_ci NOT NULL,
  `codice_tracciatura` text COLLATE utf8_unicode_ci NOT NULL,
  `anno` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1373 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `elenco_movimenti`
--

CREATE TABLE IF NOT EXISTS `elenco_movimenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` datetime NOT NULL,
  `causale` varchar(35) NOT NULL,
  `codice` varchar(15) NOT NULL,
  `barcode` text NOT NULL,
  `descrizione` varchar(30) NOT NULL,
  `gruppo` varchar(25) NOT NULL,
  `colore` varchar(15) NOT NULL,
  `prezzo_pubblico_unitario` float NOT NULL,
  `quantita` int(3) NOT NULL,
  `fornitore` varchar(35) NOT NULL,
  `cliente` varchar(35) NOT NULL,
  `sconto_affiliato` int(4) NOT NULL,
  `sconto_pubblico` int(4) NOT NULL,
  `reso` int(11) NOT NULL,
  `giacenza_minima` int(11) NOT NULL,
  `pagamento` text NOT NULL,
  `totale` float NOT NULL,
  `saldo` float NOT NULL,
  `resto` float NOT NULL,
  `identificativo` text NOT NULL,
  `cognome` text NOT NULL,
  `nome` text NOT NULL,
  `indirizzo` text NOT NULL,
  `citta` text NOT NULL,
  `cap` text NOT NULL,
  `piva` text NOT NULL,
  `costo_aziendale` text NOT NULL,
  `numero_ddt` int(11) NOT NULL,
  `codice_fornitore` text NOT NULL,
  `attivo` int(11) NOT NULL DEFAULT '1',
  `materiale` text NOT NULL,
  `sconto_saldo` tinyint(1) NOT NULL DEFAULT '0',
  `acquisto_personale` tinyint(1) NOT NULL DEFAULT '0',
  `escluso_fattura` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `causale` (`causale`),
  FULLTEXT KEY `fornitore` (`fornitore`),
  FULLTEXT KEY `cliente` (`cliente`),
  FULLTEXT KEY `barcode` (`barcode`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=168148 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `numerazione_ddt`
--

CREATE TABLE IF NOT EXISTS `numerazione_ddt` (
  `numero_ddt` int(11) NOT NULL AUTO_INCREMENT,
  `fattura_corrispondente` int(11) NOT NULL,
  `bolla_corrispondente` int(11) NOT NULL,
  PRIMARY KEY (`numero_ddt`),
  UNIQUE KEY `numero_ddt` (`numero_ddt`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=618 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `sessioni`
--

CREATE TABLE IF NOT EXISTS `sessioni` (
  `uid` char(32) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `creation_date` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `sessioni_salvate`
--

CREATE TABLE IF NOT EXISTS `sessioni_salvate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessione` longtext NOT NULL,
  `data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `negozio` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5577 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
