<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\Splade\SpladeTable;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all post data
        $posts = Post::latest()->paginate(7);

         // render view
        return view('posts.index', [
            'posts' => SpladeTable::for($posts)
            ->column('image')
            ->column('title')
            ->column('content')
            ->column('action')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // render view
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // validate request
        $this->validate($request, [
            'image' => 'required|image|mimes:jpeg,jpg,png',
            'title' => 'required|min:5',
            'content' => 'required|min:10'
        ]);

        // upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        // insert new post to db
        Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $image->hashName(),
        ]);

        // render view
        return redirect(route('posts.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        // render view
        return view('posts.edit', [
            'post' => $post
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        // validate request
        $this->validate($request, [
            'image' => 'nullable|image|mimes:jpeg,jpg,png',
            'title' => 'required|min:5',
            'content' => 'required|min:10'
        ]);

        // update post data by id
        $post->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        // check if user upload new image
        if($request->file('image')){
            // upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            // delete old image
            Storage::delete('public/posts/'. $post->image);

            // update post data image
            $post->update([
                'image' => $image->hashName(),
            ]);
        }

        // render view
        return redirect(route('posts.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // delete post image
        Storage::delete('public/posts/'. $post->image);

        // delete post data by id
        $post->delete();

        // render view
        return back();
    }
}
