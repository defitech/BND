# Serveur: localhost:8888
# Généré le: Friday 11th 2011f March 2011 03:01 pm
# MySQL
# PHP Version: 5.2.13
# --------------------------------------------------------

#
# Structure de la table: library_book
#

# --------------------------------------------------------
CREATE TABLE `library_book` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NULL,
  `editor_id` int(11) NULL,
  `title` varchar(200) NULL,
  `thumb` varchar(200) NULL,
  `tags` TEXT NULL,
  `isbn` varchar(150) NULL,
  `filename` varchar(100) NULL,
  `right` varchar(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



#
# Structure de la table: library_book_editor
#

# --------------------------------------------------------
CREATE TABLE `library_book_editor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `editor` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

#
# Structure de la table: library_book_niveau
#

# --------------------------------------------------------
CREATE TABLE `library_book_niveau` (
  `book_id` int(11) NOT NULL,
  `niveau_id` int(11) NOT NULL,
  PRIMARY KEY (`book_id`,`niveau_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure de la table: library_book_type
#

# --------------------------------------------------------
CREATE TABLE `library_book_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

#
# Structure de la table: library_niveau
#

# --------------------------------------------------------
CREATE TABLE `library_niveau` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

#
# Structure de la table: library_user
#

# --------------------------------------------------------
CREATE TABLE `library_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `right` int(3),
  `type_id` int(11),
  `deficiency_id` int(11),
  `confirmed` tinyint(1),
  `inactive` tinyint(1),
  `last_connected` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

#
# Structure de la table: library_user_download
#

# --------------------------------------------------------
CREATE TABLE `library_user_download` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `downloaded_at` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

#
# Structure de la table: library_user_type
#

# --------------------------------------------------------
CREATE TABLE `library_user_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

#
# Structure de la table: library_user_deficiency
#

# --------------------------------------------------------
CREATE TABLE `library_user_deficiency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_deficiency` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
