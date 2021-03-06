<?php

function menu()
{
    $items = __c("files")->get('menu');
    if ($items == null) {
        $items = generator();
        __c("files")->set('menu', $items, 600);
    }

    if ($_SESSION['loggedin']) {
        $items .= generator(0, 1);
    }
    if (permTo("menu_acp")) {
        $items .= generator(0, 2);
    }

    $menu = show("panels/menu", array('items' => $items,
        'link_index' => '../'
    ));

    return $menu;
}

function generator($subfrom = 0, $part = 0)
{
    $qury = Db::npquery('SELECT * FROM menu WHERE subfrom = ' . $subfrom . ' and part = ' . $part . ' Order by position');
    $menu = "";
    foreach ($qury as $get) {
        if ($get['newtab']) {
            $tab = 'target="_blank"';
        } else {
            $tab = '';
        }
        $issub = Db::npquery('SELECT subfrom FROM menu WHERE subfrom = ' . $get['id']);
        if (sizeof($issub) < 1) {
            $menu .= show("panels/menu_item", array('title' => $get['title'],
                'newtab' => $tab,
                'link' => $get['link']));
        } else {
            $menu .= show("panels/menu_sub", array('title' => $get['title'],
                'link' => $get['link'],
                'newtab' => $tab,
                'items' => generator($get['id'], $part)));
        }
    }
    return $menu;
}