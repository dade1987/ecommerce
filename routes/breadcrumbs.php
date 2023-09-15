<?php // routes/breadcrumbs.php

// Note: Laravel will automatically resolve `Breadcrumbs::` without
// this import. This is nice for IDE syntax and refactoring.
use Illuminate\Support\Str;

// This import is also not required, and you could replace `BreadcrumbTrail $trail`
//  with `$trail`. This is nice for IDE type checking and completion.
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('home'));
});

Breadcrumbs::for('{item2?}.index', function (BreadcrumbTrail $trail, array $params) {
    $trail->parent('home');

    $index = 0;
    $before_key = '';
    foreach ($params as $key => $param) {
        $parameters = array_slice($params, 0, $index + 1);

        if (Str::startsWith($key, 'item')) {
            $row_class = 'App\\Models\\' . Str::singular(Str::title($params[$before_key]));
            $item = $row_class::findOrFail($param);
            $trail->push($item->name, route('{item2?}.index', $parameters));
        } else {
            $trail->push(Str::title($param), route('{item2?}.index', $parameters));
        }

        $before_key = $key;
        $index++;
    }
});
