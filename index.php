<?php

include_once 'config.php';

$path = Library_Config::getInstance()->getData()->path->extjs;
$user = Library_Config::getInstance()->getUser();

?>
<html>
    <head>
        <title>Library BDN</title>
        <meta http-equiv="content-language" content="fr" />
        <meta name="description" content="Bilbiothèque numérique Download (BND)" />
        <meta name="Author" content="Allez-savoir" />
        <meta name="Copyright" content="Allez-savoir" />
        <meta name="Publisher" content="Allez-savoir" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="shortcut icon" type="image/png" href="resources/images/favicon.png" />
        <!-- CSS Ext -->
        <link rel="stylesheet" type="text/css" href="<?php echo $path; ?>resources/css/ext-all.css" />
        <!-- CSS app -->
        <link rel="stylesheet" type="text/css" href="resources/css/app.css" />
        <!-- Base JS Ext -->
        <script type="text/javascript" src="<?php echo $path; ?>adapter/ext/ext-base-debug.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>ext-all-debug.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>src/locale/ext-lang-fr.js"></script>
        <!-- Plugin JS Ext -->
        <script type="text/javascript" src="<?php echo $path; ?>examples/ux/gridfilters/menu/RangeMenu.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>examples/ux/gridfilters/menu/ListMenu.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>examples/ux/gridfilters/GridFilters.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>examples/ux/gridfilters/filter/Filter.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>examples/ux/gridfilters/filter/StringFilter.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>examples/ux/gridfilters/filter/DateFilter.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>examples/ux/gridfilters/filter/ListFilter.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>examples/ux/gridfilters/filter/NumericFilter.js"></script>
        <script type="text/javascript" src="<?php echo $path; ?>examples/ux/gridfilters/filter/BooleanFilter.js"></script>
        <!-- Base JS App -->
        <script type="text/javascript" src="lang/fr.js"></script>
        <script type="text/javascript" src="index.js"></script>
        <script type="text/javascript" src="src/Main.js"></script>
        <script type="text/javascript" src="src/App.js"></script>
        <script type="text/javascript" src="src/Book.js"></script>
        <script type="text/javascript" src="src/BookGrid.js"></script>
        <script type="text/javascript" src="src/admin/App.js"></script>
        <script type="text/javascript" src="src/admin/BookGrid.js"></script>
        <script type="text/javascript" src="src/admin/Book.js"></script>
        <!-- Envionment data -->
        <script type="text/javascript">var stacknmblue = <?php echo ($user ? $user->id : 0); ?>;</script>
    </head>
    <body></body>
</html>