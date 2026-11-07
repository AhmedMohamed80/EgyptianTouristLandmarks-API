<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\ShoppingCenter;
use App\Models\Review;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

class shoping_centerController extends Controller
{
    public function Display_all_item()
    {   
        $shoppingCenters = ShoppingCenter::with(['item.photos'])
            ->withAvg('reviews', 'rating_value')  
            ->withCount('reviews')  
            ->paginate(10);
    
        return response()->json($shoppingCenters);
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
                'website_link' => 'nullable|url',
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
                $path = public_path('image/ShoppingCenter');                
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $main->move($path, $file_name);
                $item->image_path = asset('image/ShoppingCenter/' . $file_name);
            }
            $item->save();

            $shoppingCenter = new ShoppingCenter();
            $shoppingCenter->Item_ID = $item->Item_ID;
            $shoppingCenter->website_link = $request->website_link;
            $shoppingCenter->save();

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $file_extension = $photo->getClientOriginalExtension();
                    $file_name = time() . rand(1000, 9999) . '.' . $file_extension;
                    $path = public_path('image/ShoppingCenter/photos');
                    $photo->move($path, $file_name);
                    Photo::create([
                        'Item_ID' => $item->Item_ID,
                        'image_path' => asset("image/ShoppingCenter/photos/$file_name"),
                    ]);
                }
            }

            $item->load([ 'shoppingCenter','photos']);

            return response()->json([
                'message' => 'shoppingCenter created successfully',
                'item' => $item->load([ 'shoppingCenter','photos'])
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function show_specific_item($id)
    {
        try {
            $shoppingCenter = ShoppingCenter::with('item.photos')->find($id);
    
            if (!$shoppingCenter) {
                return response()->json(['message' => 'Shopping center not found'], 404);
            }
    
            if (!$shoppingCenter->item) {
                return response()->json(['message' => 'Item not found'], 404);
            }
    
            $reviews = Review::with('user')->where('Item_ID', $shoppingCenter->item->Item_ID)->get();
            $avgRating = $reviews->avg('rating_value');
            $totalReviews = $reviews->count();
    
            $shoppingCenter->item->reviews = $reviews;
            $shoppingCenter->item->average_rating = $avgRating;
            $shoppingCenter->item->total_reviews = $totalReviews;
    
            return response()->json([
                'message' => 'successful',
                'shopping_center' => $shoppingCenter
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }
    

    public function update_an_item(Request $request, $shopping_center_id)
    {
        $shoppingCenter = ShoppingCenter::where('id', $shopping_center_id)->first();

        if (!$shoppingCenter) {
            return response()->json(['message' => 'Shopping center not found'], 404);
        }

        $item = Item::where('Item_ID', $shoppingCenter->Item_ID)->first();

        if (!$item) {
            return response()->json(['message' => 'Related item not found'], 404);
        }

        $item->update($request->only(['name', 'description', 'city', 'location']));

        $shoppingCenter->update([
            'website_link' => $request->has('website_link') ? $request->website_link : $shoppingCenter->website_link,
        ]);

        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $imagePath = $request->file('image')->store('items', 'public');
            $item->image_path = $imagePath;
            $item->save();
        }

        return response()->json([
            'message' => 'Shopping center updated successfully',
            'data' => ShoppingCenter::with('item.photos')->find($shoppingCenter->id)
        ]);
    }

    public function Delete_an_item($id)
    {
        try {
            $shoppingCenter = ShoppingCenter::where('id', $id)->first();
    
            if (!$shoppingCenter) {
                return response()->json(['message' => 'failed - Shopping center not found'], 404);
            }
    
            $Item_ID = $shoppingCenter->Item_ID;
    
            if (!$Item_ID) {
                return response()->json(['message' => 'failed - Item_ID not found'], 404);
            }
    
            $item = Item::where('Item_ID', $Item_ID)->first();
    
            if (!$item) {
                return response()->json(['message' => 'failed - Item not found'], 404);
            }
    
            $photos = Photo::where('Item_ID', $Item_ID)->get();
    
            if ($photos->isNotEmpty()) {
                foreach ($photos as $photo) {
                    $photoPath = public_path('image/ShoppingCenter/photos/' . $photo->image_path);

                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                        \Log::info("Deleted file: " . $photoPath);
                    } else {
                        \Log::error("File not found: " . $photoPath);
                    }

                }
            }
    
            $shoppingCenter->delete();
    
            $item->delete();
    
            return response()->json(['message' => 'successful'], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'failed: ' . $e->getMessage()], 500);
        }
    }
}
