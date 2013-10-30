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

// test récupération de mot de passe. Si on demande un mot de passe, on va
// afficher la popup de récup
$ctrl = Library_Controller::output(array_merge($_REQUEST, array(
    'cmd' => 'checkNewPasswordAsk'
)));

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
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/plupload/js/plupload.full.min.js"></script>
        <script type="text/javascript" src="src/admin/FlashPdfButton.js"></script>
        
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/menu/ListMenu.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/menu/RangeMenu.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/GridFilters.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/filter/Filter.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/filter/StringFilter.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/filter/NumericFilter.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>../extjsux/examples/ux/gridfilters/filter/ListFilter.js"></script>
        <!-- Base JS App -->
        <script type="text/javascript" src="lang/fr.js"></script>
        <script type="text/javascript" src="src/Main.js"></script>
        <script type="text/javascript" src="src/login/Login.js"></script>
        <?php /* si un utilisateur est connecté, on affiche l'appli */ if ($user) : ?>
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
        <?php /* on demande un nouveau password */ elseif ($ctrl['success']) : ?>
        <script type="text/javascript" src="src/login/AskPass.js"></script>
        <script type="text/javascript">
        Ext.onReady(function(){
            var win = new Library.login.AskPass({
                data: <?= Zend_Json::encode($ctrl['data']); ?>
            });
            win.show();
        });
        </script>
        <?php /* si aucun utilisateur n'est connecté, on affiche le login */  else : ?>
        <script type="text/javascript">
        Ext.onReady(function(){
            var win = new Library.login.Form();
            win.show();
        });
        </script>
        <?php endif; ?>
        <!-- Envionment data -->
        <script type="text/javascript">
            Ext.BLANK_IMAGE_URL = '<?php echo $path; ?>resources/images/default/s.gif';
            Library.Main.addConfig(<?php echo Zend_Json::encode($config); ?>);
        </script>
    </head>
    <body class="book-background book-background-<?php echo $config['background']; ?>"></body>
</html>