<?php

namespace App\Http\Controllers\Admin;

class ProductController extends MasterDataController
{
    protected $table = 'product_group';
    protected $primaryKey = 'product_id';
    protected $nameField = 'product';
    protected $viewPath = 'admin.master.generic';
    protected $routeName = 'admin.products';
    protected $title = 'กลุ่มสินค้า';
}
