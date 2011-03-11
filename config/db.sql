# SQLiteManager Dump
# Version: 1.2.4
# http://www.sqlitemanager.org/
#
# Serveur: localhost:8888
# Généré le: Friday 11th 2011f March 2011 03:01 pm
# SQLite Version: 3.3.7
# PHP Version: 5.2.13
# Base de données: db.txt
# --------------------------------------------------------

#
# Structure de la table: defitech_book
#
CREATE TABLE 'defitech_book' ( 'id' INTEGER NOT NULL PRIMARY KEY, 'type_id' INT, 'editor_id' INT, 'title' VARCHAR(200), 'thumb' VARCHAR(200), 'tags' TEXT, 'isbn' VARCHAR(150), 'filename' VARCHAR(100) );
# --------------------------------------------------------


#
# Structure de la table: defitech_book_editor
#
CREATE TABLE 'defitech_book_editor' ( 'id' INTEGER NOT NULL PRIMARY KEY, 'editor' VARCHAR(200) );
# --------------------------------------------------------


#
# Structure de la table: defitech_book_niveau
#
CREATE TABLE 'defitech_book_niveau' ( 'book_id' INTEGER NOT NULL, 'niveau_id' INTEGER NOT NULL );
# --------------------------------------------------------


#
# Structure de la table: defitech_book_type
#
CREATE TABLE 'defitech_book_type' ( 'id' INTEGER NOT NULL PRIMARY KEY, 'label' VARCHAR(200) );
# --------------------------------------------------------


#
# Structure de la table: defitech_niveau
#
CREATE TABLE 'defitech_niveau' ( 'id' INTEGER NOT NULL PRIMARY KEY DEFAULT "0", 'label' VARCHAR(50) );
# --------------------------------------------------------


#
# Structure de la table: defitech_user
#
CREATE TABLE 'defitech_user' ( 'id' INTEGER NOT NULL PRIMARY KEY, 'login' VARCHAR(50) , 'pass' VARCHAR(255) , 'right' SMALLINT , 'last_connected' TIMESTAMP );
# --------------------------------------------------------

#
# Contenu de la table: defitech_user
#
INSERT INTO 'defitech_user' VALUES ('1', 'admin', 'c462ea29c64efa0f679b05d046205d41', '1', '0');
# --------------------------------------------------------


#
# Propriété de la fonction: md5rev
#
/*
function md5_and_reverse($string) { return strrev(md5($string)); }
*/

#
# Propriété de la fonction: IF
#
/*
function sqliteIf($compare, $good, $bad){ if ($compare) { return $good; } else { return $bad; } }
*/