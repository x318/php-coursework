<?php

Router::get('/film', function ($req, $res) {
    return $res->render('film.pbs', ['film' => Database::getItem('film', $req->query['id'])]);
});
