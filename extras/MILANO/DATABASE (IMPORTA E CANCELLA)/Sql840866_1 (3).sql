-- phpMyAdmin SQL Dump
-- version 3.4.7.1
-- http://www.phpmyadmin.net
--
-- Host: 62.149.150.200
-- Generato il: Dic 29, 2015 alle 22:46
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
-- Struttura della tabella `colori`
--

CREATE TABLE IF NOT EXISTS `colori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

--
-- Dump dei dati per la tabella `colori`
--

INSERT INTO `colori` (`id`, `nome`) VALUES
(1, 'ARGENTO'),
(2, 'ORO'),
(3, 'NERO'),
(4, 'GRIGIO'),
(5, 'ROSSO'),
(6, 'BIANCO'),
(7, 'VERDE'),
(8, 'BLU'),
(9, 'MARRONE'),
(10, 'VIOLA'),
(11, 'GIALLO'),
(20, 'ROSA'),
(19, 'BEIJE'),
(18, 'NEUTRO'),
(21, 'AZZURRO'),
(22, 'ARANCIONE'),
(23, 'MULTICOLOR'),
(24, 'NEROBIANCO'),
(25, 'FUCSIA'),
(26, 'AI 2015-16');

-- --------------------------------------------------------

--
-- Struttura della tabella `etichette_materiali`
--

CREATE TABLE IF NOT EXISTS `etichette_materiali` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `materiale` text NOT NULL,
  `etichetta` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=36 ;

--
-- Dump dei dati per la tabella `etichette_materiali`
--

INSERT INTO `etichette_materiali` (`id`, `materiale`, `etichetta`) VALUES
(1, 'STRASS METALLO-SENZA NICHEL', ''),
(2, 'strass-pvc SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(3, 'strass-metallo SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(4, 'pvc SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(5, 'pvc-metallo SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(6, 'pvc-legno SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(7, 'metallo-pvc SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(8, 'metallo-legno SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(9, 'CRISTALLO SENZA NICHEL', ''),
(10, 'vetro-strass SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(11, 'vetro-pvc SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(12, 'vetro-metallo SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(13, 'vetro SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(14, 'cuoio SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(15, 'cuoio/strass SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(16, 'legno-cuoio/metallo SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(17, 'legno/strass SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(18, 'legno/metallo SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(19, 'pietra SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(20, 'pietra/metallo SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(21, 'pietra/vetro SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(22, 'metallo/vetro SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(23, 'metallo SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(24, 'tessuto SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(25, 'tessuto/strass SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(26, 'tessuto/metallo SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(27, 'vetro SENZA NICHEL', 'Non adatto a bambini di età inferiore a 3 anni.'),
(33, 'ACCIAIO SENZA NICHEL', ''),
(28, 'TESSUTO LEGNO', ''),
(29, 'TESSUTO LEGNO SENZA NICHEL', ''),
(30, 'TESSUTO VETRO SENZA NICHEL', ''),
(31, 'TESSUTO METALLO SENZA NICHEL', ''),
(32, 'METALLO POLIESTERE', ''),
(34, 'cuoio-metallo senza nichel', ''),
(35, 'PELLE VERA', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `gruppi`
--

CREATE TABLE IF NOT EXISTS `gruppi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` text NOT NULL,
  `sconto_azienda` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=341 ;

--
-- Dump dei dati per la tabella `gruppi`
--

INSERT INTO `gruppi` (`id`, `nome`, `sconto_azienda`) VALUES
(1, 'ACCESSORI CAPELLI', 60),
(2, 'BRACCIALI', 60),
(3, 'ORECCHINI', 60),
(4, 'SPILLE', 60),
(5, 'PORTACHIAVI', 60),
(6, 'COLLANE', 60),
(7, 'ANELLI', 60),
(8, 'CERCHIELLI', 60),
(9, 'CAVIGLIERE', 60),
(10, 'BAULETTI', 50),
(11, 'POCHETTE ECOPELLE', 50),
(12, 'ACCESSORI', 50),
(13, 'BORSE', 50),
(14, 'COVER', 50),
(15, 'FOULARD', 50),
(16, 'CAPPELLI PAGLIA', 50),
(17, 'CINTURE', 50),
(18, 'PORTAFOGLI', 50),
(19, 'OCCHIALI', 50),
(20, 'OMBRELLI', 50),
(21, 'BAMBOLE', 50),
(22, 'ABBIGLIAMENTO', 50),
(23, 'CALZATURE', 50),
(24, 'COSTUMI', 50),
(25, 'CO PERLE LUNGHE', 60),
(26, 'CO CRISTALLINI S', 60),
(27, 'CO CRISTALLINI L', 60),
(28, 'BR CRISTALLINI S', 60),
(29, 'BR CRISTALLINI L', 60),
(30, 'COLLARINI STRASS 1F', 60),
(31, 'COLLARINI STRASS 2F', 60),
(32, 'COLLARINI STRASS 3F', 60),
(33, 'PARURE STRASS', 60),
(34, 'CO MOROSITAS', 60),
(35, 'OR MOROSITAS S 8,90', 60),
(36, 'OR MOROSITAS M 9,90', 60),
(37, 'OR MOROSITAS L 10,90', 60),
(38, 'CERCHI LISCI XS 3,90', 60),
(39, 'CERCHI LISCI S 3.90', 60),
(40, 'CERCHI LISCI M 4,90', 60),
(41, 'CERCHI LISCI L 5,90', 0),
(43, 'CERCHI LISCI XL 6,90', 60),
(44, 'CERCHI LISCI XXL 7,90', 60),
(45, 'CERCHI STRASS XS', 60),
(46, 'CERCHI STRASS S', 60),
(47, 'CERCHI STRASS M', 60),
(48, 'CERCHI STRASS L', 60),
(49, 'CERCHI STRASS XL', 60),
(50, 'CERCHI STRASS XXL', 60),
(51, 'BR TENNIS ACCIAIO', 60),
(52, 'CO PUNTO LUCE SPOSA', 60),
(53, 'PINZE LISCE', 60),
(54, 'PINZE STRASS', 60),
(55, 'MOLLETTINE LISCE', 60),
(56, 'MOLLETTINE STRASS', 60),
(57, 'CLICK CLACK LISCE', 60),
(58, 'CLICK CLACK STRASS', 60),
(59, 'FORCINE LISCE', 60),
(60, 'FORCINE STRASS', 60),
(61, 'ELASTICI', 60),
(62, 'CLUTCH', 50),
(63, 'CLUTCH STRASS ', 50),
(64, 'CINTURINE STRASS 1F', 50),
(65, 'CINTURINE STRASS 2F', 50),
(66, 'CINTURINE STRASS 3F', 50),
(67, 'SPECCHIETTI', 50),
(68, 'PORTABORSE', 50),
(69, 'VENTAGLI', 50),
(70, 'SET MANICURE', 50),
(71, 'BEAUTY CASE', 50),
(72, 'ROID', 60),
(73, 'EARCUFF', 60),
(74, 'FASCE 2.90', 60),
(75, 'ROID PENDENTE', 60),
(76, 'ROID CERCHIO', 60),
(77, 'PARURE STRASS', 60),
(78, 'CO KING', 60),
(79, 'AVAMBRACCIO', 60),
(80, 'CO ROSARIO CORTO', 60),
(81, 'CO PIETRA', 60),
(82, 'CO OROLOGIO ANTICATO', 60),
(83, 'PIN UP', 60),
(84, 'FIORI', 60),
(85, 'SPUGNE CHIGNON L 4.90', 60),
(86, 'SPUGNE CHIGNON S 2.90', 60),
(87, 'CO LETTERE', 60),
(88, 'CRAVATTE STRASS', 60),
(89, 'CATENINE CORTE', 60),
(90, 'CORONCINE', 60),
(91, 'BR TENNIS 1F', 60),
(92, 'BR TENNIS 2F', 60),
(93, 'BR TENNIS 3F', 60),
(94, 'BR TENNIS 4F', 60),
(95, 'BR TENNIS 5F', 60),
(96, 'BR TENNNIS 6F', 60),
(97, 'BR PERLE S', 60),
(98, 'BR PERLE L', 60),
(99, 'OR CLIP', 60),
(100, 'COLLETTI', 60),
(101, 'FASCE BANDANA', 60),
(102, 'MASCHERE', 50),
(103, 'PIERCING', 60),
(104, 'SCIARPE LANA', 50),
(105, 'CAPPELLI LANA', 50),
(106, 'GUANTI LANA', 50),
(107, 'PARAORECCHIE', 50),
(108, 'BR TENNIS 7F', 60),
(109, 'BR PERLE M 4.90', 60),
(110, 'CO PERLE CORTE', 60),
(111, 'BR UOMO TOMMY 29', 60),
(112, 'FOULARINI COLLO', 50),
(113, 'CO VINTAGE', 60),
(114, 'SPUGNE CHIGNON M 3.90', 60),
(115, 'BR SW 2 GIRI', 60),
(116, 'PORTAMONETE', 50),
(117, 'CERCHIELLI VELETTE', 60),
(118, 'FORCINE MC', 60),
(119, 'BR OROLOGIO', 60),
(120, 'CO BIMBA', 60),
(121, 'BR BIMBA', 60),
(122, 'OR PUNTI LUCE', 60),
(123, 'CO ZIRCONE P LUCE', 60),
(124, 'ACC SPOSA', 50),
(125, 'CINTURINE STRASS 4F', 50),
(126, 'COLLARINO TATOO', 60),
(127, 'STOLA', 50),
(128, 'CO BEST FRIENDS', 60),
(129, 'CO CUORE SPEZZATO', 60),
(130, 'BR ESPOSITORE', 60),
(131, 'CLIPS SFUSE', 50),
(132, 'VESTITI CERIMONIA L', 50),
(133, 'PARURE ZIRCONE ', 60),
(134, 'CERCHIELLI ELASTICI L', 60),
(135, 'CERCHIELLI ELASTICI S', 60),
(136, 'BR KING STRASS', 60),
(137, 'BR ROSARIO', 60),
(138, 'CAUCCIU', 60),
(139, 'ELASTICI 12X', 60),
(140, 'FASCETTE MAN', 60),
(141, 'PARURE SCATOLETTE', 60),
(142, 'POCHETTE AGE', 50),
(143, 'CATENINE LUNGHE', 60),
(144, 'NASINI', 60),
(145, 'VARIE', 60),
(146, 'CO CRISTALLINI M', 60),
(147, 'CINTURINE STRASS 6F', 50),
(148, 'OR FILIGRANA 4.90', 60),
(274, 'CAPPELLO PIOGGIA', 50),
(150, 'OR KING STRASS', 60),
(151, 'TATUAGGI', 50),
(152, 'FASCE 3.90', 60),
(153, 'CINTURINE STRASS 5F', 50),
(154, 'CINTURINE STRASS 10F', 50),
(155, 'ELASTICI CIOCCHE ', 60),
(156, 'ELASTICI CAPELLI VERI', 60),
(157, 'CERCHIELLIELASTICI TRECCIA 3S', 60),
(158, 'CERCHIELLIELASTICI TRECCIA L', 60),
(159, 'OR FILIGRANA 6.90', 60),
(160, 'PICANDOEO', 60),
(161, 'CAUCCIU PENDAGLIO', 60),
(162, 'CO PIETRA PENDAGLIO 24', 60),
(163, 'VELETTE ', 60),
(164, 'BR CUOIO S BORCHIE05', 60),
(165, 'BR CUOIO S LISCIO04', 60),
(166, 'BR CUOIO M BORCHIE06', 60),
(167, 'BR CUOIO M LISCIO05', 60),
(168, 'BR CUOIO L BORCHIE07', 60),
(169, 'BR CUOIO L LISCIO06', 60),
(170, 'CERCHI STRASS XXS 890', 60),
(171, 'CAPPELLI MAGLINA', 50),
(172, 'PONCHO LANA', 50),
(173, 'OR VENTAGLIO L', 60),
(174, 'OR VENTAGLIO S', 60),
(175, 'CO CHIAMA-ANGELI', 60),
(176, 'CINTURINE STRASS 7F', 50),
(177, 'OR RETE STRASS S', 60),
(178, 'OR RETE STRASS L ', 60),
(179, 'VESTITI CERIMONIA S', 50),
(180, 'VESTITO', 50),
(181, 'OR ZIRCONE L', 60),
(182, 'OR ZIRCONE S', 50),
(183, 'GIACCA', 50),
(184, 'PANTALONI', 50),
(185, 'GONNA', 50),
(186, 'PELLICCIOTTO', 50),
(187, 'GIUBBINO', 50),
(188, 'CAPPOTTO', 50),
(189, 'GOLFINO', 50),
(190, 'MAGLIA MANICA LUNGA', 50),
(191, 'MAGLIA MANICA CORTA ', 50),
(192, 'CAMICIE', 50),
(193, 'OR CLIP CERCHI XS 690', 60),
(194, 'OR CLIP CERCHI L 890', 60),
(195, 'POCHETTE PERLINE', 50),
(196, 'ADDOBBO CAPELLI', 60),
(197, 'COLLO LANA', 50),
(198, 'SCIARPE LUXURY', 50),
(199, 'PONCHO PELLICCIA', 50),
(200, 'SCIARPE CASHMERE', 50),
(201, 'POCHETTE RIGIDE', 50),
(202, 'CERCHIELLI ELASTICI STRASS', 60),
(203, 'CO PUNTO LUCE', 60),
(204, 'CO LETTERA A', 60),
(205, 'CO LETTERA B', 60),
(206, 'CO LETTERA C', 60),
(207, 'CO LETTERA D', 60),
(208, 'CO LETTERA E', 60),
(209, 'CO LETTERA F', 60),
(210, 'CO LETTERA G', 60),
(211, 'CO LETTERA I', 60),
(212, 'CO LETTERA L', 60),
(213, 'CO LETTERA M', 60),
(214, 'CO LETTERA N', 60),
(215, 'CO LETTERA O', 60),
(216, 'CO LETTERA P', 60),
(217, 'CO LETTERA R', 60),
(218, 'CO LETTERA S', 60),
(219, 'CO LETTERA T', 60),
(220, 'CO LETTERA V', 60),
(221, 'FASCE PARTY', 60),
(222, 'CO LETTERA K', 60),
(223, 'CO LETTERA W', 60),
(224, 'CO LETTERA J', 60),
(225, 'CO LETTERA Y', 60),
(226, 'STRASS CAPELLI', 60),
(227, 'GUANTINI ECOPELLE', 50),
(228, 'FUSCIACCA', 50),
(229, 'CAPPELLI FELTRO', 50),
(230, 'GIFT CARD', 0),
(231, 'CANOTTE ', 50),
(232, 'SPILLONI LISCI S', 50),
(233, 'SPILLONI LISCI L', 50),
(234, 'SPILLONI AGO', 50),
(235, 'SPILLA BALIA STRASS', 50),
(236, 'SPILLONI STRASS S', 50),
(237, 'MAGLIONE', 50),
(238, 'CO UOMO', 60),
(239, 'BR UOMO DM 14', 60),
(240, 'POCHETTE VERA PELLE', 50),
(241, 'CRAVATTE', 50),
(242, 'GUANTI MAGLIA', 50),
(243, 'PERLINE BOX', 50),
(244, 'GUANTI PELLE', 50),
(245, 'COLLO PELLICCIA', 50),
(246, 'GUANTI PIZZO', 50),
(247, 'PORTACHIAVI PON PON', 50),
(248, 'BR PUNTO LUCE FLEX', 60),
(249, 'BR PUNTO LUCE RIGIDO', 60),
(250, 'BR UOMO LUXUY 19', 60),
(251, 'MANICOTTO LANA', 50),
(252, 'COPRISPALLE PELLICCIA ', 50),
(253, 'CRAVATTE BOX', 50),
(254, 'P.L. OR PERLE XXS', 60),
(255, 'P.L. OR PERLE XS', 60),
(256, 'P.L. OR PERLE S', 60),
(257, 'P.L. OR PERLE M', 60),
(258, 'P.L. OR PERLE L 4', 60),
(259, 'P.L. OR PERLE XL', 60),
(260, 'P.L. OR PERLE XXL', 60),
(261, 'P.L. OR BRILLANTINI-GRAFF XXS', 60),
(262, 'P.L. OR BRILLANTINI-GRAFF XS', 60),
(263, 'P.L. OR BRILLANTINI-GRAFF S', 60),
(264, 'P.L. OR BRILLANTINI-GRAFF M', 60),
(265, 'P.L. OR BRILLANTINI-GRAFF L', 60),
(266, 'P.L. OR BRILLANTINI-GRAFF XL', 60),
(267, 'P.L. OR BRILLANTINI-GRAFF XXL', 60),
(268, 'P.L. OR MOROSITAS XXS', 60),
(269, 'P.L. OR MOROSITAS XS', 60),
(270, 'P.L. OR MOROSITAS S', 60),
(271, 'P.L. OR MOROSITAS M', 60),
(272, 'P.L. OR MOROSITAS XL', 60),
(273, 'P.L. OR MOROSITAS L', 60),
(275, 'P.L. OR BRILLANT-LISCIO XXS', 50),
(276, 'P.L. OR BRILLANT-LISCIO XS', 50),
(277, 'P.L. OR BRILLANT-LISCIO S', 50),
(278, 'P.L. OR BRILLANT-LISCIO M', 60),
(279, 'P.L. OR BRILLANT-LISCIO L', 60),
(280, 'P.L. OR BRILLANT-LISCIO XL', 60),
(281, 'P.L. OR BRILLANT-LISCIO XXL', 60),
(282, 'P.L. OR BRILLANT-QUADR XXS', 60),
(283, 'P.L. OR BRILLANT-QUADR XS', 60),
(284, 'P.L. OR BRILLANT-QUADR S', 60),
(285, 'P.L. OR BRILLANT-QUADR M', 60),
(286, 'P.L. OR BRILLANT-QUADR L', 60),
(287, 'P.L. OR BRILLANT-QUADR XL', 60),
(288, 'P.L. OR BRILLANT-QUADR XXL', 60),
(289, 'PORTAFOGLI UOMO', 50),
(290, 'CINTURE UOMO', 50),
(291, 'SCIARPA UOMO', 50),
(292, 'SET ACCESSORI UOMO BOX', 50),
(293, 'SCIARPA UOMO BOX', 50),
(294, ' FASCE CAPELLI LANA', 50),
(295, 'CERCHIELLI STRASS', 60),
(296, 'SPILLONI SPOSA', 50),
(297, 'CO ROSARIO LUNGO', 60),
(298, 'BRACCIALI RIGIDI', 60),
(299, 'OROLOGI INDIAN', 50),
(300, 'BACIAMANO', 50),
(301, 'BACIAMANO', 50),
(302, 'BR PELLICCIA', 60),
(303, 'FAKE SEPTUM STRASS', 60),
(304, 'PINZETTE SACCHETTINO', 50),
(305, 'PORTACHIAVI LETT A', 60),
(306, 'PORTACHIAVI LET B', 60),
(307, 'PORTACHIAVI LET C', 60),
(308, 'PORTACHIAVI LET D', 60),
(309, 'PORTACHIAVI LET E', 60),
(310, 'PORTACHIAVI LET F', 60),
(311, 'PORTACHIAVI LET G', 60),
(312, 'PORTACHIAVI LET I', 60),
(313, 'PORTACHIAVI LET L', 60),
(314, 'PORTACHIAVI LET M', 60),
(315, 'PORTACHIAVI LET N', 60),
(316, 'PORTACHIAVI LET O', 60),
(317, 'PORTACHIAVI LET P', 60),
(318, 'PORTACHIAVI LET R', 60),
(319, 'PORTACHIAVI LET S', 60),
(320, 'PORTACHIAVI LET T', 60),
(321, 'PORTACHIAVI LET V', 60),
(322, 'PORTACHIAVI LET X', 60),
(323, 'PORTACHIAVI LET Y', 60),
(324, 'PORTACHIAVI LET J', 60),
(325, 'PORTACHIAVI LET W', 60),
(326, 'PORTACHIAVI LET X', 60),
(327, 'PORTACHIAVI LET Y', 60),
(328, 'PELLICCIOTTO COLLO', 50),
(329, 'COPRISPALLA', 50),
(330, 'foular lux', 50),
(331, 'CERCHI LISCI XXXS', 60),
(332, 'GUANTI VELLUTO', 50),
(333, 'GUANTI RASO LUNGHI', 50),
(334, 'FOULARD DONNA BOX', 50),
(335, 'CO PERLE SCARAMAZZE L', 60),
(336, 'PIERCING LUXURY', 50),
(337, 'or magnetici', 60),
(338, 'collarini strass', 60),
(339, 'FORCINE COLORATE', 60),
(340, 'VESTITI CHIC', 50);

-- --------------------------------------------------------

--
-- Struttura della tabella `magazzino_base`
--

CREATE TABLE IF NOT EXISTS `magazzino_base` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negozio` text NOT NULL,
  `pannello` text NOT NULL,
  `bool` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=287 ;

--
-- Dump dei dati per la tabella `magazzino_base`
--

INSERT INTO `magazzino_base` (`id`, `negozio`, `pannello`, `bool`) VALUES
(199, 'BF NICOSIA', 'ARGENTO 1', 'checked'),
(200, 'BF NICOSIA', 'ARGENTO 2', ''),
(201, 'BF NICOSIA', 'ORO 1', ''),
(202, 'BF NICOSIA', 'PROVA', 'checked'),
(203, 'BLACK FASHION (Amico Irene)', 'ARGENTO 1', ''),
(204, 'BLACK FASHION (Amico Irene)', 'ARGENTO 2', ''),
(205, 'BLACK FASHION (Amico Irene)', 'ORO 1', ''),
(206, 'BLACK FASHION (Amico Irene)', 'PROVA', 'checked'),
(207, 'BLACK FASHION (Esse Erre)', 'ARGENTO 1', ''),
(208, 'BLACK FASHION (Esse Erre)', 'ARGENTO 2', ''),
(209, 'BLACK FASHION (Esse Erre)', 'ORO 1', ''),
(210, 'BLACK FASHION (Esse Erre)', 'PROVA', ''),
(211, 'BLACK FASHION BIELLA', 'ARGENTO 1', ''),
(212, 'BLACK FASHION BIELLA', 'ARGENTO 2', ''),
(213, 'BLACK FASHION BIELLA', 'ORO 1', ''),
(214, 'BLACK FASHION BIELLA', 'PROVA', 'checked'),
(215, 'BLACK FASHION BITONTO', 'ARGENTO 1', ''),
(216, 'BLACK FASHION BITONTO', 'ARGENTO 2', ''),
(217, 'BLACK FASHION BITONTO', 'ORO 1', ''),
(218, 'BLACK FASHION BITONTO', 'PROVA', 'checked'),
(219, 'BLACK FASHION CASTELFRANCO', 'ARGENTO 1', ''),
(220, 'BLACK FASHION CASTELFRANCO', 'ARGENTO 2', ''),
(221, 'BLACK FASHION CASTELFRANCO', 'ORO 1', ''),
(222, 'BLACK FASHION CASTELFRANCO', 'PROVA', ''),
(223, 'BLACK FASHION GALATINA', 'ARGENTO 1', ''),
(224, 'BLACK FASHION GALATINA', 'ARGENTO 2', ''),
(225, 'BLACK FASHION GALATINA', 'ORO 1', ''),
(226, 'BLACK FASHION GALATINA', 'PROVA', ''),
(227, 'BLACK FASHION LECCE', 'ARGENTO 1', ''),
(228, 'BLACK FASHION LECCE', 'ARGENTO 2', ''),
(229, 'BLACK FASHION LECCE', 'ORO 1', ''),
(230, 'BLACK FASHION LECCE', 'PROVA', ''),
(231, 'BLACK FASHION MACERATA', 'ARGENTO 1', ''),
(232, 'BLACK FASHION MACERATA', 'ARGENTO 2', ''),
(233, 'BLACK FASHION MACERATA', 'ORO 1', ''),
(234, 'BLACK FASHION MACERATA', 'PROVA', ''),
(235, 'BLACK FASHION MEGLIADINO', 'ARGENTO 1', ''),
(236, 'BLACK FASHION MEGLIADINO', 'ARGENTO 2', ''),
(237, 'BLACK FASHION MEGLIADINO', 'ORO 1', ''),
(238, 'BLACK FASHION MEGLIADINO', 'PROVA', ''),
(239, 'BLACK FASHION MONTEBELLUNA', 'ARGENTO 1', ''),
(240, 'BLACK FASHION MONTEBELLUNA', 'ARGENTO 2', ''),
(241, 'BLACK FASHION MONTEBELLUNA', 'ORO 1', ''),
(242, 'BLACK FASHION MONTEBELLUNA', 'PROVA', ''),
(243, 'BLACK FASHION ODERZO', 'ARGENTO 1', ''),
(244, 'BLACK FASHION ODERZO', 'ARGENTO 2', ''),
(245, 'BLACK FASHION ODERZO', 'ORO 1', ''),
(246, 'BLACK FASHION ODERZO', 'PROVA', ''),
(247, 'BLACK FASHION PORTOFERRAIO', 'ARGENTO 1', ''),
(248, 'BLACK FASHION PORTOFERRAIO', 'ARGENTO 2', ''),
(249, 'BLACK FASHION PORTOFERRAIO', 'ORO 1', ''),
(250, 'BLACK FASHION PORTOFERRAIO', 'PROVA', ''),
(251, 'BLACK FASHION POTENZA', 'ARGENTO 1', ''),
(252, 'BLACK FASHION POTENZA', 'ARGENTO 2', ''),
(253, 'BLACK FASHION POTENZA', 'ORO 1', ''),
(254, 'BLACK FASHION POTENZA', 'PROVA', ''),
(255, 'BLACK FASHION RIETI', 'ARGENTO 1', ''),
(256, 'BLACK FASHION RIETI', 'ARGENTO 2', ''),
(257, 'BLACK FASHION RIETI', 'ORO 1', ''),
(258, 'BLACK FASHION RIETI', 'PROVA', ''),
(259, 'BLACK FASHION TERNI', 'ARGENTO 1', ''),
(260, 'BLACK FASHION TERNI', 'ARGENTO 2', ''),
(261, 'BLACK FASHION TERNI', 'ORO 1', ''),
(262, 'BLACK FASHION TERNI', 'PROVA', ''),
(263, 'BLACK FASHION THIENE', 'ARGENTO 1', ''),
(264, 'BLACK FASHION THIENE', 'ARGENTO 2', ''),
(265, 'BLACK FASHION THIENE', 'ORO 1', ''),
(266, 'BLACK FASHION THIENE', 'PROVA', ''),
(267, 'BLACK FASHION VENEZIA', 'ARGENTO 1', ''),
(268, 'BLACK FASHION VENEZIA', 'ARGENTO 2', ''),
(269, 'BLACK FASHION VENEZIA', 'ORO 1', ''),
(270, 'BLACK FASHION VENEZIA', 'PROVA', ''),
(271, 'BLACK FASHION VICENZA', 'ARGENTO 1', ''),
(272, 'BLACK FASHION VICENZA', 'ARGENTO 2', ''),
(273, 'BLACK FASHION VICENZA', 'ORO 1', ''),
(274, 'BLACK FASHION VICENZA', 'PROVA', ''),
(275, 'ESSE ERRE SAS', 'ARGENTO 1', ''),
(276, 'ESSE ERRE SAS', 'ARGENTO 2', ''),
(277, 'ESSE ERRE SAS', 'ORO 1', ''),
(278, 'ESSE ERRE SAS', 'PROVA', ''),
(279, 'GRAZIELLA TRINELLI', 'ARGENTO 1', 'checked'),
(280, 'GRAZIELLA TRINELLI', 'ARGENTO 2', 'checked'),
(281, 'GRAZIELLA TRINELLI', 'ORO 1', 'checked'),
(282, 'GRAZIELLA TRINELLI', 'PROVA', ''),
(283, 'LAUDICINA VITO SALVATORE', 'ARGENTO 1', 'checked'),
(284, 'LAUDICINA VITO SALVATORE', 'ARGENTO 2', 'checked'),
(285, 'LAUDICINA VITO SALVATORE', 'ORO 1', 'checked'),
(286, 'LAUDICINA VITO SALVATORE', 'PROVA', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `negozi`
--

CREATE TABLE IF NOT EXISTS `negozi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Dump dei dati per la tabella `negozi`
--

INSERT INTO `negozi` (`id`, `nome`) VALUES
(1, 'Bra'),
(2, 'Chiavari'),
(3, 'Lecce'),
(4, 'Macerata'),
(5, 'Thiene'),
(6, 'Portoferraio'),
(7, 'Potenza'),
(8, 'Catania'),
(9, 'Trapani'),
(10, 'Megliadino'),
(11, 'Bitonto'),
(12, 'Marsala'),
(13, 'Rieti'),
(14, 'Galatina');

-- --------------------------------------------------------

--
-- Struttura della tabella `pannelli`
--

CREATE TABLE IF NOT EXISTS `pannelli` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` text NOT NULL,
  `gruppo` text NOT NULL,
  `colore` text NOT NULL,
  `quantita` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=564 ;

--
-- Dump dei dati per la tabella `pannelli`
--

INSERT INTO `pannelli` (`id`, `nome`, `gruppo`, `colore`, `quantita`) VALUES
(493, 'PROVA', 'COLLANE', 'ROSSO', 1000),
(494, 'ARGENTO 1', 'BR KING STRASS', 'ARGENTO', 3),
(495, 'ARGENTO 1', 'BR PUNTO LUCE FLEX', 'ARGENTO', 20),
(496, 'ARGENTO 1', 'BR PUNTO LUCE RIGIDO', 'ARGENTO', 10),
(497, 'ARGENTO 1', 'BR ROSARIO', 'ARGENTO', 2),
(498, 'ARGENTO 1', 'BRACCIALI', 'ARGENTO', 37),
(499, 'ARGENTO 1', 'BRACCIALI RIGIDI', 'ARGENTO', 10),
(500, 'ARGENTO 1', 'CERCHI LISCI L 5,90', 'ARGENTO', 4),
(501, 'ARGENTO 1', 'CERCHI LISCI M 4,90', 'ARGENTO', 4),
(502, 'ARGENTO 1', 'CERCHI LISCI S 3.90', 'ARGENTO', 6),
(503, 'ARGENTO 1', 'CERCHI LISCI XL 6,90', 'ARGENTO', 4),
(504, 'ARGENTO 1', 'CERCHI LISCI XS 3,90', 'ARGENTO', 6),
(505, 'ARGENTO 1', 'CERCHI LISCI XXL 7,90', 'ARGENTO', 3),
(506, 'ARGENTO 1', 'CERCHI STRASS L', 'ARGENTO', 3),
(507, 'ARGENTO 1', 'CERCHI STRASS M', 'ARGENTO', 3),
(508, 'ARGENTO 1', 'CERCHI STRASS S', 'ARGENTO', 3),
(509, 'ARGENTO 1', 'CERCHI STRASS XL', 'ARGENTO', 3),
(510, 'ARGENTO 1', 'CERCHI STRASS XS', 'ARGENTO', 3),
(511, 'ARGENTO 1', 'CERCHI STRASS XXL', 'ARGENTO', 3),
(512, 'ARGENTO 1', 'CERCHI STRASS XXS 890', 'ARGENTO', 6),
(513, 'ARGENTO 1', 'CO MOROSITAS', 'ARGENTO', 3),
(514, 'ARGENTO 1', 'CO PUNTO LUCE', 'ARGENTO', 12),
(515, 'ARGENTO 1', 'CO PUNTO LUCE SPOSA', 'ARGENTO', 2),
(516, 'ARGENTO 1', 'CO ROSARIO CORTO', 'ARGENTO', 2),
(517, 'ARGENTO 1', 'CO ROSARIO LUNGO', 'ARGENTO', 2),
(518, 'ARGENTO 1', 'CO ZIRCONE P LUCE', 'ARGENTO', 6),
(519, 'ARGENTO 1', 'COLLANE', 'ARGENTO', 26),
(520, 'ARGENTO 1', 'COLLANE KING', 'ARGENTO', 4),
(521, 'ARGENTO 1', 'COLLARINI STRASS 1F', 'ARGENTO', 2),
(522, 'ARGENTO 1', 'COLLARINI STRASS 2F', 'ARGENTO', 2),
(523, 'ARGENTO 1', 'COLLARINI STRASS 3F', 'ARGENTO', 2),
(524, 'ARGENTO 1', 'COLLETTI', 'ARGENTO', 2),
(525, 'ARGENTO 1', 'OR FILIGRANA 4.90', 'ARGENTO', 8),
(526, 'ARGENTO 1', 'OR FILIGRANA 6.90', 'ARGENTO', 4),
(527, 'ARGENTO 1', 'OR KING STRASS', 'ARGENTO', 6),
(528, 'ARGENTO 1', 'OR RETE STRASS L ', 'ARGENTO', 2),
(529, 'ARGENTO 1', 'OR RETE STRASS S', 'ARGENTO', 2),
(530, 'ARGENTO 1', 'OR ZIRCONE L', 'ARGENTO', 5),
(531, 'ARGENTO 1', 'OR ZIRCONE S', 'ARGENTO', 5),
(532, 'ARGENTO 1', 'ORECCHINI', 'ARGENTO', 65),
(533, 'ARGENTO 2', 'BRACCIALI', 'ARGENTO', 40),
(534, 'ARGENTO 2', 'BRACCIALI RIGIDI', 'ARGENTO', 10),
(535, 'ARGENTO 2', 'CO PUNTO LUCE', 'ARGENTO', 14),
(536, 'ARGENTO 2', 'CO ZIRCONE P LUCE', 'ARGENTO', 6),
(537, 'ARGENTO 2', 'COLLANE', 'ARGENTO', 30),
(538, 'ARGENTO 2', 'COLLANE KING', 'ARGENTO', 3),
(539, 'ARGENTO 2', 'ORECCHINI', 'ARGENTO', 100),
(540, 'ORO 1', 'BR PUNTO LUCE FLEX', 'ORO', 20),
(541, 'ORO 1', 'BR PUNTO LUCE RIGIDO', 'ORO', 10),
(542, 'ORO 1', 'BR ROSARIO', 'ORO', 2),
(543, 'ORO 1', 'BRACCIALI', 'ORO', 38),
(544, 'ORO 1', 'CERCHI LISCI L 5,90', 'ORO', 3),
(545, 'ORO 1', 'CERCHI LISCI M 4,90', 'ORO', 3),
(546, 'ORO 1', 'CERCHI LISCI S 3.90', 'ORO', 4),
(547, 'ORO 1', 'CERCHI LISCI XL 6,90', 'ORO', 3),
(548, 'ORO 1', 'CERCHI LISCI XS 3,90', 'ORO', 6),
(549, 'ORO 1', 'CERCHI LISCI XXL 7,90', 'ORO', 3),
(550, 'ORO 1', 'CERCHI STRASS XXS 890', 'ORO', 6),
(551, 'ORO 1', 'CO CRISTALLINI L', 'ORO', 4),
(552, 'ORO 1', 'CO CRISTALLINI M', 'ORO', 4),
(553, 'ORO 1', 'CO CRISTALLINI S', 'ORO', 4),
(554, 'ORO 1', 'CO MOROSITAS', 'ORO', 3),
(555, 'ORO 1', 'CO PUNTO LUCE', 'ORO', 12),
(556, 'ORO 1', 'CO PUNTO LUCE SPOSA', 'ORO', 2),
(557, 'ORO 1', 'CO ROSARIO CORTO', 'ORO', 2),
(558, 'ORO 1', 'CO ROSARIO LUNGO', 'ORO', 2),
(559, 'ORO 1', 'CO ZIRCONE P LUCE', 'ORO', 6),
(560, 'ORO 1', 'COLLANE', 'ORO', 28),
(561, 'ORO 1', 'COLLANE KING', 'ORO', 3),
(562, 'ORO 1', 'COLLETTI', 'ORO', 2),
(563, 'ORO 1', 'ORECCHINI', 'ORO', 100);

-- --------------------------------------------------------

--
-- Struttura della tabella `permessi`
--

CREATE TABLE IF NOT EXISTS `permessi` (
  `id` int(10) unsigned NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descrizione` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `permessi`
--

INSERT INTO `permessi` (`id`, `nome`, `descrizione`) VALUES
(1, 'sede_centrale', 'Tutti i permessi'),
(2, 'affiliato', 'Permesso per i negozi affiliati');

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti3`
--

CREATE TABLE IF NOT EXISTS `utenti3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_sede_legale` text COLLATE utf8_unicode_ci NOT NULL,
  `indirizzo_sede_legale` text COLLATE utf8_unicode_ci NOT NULL,
  `citta_sede_legale` text COLLATE utf8_unicode_ci NOT NULL,
  `provincia_sede_legale` text COLLATE utf8_unicode_ci NOT NULL,
  `partita_iva_sede_legale` text COLLATE utf8_unicode_ci NOT NULL,
  `nome_negozio` text COLLATE utf8_unicode_ci NOT NULL,
  `indirizzo_negozio` text COLLATE utf8_unicode_ci NOT NULL,
  `citta_negozio` text COLLATE utf8_unicode_ci NOT NULL,
  `provincia_negozio` text COLLATE utf8_unicode_ci NOT NULL,
  `partita_iva_negozio` text COLLATE utf8_unicode_ci NOT NULL,
  `username` text COLLATE utf8_unicode_ci NOT NULL,
  `password` text COLLATE utf8_unicode_ci NOT NULL,
  `permessi` int(11) NOT NULL DEFAULT '2',
  `email` text COLLATE utf8_unicode_ci NOT NULL,
  `regdata` text COLLATE utf8_unicode_ci NOT NULL,
  `uid` text COLLATE utf8_unicode_ci NOT NULL,
  `codice_fiscale_negozio` text COLLATE utf8_unicode_ci NOT NULL,
  `codice_fiscale_sede_legale` text COLLATE utf8_unicode_ci NOT NULL,
  `2_euro` text COLLATE utf8_unicode_ci NOT NULL,
  `5_euro` text COLLATE utf8_unicode_ci NOT NULL,
  `50_perc` text COLLATE utf8_unicode_ci NOT NULL,
  `qt` text COLLATE utf8_unicode_ci NOT NULL,
  `20_perc` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=29 ;

--
-- Dump dei dati per la tabella `utenti3`
--

INSERT INTO `utenti3` (`id`, `nome_sede_legale`, `indirizzo_sede_legale`, `citta_sede_legale`, `provincia_sede_legale`, `partita_iva_sede_legale`, `nome_negozio`, `indirizzo_negozio`, `citta_negozio`, `provincia_negozio`, `partita_iva_negozio`, `username`, `password`, `permessi`, `email`, `regdata`, `uid`, `codice_fiscale_negozio`, `codice_fiscale_sede_legale`, `2_euro`, `5_euro`, `50_perc`, `qt`, `20_perc`) VALUES
(5, 'ESSE ERRE SAS', 'VIA E. DE AMICIS 14', 'Castelfranco Veneto', 'TV', '04589810268', 'ESSE ERRE SAS', 'VIA E.DE AMICIS, 14', 'Castelfranco Veneto', 'TV', '04589810268', 'Black_Fashion', '482c811da5d5b4bc6d497ffa98491e38', 1, '', '', '', '', '', '', '', '', '0', ''),
(9, 'LAUDICINA VITO SALVATORE', 'Via G.Battista Fardella, 46', 'Trapani', 'TP', '02573610819', 'LAUDICINA VITO SALVATORE', 'Via G.Battista Fardella, 46', 'Trapani', 'TP', '02573610819', 'Black_Trapani', 'ea48c5a9ccaf8f72e35c7656e2a187ed', 2, '', '', '', '', '', '', '', '', '0', ''),
(6, 'GRAZIELLA TRINELLI', 'VIA VITTORIO EMANUELE 237', 'BRA', 'CN', '02806590044', 'GRAZIELLA TRINELLI', 'VIA VITTORIO EMANUELE 237', 'BRA', 'CN', '02806590044', 'Black_Bra', '9bc8bdaf4c9bdaa83fe7780279511607', 2, '', '', '', '', 'TRNGZL69D45B111L', '', '', '', '0', ''),
(7, 'ESSE ERRE SAS', 'VIA E.DE AMICIS 14', 'CASTELFRANCO VENETO', 'TV', '04589810268', 'BLACK FASHION (Esse Erre)', 'VIA GRAMSCI, 31', 'MILANO MARITTIMA', 'CE', 'PROVA', 'Black_Milano.Marittima', '0f697e2695e6bfd7d880121a72054d7f', 2, '', '', '', 'PROVA', 'PROVA', '', '', '', '0', ''),
(8, 'AMICO IRENE', 'VIA XI MAGGIO 154', 'MARSALA', 'TP', '02542410812', 'BLACK FASHION (Amico Irene)', 'VIA XI MAGGIO 154', 'MARSALA', 'TP', '02542410812', 'Black_Marsala', 'c535f186b232e3676be1ec8cf3b900f2', 2, '', '', '', 'MCARNI94E59D423R', 'MCARNI94E59D423R', '', 'on', '', '16', ''),
(12, 'IL TUCANO DI DE SANTIS ROBERTO', 'VIA GALLIPOLI 28', 'GALATINA', 'LE', '04279760757', 'BLACK FASHION GALATINA', 'VIA ROMA 5', 'GALATINA', 'LE', '04279760757', 'Black_Galatina', 'f0a1d80faca34954efb36e3440b4a738', 2, '', '', '', 'DNSRRT72C28H501Y', 'DSNRRT72C28H501Y', '', '', '', '0', ''),
(13, 'VERONICA TOMA', 'PIAZZA SANT ORONZO 20', 'LECCE', 'LE', '04561400757', 'BLACK FASHION LECCE', 'PIAZZA SANT ORONZO 20', 'LECCE', 'LE', '04561400757', 'Black_Lecce', 'fe84e2121a8e9d937deea5ca744fef99', 2, '', '', '', 'TMOVCN80A45E506T', 'TMOVCN80A45E506T', 'on', 'on', 'on', '1000', ''),
(14, 'ALLBEST di Alberti Stefania', 'VIA PALOMBARA SNC', 'COTTANELLO', 'RI', '01121170573', 'BLACK FASHION RIETI', 'VIA FUNDANIA C.CLE PERSEO', 'RIETI', 'RI', '01121170573', 'Black_Rieti', 'b5bdc3e88de05349c068a0dbfdb5e5a1', 2, '', '', '', 'LBRSFN86D49H282J', 'LBRSFN86D49H282J', '', '', '', '0', ''),
(15, 'LA SAPONERIA SRL', 'VIA GIANNUTRI 37', 'PORTOFERRAIO', 'LI', '01710170497', 'BLACK FASHION PORTOFERRAIO', 'VIA GIANNUTRI 37', '57037 PORTOFERRAIO', 'LI', '01710170497', 'Black_Portoferraio', 'bc264389d215bcf2d4d79a9baac75389', 2, '', '', '', '01710170497', '01710170497', '', '', '', '0', ''),
(16, 'IROL DI CAIVANO LORIANA', 'VIA PRETORIA 260', 'POTENZA', 'PZ', '01868180769', 'BLACK FASHION POTENZA', 'VIA PRETORIA 260', 'POTENZA', 'PZ', '01868180769', 'Black_Potenza', '1e9cd91e24bdc9114c2bc424e0379e9a', 2, '', '', '', 'CVNLRN86D52G942A', 'CVNLRN86D52G942A', '', '', '', '0', ''),
(17, 'RS MODA DI SPADONE ROSANNA', 'VIA REPUBBLICA ITALIANA 78', 'BITONTO', 'BA', '07598990724', 'BLACK FASHION BITONTO', 'VIA REPUBBLICA ITALIANA, 78', 'BITONTO', 'BA', '07598990724', 'Black_Bitonto', '696a7c6fef08ab49fc7af92532bb4f6f', 2, '', '', '', 'SPDRNN76R71A893B', 'SPDRNN76R71A893B', '', '', '', '0', ''),
(18, 'IL MONDO DI MOMI SNC DI MONICA MATTIACCI', 'E MICHELA AGOSTINELLI', 'CORSO MATTEOTTI 9/11', 'MACERATA ', '01831420433', 'BLACK FASHION MACERATA', 'CORSO MATTEOTTI 9/11', 'MACERATA', 'MC', '01831420433', 'Black_Macerata', '97be2ef5474626f6d984f69f79e1fabb', 2, '', '', '', '01831420433', '01831420433', 'on', 'on', 'on', '1200', ''),
(19, 'E&C SNC DI ANNA CINZIA VANZO & C', 'PIAZZA G. ROSSI 9', 'THIENE', 'VI', '03842410247', 'BLACK FASHION THIENE', 'PIAZZA G. ROSSI 9', 'THIENE', 'VI', '03842410247', 'Black_Thiene', 'a22a8db67c9e1d820140b0e61ee7cca0', 2, '', '', '', '03842410247', '03842410247', '', '', '', '0', ''),
(20, 'NEW SEASON S.N.C. DI BROGGIATO ANNA & MUNARI ALESSANDRA', 'VIA VALLESELLA 1/1', 'MEGLIADINO SAN FIDENZIO', 'PD', '04413390289', 'BLACK FASHION MEGLIADINO', 'VIA VALLESELLA C.CLE MEGLIADINO', 'MEGLIADINO', 'PD', '04413390289', 'Black_Megliadino', '5ea03d27a01c923d6e943fc3a55b52ee', 2, '', '', '', '04413390289', '04413390289', '', '', '', '0', ''),
(21, 'CHAT NOIR DI DANIELA MINO ', 'VIA ITALIA 43', 'BIELLA', 'BI', '02525290025', 'BLACK FASHION BIELLA', 'VI ITALIA 43', 'BIELLA', 'BI', '02525290025', 'Black_Biella', '07a88e756847244f3496f63f473d6085', 2, '', '', '', 'MNIDNL60E65D094T', 'MNIDNL60E65D094T', '', '', '', '0', ''),
(22, 'BLACK FASHION DI SIMIONI RENZO', 'CORSO PALLADIO 122', 'VICENZA', 'VI', '043222200280', 'BLACK FASHION VICENZA', 'CORSO PALLADIO 122', 'VICENZA', 'VI', '043222200280', 'Black_Vicenza', '16355b376b7fd2e44b8bf9d5ce4706e6', 2, '', '', '', '043222200280', '043222200280', '', '', '', '0', ''),
(23, 'SIMIONI GIADA', 'VIA DE AMICIS 14', 'CASTELFRANCO VENETO', 'TV', 'SMNGDI86C68G224M', 'BLACK FASHION ODERZO', 'CORSO GALIBALDI 4', 'ODERZO', 'TV', '04149750269', 'Black_Oderzo', '35a73131df1bf1a8100b822c21f0a311', 2, '', '', '', 'SMNGDI86C68G224M', '04149750269', '', '', '', '0', ''),
(24, 'SIMIONI GRETA', 'VIA E. DE AMICIS, 14', 'CASTELFRANCO VENETO', 'TV', '04589810268', 'BLACK FASHION VENEZIA', '', '', '', '', 'Black_Venezia', '8a5b98f7be3d04197a1c2621116a1ec5', 2, '', '', '', '', '', '', '', 'on', '5', ''),
(25, 'FARMODA S.N.C', 'CONTRADA MAGNANA', 'NICOSIA', '', '', 'BF NICOSIA', 'CONTRADA MAGNANA', 'NICOSIA', '', '01210860860', 'Black_Nicosia', 'c66165751d5658b4110f991e43d9a432', 2, '', '', '', '', '01210860860', '', '', '', '0', ''),
(26, 'SIMIONI GRETA', 'VIA DE AMICIS 14', 'CASTELFRANCO VENETO', 'TV', '04301580264', 'BLACK FASHION CASTELFRANCO', 'Corte Franceschini 59L', 'castelfranco Veneto', 'TV', '04301580264', 'Black_Castelfranco', 'e5f47b82b1b8adc07850ea7c8310b235', 2, '', '', '', 'SMNGRT91D5C743E', 'SMNGRT91D5C743E', '', '', '', '0', ''),
(27, 'SIMIONI GRETA', 'VIA E. DE AMICIS, 14', 'CASTELFRANCO VENETO', 'TV', '04301580264', 'BLACK FASHION MONTEBELLUNA', 'Viale XXX aprile, 13', 'MONTEBELLUNA', 'TV', '04301580264', 'Black_Montebelluna', 'dc3f3613f8e104c4a9ec4271a7da8597', 2, '', '', '', 'SMNGRT91D5C743E', 'SMNGRT91D5C743E', '', '', '', '0', ''),
(28, 'ALLBEST DI ALBERTI STEFANIA', 'VIA PALOMBARA SNC', 'COTTANELLO', 'RI', '011211710573', 'BLACK FASHION TERNI', 'CORSO VECCHIO 810', 'TERNI', 'TR', '011211710573', 'Black_Terni', '998e7aa9965f658d92903348bd6a2527', 2, '', '', '', 'LBRSFN86D49H282J', 'LBRSFN86D49H282J', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `voci_confezionamento`
--

CREATE TABLE IF NOT EXISTS `voci_confezionamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` text NOT NULL,
  `costo_unitario` text NOT NULL,
  `quantita_minima` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

--
-- Dump dei dati per la tabella `voci_confezionamento`
--

INSERT INTO `voci_confezionamento` (`id`, `nome`, `costo_unitario`, `quantita_minima`) VALUES
(1, 'SHOPPER LOGO 20X25', '0.10', '200'),
(2, 'SHOPPER LOGO 25X30', '0.12', '200'),
(3, 'SHOPPER LOGO 30X45', '0.13', '200'),
(4, 'SHOPPE LOGO 50X60', '0.20', '100'),
(5, 'SHOPPER LOGO CARTA 50X60', '0.34', '200'),
(6, 'ETICHETTE CHIUDIPACCO', '0.031', '1000'),
(7, 'SACCHETTI TRASPARENTI 10X5 ', '0.01', '100'),
(8, 'SACCHETTI TRASPARENTI 25X22', '0.01', '100'),
(9, 'SACCHETTI TRASPARENTI 15X5', '0.01', '100'),
(10, 'SACCHETTI TRASPARENTI 16X13', '0.01', '100'),
(11, 'SACCHETTI TRASPARENTI 18X18', '0.01', '100'),
(12, 'SACCHETTI TRASPARENTI 10X14', '0.01', '100'),
(13, 'SACCHETTI REGALO 19X12', '0.05', '100'),
(14, 'SACCHETTI REGALO 27X17', '0.12', '50'),
(15, 'SACCHETTI REGALO 34X21', '0.14', '50'),
(16, 'SACCHETTI REGALO 10X5', '0.05', '100'),
(17, 'SACCHETTI REGALO 7X16', '0.05', '100');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
