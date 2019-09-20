<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tag;

// 引入表单请求类
use App\Http\Requests\TagCreateRequest;
use App\Http\Requests\TagUpdateRequest;


class TagController extends Controller
{
    protected $fields=[
        'tag' => '',
        'title' => '',
        'subtitle'=>'',
        'meta_description' => '',
        'page_image' => '',
        'layout' => 'blog.layouts.index',
        'reverse_direction' => 0
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tags = Tag::all();
        return view('admin.tag.index')->withTags($tags);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [];
        foreach ($this->fields as $key => $value) {
            $data[$key] = old($key, $value);
        }
        return view('admin.tag.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TagCreateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(TagCreateRequest $request)
    {
        $tag = new Tag();
        foreach (array_keys($this->fields) as $key) {
            $tag->$key = $request->get($key);
        }
        $tag->save();
    
        return redirect('/admin/tag')
                        ->with('success', '标签「' . $tag->tag . '」创建成功.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tag = Tag::findOrFail($id);
        $data = ['id' => $id];
        foreach (array_keys($this->fields) as $key) {
            $data[$key] = old($key, $tag->$key);
        }
        return view('admin.tag.edit',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TagUpdateRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TagUpdateRequest $request, $id)
    {
        $tag = Tag::findOrFail($id);

        foreach(array_keys(array_except($this->fields, ['tag'])) as $key) {
            $tag[$key] = $request->get($key);
        }
        $tag->save();

        return redirect("/admin/tag")
        ->with('success', '修改已保存.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();

        return redirect('/admin/tag')
        ->with('success', '标签「' . $tag->tag . '」已经被删除.');
    }
}
