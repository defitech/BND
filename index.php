<?php

include_once 'config.php';

$path = Library_Config::getInstance()->getData()->path->extjs;
$user = Library_Config::getInstance()->getUser();

$config = array(
    'sid' => session_id(),
    'cid' => $user ? $user->id : 0,
    'rid' => $user ? $user->right : 0,
    'libspath' => $path . '../',
    'background' => 'foretcanada'
);

function renameThumbs() {
    // migration des images du svn vers le dossier des livres
    $table = new Library_Book();
    $rowset = $table->fetchAll();
    foreach ($rowset as $row) {
        $row->thumb = str_replace('resources/books/', '', $row->thumb);
        $row->save();
    }
}

function regenerateTags() {
    // mise en place des tags du titre en slug
    $table = new Library_Book();
    $rowset = $table->fetchAll();
    foreach ($rowset as $row) {
        $old_tags = array_map('trim', explode(',', $row->tags));
        $new_tags = explode('-', Library_Util::getSlug($row->title));
        $ok_tags = array_unique(array_merge($new_tags, $old_tags));
        $row->tags = implode(',', $ok_tags);
        $row->save();
    }
}
//renameThumbs();
//regenerateTags();

?>
<html>
    <head>
        <title>Library BND</title>
        <meta http-equiv="content-language" content="fr" />
        <meta name="description" content="Bilbiothèque numérique Download (BND)" />
        <meta name="Author" content="Allez-savoir" />
        <meta name="Copyright" content="Allez-savoir" />
        <meta name="Publisher" content="Allez-savoir" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="shortcut icon" type="image/png" href="resources/images/favicon.png" />
        <!-- CSS Ext -->
        <link rel="stylesheet" type="text/css" href="<?php echo $path; ?>resources/css/ext-all.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $path; ?>../extjsux/extjsplupload/ext.ux.plupload.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/css/GridFilters.css" />
        <!-- CSS app -->
        <link rel="stylesheet" type="text/css" href="resources/css/app.css" />
        <!-- Base JS Ext -->
        <script type="text/javascript" src="<?php echo $path; ?>adapter/ext/ext-base.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>ext-all.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/src/locale/ext-lang-fr.js"></script>
        <!-- Plugin JS Ext -->
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/plupload/js/plupload.min.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/extjsplupload/ext.ux.plupload.js"></script>

        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/menu/ListMenu.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/GridFilters.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/filter/Filter.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/filter/StringFilter.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/filter/ListFilter.js"></script>
        <!-- Base JS App -->
        <script type="text/javascript" src="lang/fr.js"></script>
        <script type="text/javascript" src="src/Main.js"></script>
        <?php /* si un utilisateur est connecté, on affiche l'appli */ if ($user) { ?>
        <script type="text/javascript" src="index.js"></script>
        <script type="text/javascript" src="src/App.js"></script>
        <script type="text/javascript" src="src/Book.js"></script>
        <script type="text/javascript" src="src/BookGrid.js"></script>
        <script type="text/javascript" src="src/ContextMenu.js"></script>
        <script type="text/javascript" src="src/Keys.js"></script>
        <script type="text/javascript" src="src/admin/App.js"></script>
        <script type="text/javascript" src="src/admin/BookGrid.js"></script>
        <script type="text/javascript" src="src/admin/Book.js"></script>
        <script type="text/javascript" src="src/admin/ContextMenu.js"></script>
        <script type="text/javascript" src="src/admin/Keys.js"></script>
        <script type="text/javascript" src="src/admin/UserPanel.js"></script>
        <script type="text/javascript" src="src/admin/UserGrid.js"></script>
        <script type="text/javascript" src="src/admin/UserDownload.js"></script>
        <?php /* si aucun utilisateur n'est connecté, on affiche le login */ } else { ?>
        <script type="text/javascript" src="src/login/Login.js"></script>
        <?php } ?>
        <!-- Envionment data -->
        <script type="text/javascript">
            Ext.BLANK_IMAGE_URL = '<?php echo $path; ?>resources/images/default/s.gif';
            Library.Main.addConfig(<?php echo Zend_Json::encode($config); ?>);
        </script>
    </head>
    <body class="book-background book-background-<?php echo $config['background']; ?>"></body>
</html>