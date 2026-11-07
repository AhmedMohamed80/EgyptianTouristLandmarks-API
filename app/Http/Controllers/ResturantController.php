<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Restaurant;
use App\Models\FacilitiesRestaurants;
use App\Models\Review;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

class ResturantController extends Controller
{
    public function Display_all_item()
    {
        try{
            $restaurants = Restaurant::with(['facilities_restaurants','item.photos'])
            ->withAvg('reviews', 'rating_value')
            ->withCount('reviews')  
            ->paginate(10);

            return response()->json($restaurants);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }  
    }

    public function store_new_item(Request $request)
    {   
        try {
            $request->validate([
                'name'           => 'required|string|max:255',
                'description'    => 'nullable|string',
                'city'           => 'required|string',
                'location'       => 'required|string',
                'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'cuisine_type'   => 'required|string',
                'website_link'   => 'nullable|url',
                'facebook_link'  => 'nullable|url',
                'photos.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'facilitie.*'    =>'nullable|string',
            ]);
    
            $item = new Item();
            $item->name = $request->name;
            $item->description = $request->description;
            $item->city = $request->city;
            $item->location = $request->location;
            $item->save();
            $item->refresh(); 
    
            if ($request->hasFile('image')) {
                $file_extension = $request->image->getClientOriginalExtension();
                $file_name = time() . '.' . $file_extension;
                $path = public_path('image/restaurants');
                $request->image->move($path, $file_name);
                $item->image_path = asset("image/restaurants/$file_name");
                $item->save();
            }
    
            $restaurant = new Restaurant();
            $restaurant->item_id = $item->Item_ID;
            $restaurant->cuisine_type = $request->cuisine_type;
            $restaurant->website_link = $request->website_link;
            $restaurant->facebook_link = $request->facebook_link;
            $restaurant->save();
    
            if ($request->has('facilitie')) {
                foreach ($request->facilitie as $facilityName) {
                    $facility = new FacilitiesRestaurants();
                    $facility->restaurant_id = $restaurant->id;
                    $facility->facility_name = $facilityName;
                    $facility->save();
                }
            }

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $file_extension = $photo->getClientOriginalExtension();
                    $file_name = time() . rand(1000, 9999) . '.' . $file_extension;
                    
                    $path = public_path('image/restaurants/photos');
                    
                    $photo->move($path, $file_name);
            
                    Photo::create([
                        'Item_ID' => $item->Item_ID,
                        'image_path' => asset("image/restaurants/photos/$file_name"),
                    ]);
                }
            }
            
    
            $item->load([ 'restaurant.facilities_restaurants','restaurant','photos']);

            return response()->json([
                'message' => 'restaurant created successfully',
                'item' => $item->load([ 'restaurant.facilities_restaurants','restaurant','photos'])
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }    
    }
    public function show_specific_item($id)
    {
        try {
            $restaurant = Restaurant::with(['facilities_restaurants','item.photos'])->find($id);
    
            if (!$restaurant) {
                return response()->json(['message' => 'failed'], 404);
            }
    
            if (!$restaurant->item) {
                return response()->json(['message' => 'Item not found'], 404);
            }
    
            $reviews = Review::with('user')->where('Item_ID', $restaurant->item->Item_ID)->get();
            $avgRating = $reviews->avg('rating_value'); 
            $totalReviews = $reviews->count(); 
    
            $restaurant->item->reviews = $reviews;
            $restaurant->item->average_rating = $avgRating;
            $restaurant->item->total_reviews = $totalReviews;
    
            return response()->json([
                'message' => 'successful',
                'restaurant' => $restaurant
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'failed: ' . $e->getMessage()], 500);
        }
    }
    

    public function update_an_item(Request $request, $id)
    {
        try {
            $restaurant = Restaurant::where('id', $id)->first();
    
            if (!$restaurant) {
                return response()->json(['message' => 'failed - Restaurant not found'], 404);
            }
    
            $item = Item::where('Item_ID', $restaurant->Item_ID)->first();
    
            if (!$item) {
                return response()->json(['message' => 'failed - Item not found'], 404);
            }
    
            $item->update($request->only(['name', 'description', 'city', 'location']));
    
            $restaurant->update($request->only(['cuisine_type', 'website_link','facebook_link']));
    
            if ($request->has('facilitie')) {
                $restaurant->facilities_restaurants()->delete();
                foreach ($request->facilitie as $facilitie) {
                    facilitiesRestaurants::create([
                        'restaurant_id' => $restaurant->id,
                        'facility_name' => $facilitie
                    ]);
                }
            }

            if ($request->hasFile('image')) {
                if (!empty($restaurant->image_path)) {
                    $oldImagePath = public_path('image/restaurants/' . basename($restaurant->image_path));
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
    
                $file_extension = $request->image->getClientOriginalExtension();
                $file_name = time() . '.' . $file_extension;
                $path = public_path('image/restaurants');
                $request->image->move($path, $file_name);
    
                $restaurant->image_path = asset("image/restaurants/$file_name");
                $restaurant->save();
            }
    
            return response()->json([
                'message' => 'restaurant updated successfully',
                'data' => restaurant::with('facilities_restaurants','item.photos')->find($restaurant->id),
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'failed: ' . $e->getMessage()], 500);
        }
    }
    
    

    public function Delete_an_item($id)
    {
        try {
            $Restaurant = Restaurant::where('id', $id)->first();
            if (!$Restaurant) {
                return response()->json(['message' => 'failed - Restaurant not found'], 404);
            }
            $Item_ID = $Restaurant->Item_ID;
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
                    $photoPath = public_path('image/Hotel/photos/' . $photo->image_path);
                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                        \Log::info("Deleted file: " . $photoPath);
                    } else {
                        \Log::error("File not found: " . $photoPath);
                    }
                }
            }
            $Restaurant->facilitiesrestaurants()->dalate();
            $Restaurant->delete();    
            $item->delete();
            return response()->json(['message' => 'successful'], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'failed: ' . $e->getMessage()], 500);
        }
    }
}
