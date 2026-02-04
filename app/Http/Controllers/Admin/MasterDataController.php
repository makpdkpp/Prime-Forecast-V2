<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Generic Master Data Controller
 * This controller handles CRUD operations for simple master data tables
 */
class MasterDataController extends Controller
{
    protected $table;
    protected $primaryKey;
    protected $nameField;
    protected $viewPath;
    protected $routeName;
    protected $title;

    public function index()
    {
        $data = DB::table($this->table)->orderBy($this->nameField)->get();
        return view("{$this->viewPath}.index", [
            'data' => $data,
            'title' => $this->title,
            'routeName' => $this->routeName,
            'nameField' => $this->nameField,
            'primaryKey' => $this->primaryKey,
        ]);
    }

    public function create()
    {
        return view("{$this->viewPath}.create", [
            'title' => $this->title,
            'routeName' => $this->routeName,
            'nameField' => $this->nameField,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            $this->nameField => 'required|string|max:255',
        ]);

        DB::table($this->table)->insert([
            $this->nameField => $request->input($this->nameField),
        ]);

        return redirect()->route("{$this->routeName}.index")->with('success', "เพิ่ม{$this->title}เรียบร้อยแล้ว");
    }

    public function edit($id)
    {
        $item = DB::table($this->table)->where($this->primaryKey, $id)->first();
        return view("{$this->viewPath}.edit", [
            'item' => $item,
            'title' => $this->title,
            'routeName' => $this->routeName,
            'nameField' => $this->nameField,
            'primaryKey' => $this->primaryKey,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            $this->nameField => 'required|string|max:255',
        ]);

        DB::table($this->table)
            ->where($this->primaryKey, $id)
            ->update([
                $this->nameField => $request->input($this->nameField),
            ]);

        return redirect()->route("{$this->routeName}.index")->with('success', "อัพเดท{$this->title}เรียบร้อยแล้ว");
    }

    public function destroy($id)
    {
        DB::table($this->table)->where($this->primaryKey, $id)->delete();
        return redirect()->route("{$this->routeName}.index")->with('success', "ลบ{$this->title}เรียบร้อยแล้ว");
    }
}
