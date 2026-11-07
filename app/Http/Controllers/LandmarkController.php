<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Landmark;
use App\Models\Photo;
use App\Models\Review;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class LandmarkController extends Controller
{
public function Display_all_item()
{   
    try{
        $landmarks = Landmark::with(['item.photos'])
        ->withAvg('reviews', 'rating_value') 
        ->withCount('reviews')  
        ->paginate(10);

    return response()->json($landmarks);
    } catch (\Exception $e) {
        return response()->json(['error' => 'فشل الطلب: ' . $e->getMessage()], 500);
    }
}

    public function store_new_item(Request $request)
    {   
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'city' => 'required|string',
                'location' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'type' => 'required|string',
                'open_time' => 'required|date_format:H:i',
                'close_time' => 'required|date_format:H:i',
                'egyptian_adult_price' => 'required|numeric',
                'egyptian_kid_price' => 'required|numeric',
                'egyptian_student_price' => 'required|numeric',
                'foreigner_adult_price' => 'required|numeric',
                'foreigner_kid_price' => 'required|numeric',
                'foreigner_student_price' => 'required|numeric',
                'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            ]);
    
            $item = new Item();
            $item->name = $request->name;
            $item->description = $request->description;
            $item->city = $request->city;
            $item->location = $request->location;

            if ($request->hasFile('image')) {
                $main = $request->file('image');
                $file_extension = $main->getClientOriginalExtension();
                $file_name = time() . rand(1000, 9999) . '.' . $file_extension;
                $path = public_path('image/landmark');                
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $main->move($path, $file_name);
                $item->image_path = asset('image/landmark/' . $file_name);
            }
            
    
            $item->save();

            $landmark = new Landmark();
            $landmark->Item_ID = $item->Item_ID;
            $landmark->type = $request->type;
            $landmark->open_time = $request->open_time;
            $landmark->close_time = $request->close_time;
            $landmark->egyptian_adult_price = $request->egyptian_adult_price;
            $landmark->egyptian_kid_price = $request->egyptian_kid_price;
            $landmark->egyptian_student_price = $request->egyptian_student_price;
            $landmark->foreigner_adult_price = $request->foreigner_adult_price;
            $landmark->foreigner_kid_price = $request->foreigner_kid_price;
            $landmark->foreigner_student_price = $request->foreigner_student_price;
            $landmark->save();

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $file_extension = $photo->getClientOriginalExtension();
                    $file_name = time() . rand(1000, 9999) . '.' . $file_extension;
                    $path = public_path('image/Landmark/photos');
                    $photo->move($path, $file_name);
                    Photo::create([
                        'Item_ID' => $item->Item_ID,
                        'image_path' => asset("image/Landmark/photos/$file_name"),
                    ]);
                }
            }
            $item->load([ 'landmark','photos']);

            return response()->json([
                'message' => 'landmark created successfully',
                'item' => $item->load([ 'landmark','photos'])
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }    
    }
    
    public function show_specific_item($id)
    {
        try {
            $landmark = Landmark::with(['item.photos'])->find($id);
    
            if (!$landmark) {
                return response()->json(['message' => 'failed'], 404);
            }
    
            if (!$landmark->item) {
                return response()->json(['message' => 'Item not found'], 404);
            }
    
            $reviews = Review::with('user')->where('Item_ID', $landmark->item->Item_ID)->get();
            $avgRating = $reviews->avg('rating_value');
            $totalReviews = $reviews->count();
    
            $landmark->item->reviews = $reviews;
            $landmark->item->average_rating = $avgRating;
            $landmark->item->total_reviews = $totalReviews;
    
            return response()->json([
                'message' => 'successful',
                'landmark' => $landmark
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'failed: ' . $e->getMessage()], 500);
        }
    }
    

    public function update_an_item(Request $request, $id)
    {
        $landmark = Landmark::find($id);
        if (!$landmark) {
            return response()->json(['message' => 'failed: Landmark not found'], 404);
        }
        $item = Item::where('Item_ID', $landmark->Item_ID)->first();
        if (!$item) {
            return response()->json(['message' => 'failed: Item not found'], 404);
        }
        $item->update($request->only(['name', 'description', 'city', 'location']));
        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $imagePath = $request->file('image')->store('items', 'public');
            $item->update(['image_path' => $imagePath]);
        }
        $landmark->update([
            'type' => $request->type ?? $landmark->type,
            'open_time' => $request->open_time ?? $landmark->open_time,
            'close_time' => $request->close_time ?? $landmark->close_time,
            'egyptian_adult_price' => $request->egyptian_adult_price ?? $landmark->egyptian_adult_price,
            'egyptian_kid_price' => $request->egyptian_kid_price ?? $landmark->egyptian_kid_price,
            'egyptian_student_price' => $request->egyptian_student_price ?? $landmark->egyptian_student_price,
            'foreigner_adult_price' => $request->foreigner_adult_price ?? $landmark->foreigner_adult_price,
            'foreigner_kid_price' => $request->foreigner_kid_price ?? $landmark->foreigner_kid_price,
            'foreigner_student_price' => $request->foreigner_student_price ?? $landmark->foreigner_student_price
        ]);
        $updated_item = Item::with(['landmark', 'photos'])->find($item->Item_ID);
        return response()->json([
            'message' => 'Update successful',
            'data' => $updated_item
        ]);
    }

    public function Delete_an_item($id)
    {
        try{
            $landmark = Landmark::find($id);
            if (!$landmark) {
                return response()->json(['message' => 'failed: Landmark not found'], 404);
            }
            $item = $landmark->item; 
            if (!$item) {
                return response()->json(['message' => 'failed: Item not found'], 404);
            }
            foreach ($item->photos as $photo) {
                Storage::disk('public')->delete($photo->image_path);
                $photo->delete();
            }
            $landmark->delete();
            $item->delete();
            return response()->json(['message' => 'Delete successful']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'faild :' . $e->getMessage()], 500);
        }
    }
}
