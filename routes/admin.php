<?php

function checkAuth($req, $res)
{
    if (!isset($req->session['user'])) {
        return $res->redirect('/admin');
    }
}

Router::get('/admin', function ($req, $res) {
    return $res->render('admin/auth.pbs');
});

Router::post('/admin/login', function ($req, $res) {
    $login = $req->data['login'];
    $pass = $req->data['password'];

    $user = Database::getItemsWhere('user_admin', ['login' => $login, 'password' => $pass]);

    if (!count($user)) {
        return $res->redirect('/admin');
    }

    $_SESSION['user'] = $login;

    return $res->redirect('/admin/products');
});

Router::get('/admin/logout', function ($req, $res) {
    checkAuth($req, $res);
    session_unset();
    return $res->redirect('/admin');
});


/* PRODUCTS EDIT/ADD/DELETE */
Router::get('/admin/products', function ($req, $res) {
    checkAuth($req, $res);

    return $res->render('admin/products.pbs', ['films' => Database::getItems('film')]);
});

Router::post('/admin/products/edit', function ($req, $res) {
    checkAuth($req, $res);

    $fields = Database::getItemsWhere('information_schema.columns', ['table_name' => 'film'], 'column_name');
    unset($fields[0]);
    $values = Database::getItem('film', $req->query['id']);

    return $res->render('admin/edit.pbs', ['fields' => $fields, 'values' => $values]);
});

Router::post('/admin/products/edit/submit', function ($req, $res) {
    checkAuth($req, $res);
    Database::updateItem('film', $req->data['id'], $req->data);

    return $res->redirect('/admin/products');
});

Router::get('/admin/products/add', function ($req, $res) {
    checkAuth($req, $res);
    $fields = Database::getItemsWhere('information_schema.columns', ['table_name' => 'film'], 'column_name');
    unset($fields[0]);

    return $res->render('admin/add.pbs', ['fields' => $fields]);
});

Router::post('/admin/products/add/submit', function ($req, $res) {
    checkAuth($req, $res);
    [
        'title' => $title,
        'description' => $description,
        'price' => $price,
        'img' => $img,
        'category' => $category
    ] = $req->data;
    $film = new Film($title, $description, $price, $img, $category);
    Database::saveItem('film', $film);

    return $res->redirect('/admin/products');
});

Router::post('/admin/products/delete', function ($req, $res) {
    checkAuth($req, $res);
    Database::deleteItem('film', $req->query['id']);
    return $res->redirect('/admin/products');
});

/* PAGES EDIT */
Router::get('/admin/pages', function ($req, $res) {
    checkAuth($req, $res);
    function assetsMap($source_dir, $directory_depth = 0, $hidden = FALSE)
    {
        if ($fp = @opendir($source_dir)) {
            $filedata   = array();
            $new_depth  = $directory_depth - 1;
            $source_dir = rtrim($source_dir, '/') . '/';

            while (FALSE !== ($file = readdir($fp))) {
                // Remove '.', '..', and hidden files [optional]
                if (!trim($file, '.') or ($hidden == FALSE && $file[0] == '.')) {
                    continue;
                }

                if (($directory_depth < 1 or $new_depth > 0) && @is_dir($source_dir . $file)) {
                    $filedata[$file] = assetsMap($source_dir . $file . '/', $new_depth, $hidden);
                } else {
                    $filedata[] = str_replace('./templates/', '', $source_dir . $file);
                }
            }

            closedir($fp);
            return $filedata;
        }
        echo 'can not open dir';
        return FALSE;
    }

    function flatten(array $array)
    {
        $return = array();
        array_walk_recursive($array, function ($a) use (&$return) {
            $return[] = $a;
        });
        return $return;
    }

    $tamplates = flatten(assetsMap('./templates', $directory_depth = 5));

    return $res->render('admin/pages.pbs', ['tamplates' => $tamplates]);
});

Router::post('/admin/pages/edit', function ($req, $res) {
    checkAuth($req, $res);
    $templateName = $req->query['name'];
    $contents = file_get_contents('./templates/' . $templateName);

    return $res->render('admin/edit-page.pbs', ['contents' => $contents, 'template' => $templateName]);
});

Router::post('/admin/pages/edit/submit', function ($req, $res) {
    checkAuth($req, $res);
    $filename = $req->data['template'];
    $contents = $req->data['file-edit'];

    file_put_contents('./templates/' . $filename, $contents);

    return $res->redirect('/admin/pages');
});
