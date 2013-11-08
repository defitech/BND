-- phpMyAdmin SQL Dump
-- version 3.3.2deb1ubuntu1
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Ven 08 Novembre 2013 à 09:02
-- Version du serveur: 5.1.70
-- Version de PHP: 5.3.2-1ubuntu4.21

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: 'defitechbnd'
--

-- --------------------------------------------------------

--
-- Structure de la table 'library_book'
--

DROP TABLE IF EXISTS library_book;
CREATE TABLE IF NOT EXISTS library_book (
  id int(11) NOT NULL AUTO_INCREMENT,
  type_id int(11) DEFAULT NULL,
  editor_id int(11) DEFAULT NULL,
  title varchar(200) DEFAULT NULL,
  thumb varchar(200) DEFAULT NULL,
  tags text,
  isbn varchar(150) DEFAULT NULL,
  filename varchar(100) DEFAULT NULL,
  `right` varchar(255) DEFAULT NULL,
  notes text,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'library_book_editor'
--

DROP TABLE IF EXISTS library_book_editor;
CREATE TABLE IF NOT EXISTS library_book_editor (
  id int(11) NOT NULL AUTO_INCREMENT,
  editor varchar(200) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'library_book_niveau'
--

DROP TABLE IF EXISTS library_book_niveau;
CREATE TABLE IF NOT EXISTS library_book_niveau (
  book_id int(11) NOT NULL,
  niveau_id int(11) NOT NULL,
  PRIMARY KEY (book_id,niveau_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'library_book_type'
--

DROP TABLE IF EXISTS library_book_type;
CREATE TABLE IF NOT EXISTS library_book_type (
  id int(11) NOT NULL AUTO_INCREMENT,
  label varchar(200) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'library_niveau'
--

DROP TABLE IF EXISTS library_niveau;
CREATE TABLE IF NOT EXISTS library_niveau (
  id int(11) NOT NULL AUTO_INCREMENT,
  label varchar(50) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'library_user'
--

DROP TABLE IF EXISTS library_user;
CREATE TABLE IF NOT EXISTS library_user (
  id int(11) NOT NULL AUTO_INCREMENT,
  login varchar(50) NOT NULL,
  pass varchar(255) NOT NULL,
  `right` int(3) DEFAULT NULL,
  email varchar(100) DEFAULT NULL,
  type_id int(11) DEFAULT NULL,
  last_connected datetime DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'library_user_download'
--

DROP TABLE IF EXISTS library_user_download;
CREATE TABLE IF NOT EXISTS library_user_download (
  id int(11) NOT NULL AUTO_INCREMENT,
  book_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  downloaded_at datetime DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'library_user_passhash'
--

DROP TABLE IF EXISTS library_user_passhash;
CREATE TABLE IF NOT EXISTS library_user_passhash (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  hashcode varchar(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY user_id (user_id),
  UNIQUE KEY hashcode (hashcode)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'library_user_type'
--

DROP TABLE IF EXISTS library_user_type;
CREATE TABLE IF NOT EXISTS library_user_type (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_type varchar(200) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
