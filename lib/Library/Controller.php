<?php

class Library_Controller {
    
    /**
     * --------------------------------------------------------------
     *              Binz de gestion de contrôleur
     * --------------------------------------------------------------
     */

    /**
     * Les paramètres reçus par le contrôleur
     * @var array
     */
    private $params;

    private $subControllers = array();

    public static function output($params) {
        $controller = new self($params);

        $logout_actions = array('login', 'remindPassword', 'checkNewPasswordAsk', 'changePassword');
        // Si la personne est déconnectée, on relance le processus de login en
        // envoyant depuis ici l'information "Unauthorized"
        if (!in_array($controller->getParam('cmd'), $logout_actions) && !Library_Config::getInstance()->issetAUser()) {
            header('Content-type: application/json');
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }
        
        try {
            $data = $controller->action();
            // on encode/stripslashes ou autre toutes les string
            function recurse(&$tab) {
                foreach ($tab as $key => $val) {
                    if (is_array($val)) $tab[$key] = recurse($val);
                    if (is_string($val)) {
                        $tab[$key] = stripslashes($val);
                    }
                }
                return $tab;
            }
            return $data ? recurse($data) : array('success' => false, 'error' => $data);
        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            );
        }
    }

    protected function  __construct($params) {
        $this->params = $params;
        $this->subControllers = array(
            'Library_Book_Controller',
            'Library_Book_EditorController',
            'Library_Book_TypeController',
            'Library_Book_NiveauController',
            'Library_User_Controller',
            'Library_User_DownloadController',
            'Library_Book_ImportController',
        );
    }

    public function hasParam($param) {
        return isset($this->params[$param]);
    }

    public function getParam($param, $default = 'FjhOh83hoo3') {
        if ($this->hasParam($param)) {
            return $this->params[$param];
        } elseif ($default !== 'FjhOh83hoo3') {
            return $default;
        }
        throw new Exception(sprintf(Library_Wording::get('param_doesnt_exists'), $param));
    }

    public function getParams() {
        return $this->params;
    }
    
    public function setParam($param, $value) {
        $this->params[$param] = $value;
        return $this;
    }

    public function action() {
        $cmd = $this->getParam('cmd');
        // on parcourt les sous-contrôleurs pour voir dans lequel se trouve
        // la méthode appropriée
        foreach ($this->subControllers as $controller) {
            $c = new $controller($this->getParams());
            if (method_exists($c, $cmd)) {
                return $c->$cmd();
            }
        }
        throw new Exception(sprintf(Library_Wording::get('param_cmd_unknow'), $cmd));
    }

    public function getGroupParam($paramPrefix, $separator = '-') {
        $params = array();
        foreach ($this->params as $key => $val) {
            if (strpos($key, $paramPrefix) !== false) {
                $b = explode($separator, $key);
                $k = array_pop($b);
                $params[$k] = $val;
            }
        }
        return $params;
    }




    /**
     * --------------------------------------------------------------
     *              Méthodes partagées entre les contrôleurs
     * --------------------------------------------------------------
     */
    
    protected function getRightConfig() {
        Library_Config::getInstance()->testIssetAuser(2);
                
        $config = array();
        $config['userTypes'] = Library_User_Type::getComboList();
        
        return array(
            'success' => true,
            'config' => $config
        );
    }

    protected function generatePdfFirstPageThumb($pdf, $img) {
        $path_convert = Library_Config::getInstance()->getData()->path->convert;

        $argpdf = $pdf . '[0]';
        $cmd = $path_convert . "convert " . escapeshellarg($argpdf) . " " . escapeshellarg($img) . " 2>&1";
        $output = array($cmd);
        exec($cmd, $output);

        // log de l'output
        ob_start();
        echo "<pre>";
        print_r($output);
        echo "</pre>";
        $content = ob_get_contents();
        ob_end_clean();
        Library_Config::log()->debug(Library_Wording::get('thumb_generation') . $content);

        return $output;
    }





    /**
     * --------------------------------------------------------------
     *              Méthodes relatives à l'application
     * --------------------------------------------------------------
     */

    protected function getBackgrounds() {
        Library_Config::getInstance()->testIssetAuser(2);
        $config = Library_Config::getInstance();
        $path = $config->getRoot() . 'resources/background/';
        $path_thumbs = $path . 'thumbs/';
        $dir = dir($path);
        $extensions = array('jpg', 'jpeg');
        
        $items = array();
        while (false !== ($entry = $dir->read())) {
            if ($entry == '.' || $entry == '..' || is_dir($path . $entry))
                continue;
            
            $ext = Library_Util::getExtension($entry);
            if (!in_array($ext, $extensions))
                continue;
            
            if (!file_exists($path_thumbs . $entry)) {
                $im = @imagecreatefromjpeg($path . $entry);
                list($width, $height) = getimagesize($path . $entry);
                $mini_width = 210;
                $mini_height = 118;
                $tn = imagecreatetruecolor($mini_width, $mini_height);
                imagecopyresampled($tn, $im, 0, 0, 0, 0, $mini_width, $mini_height, $width, $height);
                if (!is_dir($path_thumbs)) {
                    mkdir($path_thumbs, 0777);
                }
                imagejpeg($tn, $path_thumbs . $entry, 70);
                imageDestroy($tn);
            }
            $items[] = array(
                'bg' => $entry,
                'thumb' =>  'resources/background/thumbs/'. $entry
            );
        }
        $dir->close();
        
        return array(
            'success' => true,
            'totalCount' => count($items),
            'items' => $items
        );
    }
    
    protected function changeBackground() {
        Library_Config::getInstance()->testIssetAuser(2);
        $bg = $this->getParam('bg');
        $config = Library_Config::getInstance();
        if (!trim($bg) || !file_exists($config->getRoot() . 'resources/background/' . $bg))
            return array(
                'success' => false,
                'error' => sprintf(Library_Wording::get('bg_error_notexist'), $bg)
            );
        
        if (!@file_put_contents($config->getRoot() . 'config/background.txt', $bg)) {
            $e = error_get_last();
            return array(
                'success' => false,
                'error' => $e['message']
            );
        }
        return array(
            'success' => true
        );
    }
    
    public function addBackground() {
        Library_Config::getInstance()->testIssetAuser(2);
        $file = $_FILES['bgfile'];
        if ($file['error'] != UPLOAD_ERR_OK)
            return array(
                'success' => false,
                'error' => Library_Wording::get('bg_error_upload')
            );
        
        $config = Library_Config::getInstance();
        $path = $config->getRoot() . 'resources/background/';
        $name = Library_Util::getSlug(substr($file['name'], 0, strrpos($file['name'], '.')));
        $extensions = array('jpeg', 'jpg');
        $ext = Library_Util::getExtension($file['name']);
        $name .= '.' . $ext;
        if (!in_array($ext, $extensions))
            return array(
                'success' => false,
                'error' => sprintf(Library_Wording::get('bg_error_extension'), $ext)
            );
        
        if (!move_uploaded_file($file['tmp_name'], $path . $name)) {
            return array(
                'success' => false,
                'error' => Library_Wording::get('bg_error_move')
            );
        }
        
        return array(
            'success' => true,
            'name' => $name
        );
    }


}