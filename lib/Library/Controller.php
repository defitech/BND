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

        // Si la personne est déconnectée, on relance le processus de login en
        // envoyant depuis ici l'information "Unauthorized"
        if ($controller->getParam('cmd') != 'login' && !Library_Config::getInstance()->issetAUser()) {
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




}