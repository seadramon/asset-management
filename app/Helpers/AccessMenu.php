<?php

function editMenu($url, $class = 'btn alpha-blue text-blue-800 border-blue-600 legitRipple btn-sm')
{
    $role_editable = \Auth::user()->rolebaru->filter(function($item) { return $item->can_edit == 'Y'; })->count();
    if($role_editable > 0){
        return '<a href="' . $url . '" class="' . $class . '"><i class="fa fa-edit"></i> Edit </a>';
    }
    return '';
}

function addMenu($text = '', $id = '', $url = '', $class = 'btn btn-success btn-sm', $icon = 'fa fa-plus')
{
    $role_addable = \Auth::user()->rolebaru->filter(function($item) { return $item->can_add == 'Y'; })->count();
    if($role_addable > 0){
        return '<button id="' . $id . '" class="' . $class . '"><i class="' . $icon . '"></i> ' . $text . '</button>';
    }
    return '';
}