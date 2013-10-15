<?php
/* @var $this Library_Book_Controller */
/* @var $books array */
?>
<html>
    <head>
        <title>BND</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="shortcut icon" type="image/png" href="../resources/images/favicon.png" />
        <style type="text/css">
            @page {
                margin: 1.5cm 0.8cm;
                margin-bottom: 2cm;
            }
            
            html, body {
                margin: 0;
                padding: 0;
            }
            
            table {
                border-collapse: collapse;
                width: 100%;
                font-family: Arial, sans-serif;
                font-size: 10px;
            }
            
            th {
                font-weight: bold;
                text-align: left;
            }
            
            td {
                border-top: 1px solid #ccc;
                padding: 3px;
            }
        </style>
    </head>
    <body onload="window.print();">
        <table>
            <thead>
                <tr>
                    <th><?= Library_Wording::get('field_id'); ?></th>
                    <th><?= Library_Wording::get('field_type'); ?></th>
                    <th><?= Library_Wording::get('field_editor'); ?></th>
                    <th><?= Library_Wording::get('field_niveau'); ?></th>
                    <th><?= Library_Wording::get('field_title'); ?></th>
                    <th><?= Library_Wording::get('field_isbn'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                <tr>
                    <td><?= $book['id']; ?></td>
                    <td><?= $book['type_id']; ?></td>
                    <td><?= $book['editor_id']; ?></td>
                    <td><?= $book['niveau_id']; ?></td>
                    <td><?= $book['title']; ?></td>
                    <td><?= $book['isbn']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
</html>