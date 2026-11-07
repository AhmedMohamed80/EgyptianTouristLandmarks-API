<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\story;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;



class StoryController extends Controller
{
    public function add_story(Request $request)
    {
        try {
            $request->validate([
                'description' => 'required|string',
                'date' => 'required|date',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
                'user_id' => 'required|exists:users,id'
            ]);
    
            // Store the image
            $file_extension = $request->image->getClientOriginalExtension();
            $file_name = time() . '.' . $file_extension;
            $path = 'image/stories';
            $request->image->move($path, $file_name);
    
            // Save the story
            $story = Story::create([
                'user_id' => $request->user_id,
                'image_path' => $file_name,
                'description' => $request->description,
                'date' => $request->date,
            ]);
    
            $imageUrl = url($path . '/' . $file_name);
    
            return response()->json([
                'message' => 'successful',
                'story' => [
                    'id' => $story->id,
                    'user_id' => $story->user_id,
                    'description' => $story->description,
                    'date' => $story->date,
                    'image_url' => $imageUrl, 
                ]
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'فشل: ' . $e->getMessage()], 500);
        }
    }
    
    public function show($id)
    {
        $story = Story::with('user')->find($id);
        if (!$story) {
            return response()->json(['message' => 'faild'], 200);
        }
    
        // Generate full URLs for images
        $story->image_url = url('image/stories/' . $story->image_path);
        $story->user->profile_image_url = url($story->user->profile_image);
    
        return response()->json([
            'id' => $story->id,
            'user' => [
                'id' => $story->user->id,
                'name' => $story->user->name,
                'email' => $story->user->email,
                'phone' => $story->user->phone,
                'profile_image' => $story->user->profile_image_url, // Return full URL
                'created_at' => $story->user->created_at,
                'updated_at' => $story->user->updated_at
            ],
            'description' => $story->description,
            'date' => $story->date,
            'image_url' => $story->image_url, // Return full URL
        ]);
    }
    
    public function get_All_Stories()
    {
        $stories = Story::with('user')->get();
    
        $stories->transform(function ($story) {
            $story->image_url = url('image/stories/' . $story->image_path);
            $story->user->profile_image_url = url($story->user->profile_image);
            return [
                'id' => $story->id,
                'user' => [
                    'id' => $story->user->id,
                    'name' => $story->user->name,
                    'email' => $story->user->email,
                    'phone' => $story->user->phone,
                    'profile_image' => $story->user->profile_image_url, // Return full URL
                    'created_at' => $story->user->created_at,
                    'updated_at' => $story->user->updated_at
                ],
                'description' => $story->description,
                'date' => $story->date,
                'image_url' => $story->image_url, // Return full URL
            ];
        });
    
        return response()->json($stories);
    }
    
    

    // public function update_story(Request $request, $id)
    // {
    //     $story = Story::find($id);
    //     if (!$story) {
    //         return response()->json(['message' => 'not found'], 404);
    //     }

    //     $story->update($request->all());

    //     return response()->json(['message' => 'successful', 'story' => $story]);
    // }

    public function delete_story($id)
    {
        $story = Story::find($id);
        if (!$story) {
            return response()->json(['message' => 'not found'], status: 404);
        }

        $story->delete();

        return response()->json(['message' => 'successful']);
    }
}
