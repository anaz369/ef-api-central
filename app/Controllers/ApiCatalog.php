<?php

namespace App\Controllers;

use App\Controllers\Admin\BaseAdminController;

class ApiCatalog extends BaseAdminController
{
    public function index()
    {
        $this->requireLogin();

        $data = [
            'page_title'  => 'API Catalog',
            'active_menu' => 'api_catalog',
            'breadcrumb'  => 'API Catalog',
        ];

        return $this->renderView(view('api_catalog/index', $data), $data);
    }
}
