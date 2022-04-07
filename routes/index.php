<?php

Router::get('/', function ($req, $res) {
    return $res->render('index.pbs', ['films' => Database::getItems('film')]);
});

Router::get('/films', function ($req, $res) {
    $films = Database::getItems('film');
    shuffle($films);
    return $res->render('films.pbs', ['films' => $films]);
});

Router::get('/about', function ($req, $res) {
    return $res->render('about.pbs');
});

Router::get('/company', function ($req, $res) {
    return $res->render('info.pbs');
});

Router::get('/categories', function ($req, $res) {
    return $res->redirect('/category?cat=horror');
});

Router::get('/category', function ($req, $res) {
    $data = ['films' => Database::getItemsWhere('film', ['category' => $req->query['cat']]), 'category' => ucfirst($req->query['cat'])];
    return $res->render("category.pbs", $data);
});
