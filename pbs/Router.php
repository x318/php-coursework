<?php

class Router
{
    static function create($dir = './routes')
    {
        $files = glob($dir . '/*.php');

        foreach ($files as $file) {
            require_once($file);
        }
    }

    static function get(string $route,  $callback)
    {
        ob_clean();
        if (!isset($_SESSION)) {
            session_start();
        }


        $req = new Request($_SERVER);
        $res = new Response();

        if ($req->path == $route && $req->method == 'GET') {
            call_user_func($callback, $req, $res);
            die();
        }

        self::render404($res);
    }

    static function post(string $route, $callback)
    {
        ob_clean();
        if (!isset($_SESSION)) {
            session_start();
        }

        $req = new Request($_SERVER);
        $req->data = $_POST;
        $res = new Response();

        if ($req->path == $route && $req->method == 'POST') {
            call_user_func($callback, $req, $res);
            die();
        }

        self::render404($res);
    }

    private static function render404($res)
    {
        try {
            if (!file_exists('./templates/404.pbs'))
                throw new Exception();

            pbs::render('404.pbs');
        } catch (Exception $e) {
            $res->send('404 - Страница не найдена');
            die();
        }
    }
}

class Request
{
    public string $path;
    public array $query;
    public string $method;
    public array $data;
    public array $session;

    public function __construct($reqObject)
    {
        $this->path = explode('?', $reqObject['REQUEST_URI'])[0];
        $this->query = self::constructQuery($reqObject, '&');
        $this->method = $reqObject['REQUEST_METHOD'];
        $this->session = $_SESSION;
    }

    private static function constructQuery($reqObject, $separator)
    {
        if (!isset($reqObject['QUERY_STRING'])) {
            return array();
        }

        $paramsArray = explode($separator, $reqObject['QUERY_STRING']);
        $params = array();
        foreach ($paramsArray as $param) {
            $params[explode('=', $param)[0]] = explode('=', $param)[1];
        }
        return $params;
    }
}

class Response
{
    public function __construct()
    {
    }

    public function render($template, $data = array())
    {
        pbs::render($template, $data);
    }

    public function send($data)
    {
        if (is_string($data)) {
            echo $data;
        }
        if (!is_string($data)) {
            echo json_encode($data);
        }
    }

    public function redirect($url)
    {
        header('Location: ' . $url, true, 303);
        die();
    }
}
