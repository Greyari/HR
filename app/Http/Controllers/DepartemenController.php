<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DepartemenController
{
    public function show ()
    {
        return view('pages.admin.departemen', [
            'title'=> 'departemen'
        ]);
    }
}
