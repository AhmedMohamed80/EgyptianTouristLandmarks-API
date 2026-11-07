<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Hotel;
use App\Models\facilities;
use App\Models\Review;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    public function Display_all_item()
    {   
        $hotels = Hotel::with(['facilities','item.photos'])
        ->withAvg('reviews', 'rating_value')
        ->withCount('reviews') 
        ->paginate(10);

    return response()->json($hotels);
    }

    public function store_new_item(Request $request)
    {   
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'city' => 'required|string',
                'location' => 'required|url',
                'email'=>'nullable|string',
                'booking_link'=>'nullable|url',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'website_link' => 'nullable|url',
                'contact_number' => 'nullable|string|max:20',
                'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'facilitie.*'=>'nullable|string',
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
                $path = public_path('image/Hotel');
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $main->move($path, $file_name);
                $item->image_path = asset('image/Hotel/' . $file_name);
            }
            $item->save();

            $hotel = new Hotel();
            $hotel->Item_ID = $item->Item_ID;
            $hotel->booking_link=$request->booking_link;
            $hotel->email=$request->email;
            $hotel->website_link = $request->website_link;
            $hotel->contact_number = $request->contact_number;
            $hotel->save();

            if ($request->has('facilitie')) {
                foreach ($request->facilitie as $facilityName) {
                    $facility = new facilities();
                    $facility->hotel_id = $hotel->id;
                    $facility->facility_name = $facilityName;
                    $facility->save();
                }
            }


            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $file_extension = $photo->getClientOriginalExtension();
                    $file_name = time() . rand(1000, 9999) . '.' . $file_extension;
                    $path = public_path('image/Hotel/photos');
                    $photo->move($path, $file_name);
                    Photo::create([
                        'Item_ID' => $item->Item_ID,
                        'image_path' => asset("image/Hotel/photos/$file_name"),
                    ]);
                }
            }

            $item->load(['photos','hotel','hotel.facilities']);
            return response()->json([
                'message' => 'hotel created successfully',
                'item' => $item->load(['photos','hotel','hotel.facilities']),
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function show_specific_item($id)
    {
        try {
            $hotel = Hotel::with('facilities','item.photos')->find($id);
    
            if (!$hotel) {
                return response()->json(['message' => 'Hotel not found'], 404);
            }
    
            if (!$hotel->item) {
                return response()->json(['message' => 'Item not found'], 404);
            }
    
            $reviews = Review::with('user')->where('Item_ID', $hotel->item->Item_ID)->get();
            $avgRating = $reviews->avg('rating_value');
            $totalReviews = $reviews->count();
    
    
            return response()->json([
                'message' => 'successful',
                'hotel' => $hotel
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }
    
    

    public function update_an_item(Request $request, $hotel_id)
    {
        $hotel = Hotel::where('id', $hotel_id)->first();

        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        $item = Item::where('Item_ID', $hotel->Item_ID)->first();

        if (!$item) {
            return response()->json(['message' => 'Related item not found'], 404);
        }

        $item->update($request->only(['name', 'description', 'city', 'location']));

        $hotel->update([
            'website_link' => $request->has('website_link') ? $request->website_link : $hotel->website_link,
            'booking_link' => $request->has('booking_link') ? $request->booking_link : $hotel->booking_link,
            'email' => $request->has('email') ? $request->email : $hotel->email,
            'contact_number' => $request->has('contact_number') ? $request->contact_number : $hotel->contact_number,
        ]);

        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $imagePath = $request->file('image')->store('items', 'public');
            $item->image_path = $imagePath;
            $item->save();
        }
        if ($request->has('facilitie')) {
            $hotel->facilities()->delete();
            foreach ($request->facilitie as $facilitie) {
                facilities::create([
                    'hotel_id' => $hotel->id,
                    'facility_name' => $facilitie
                ]);
            }
        }

        return response()->json([
            'message' => 'Hotel updated successfully',
            'data' => Hotel::with('facilities','item.photos')->find($hotel->id)
        ]);
    }

    public function Delete_an_item($id)
    {
        try {
            $hotel = Hotel::where('id', $id)->first();
    
            if (!$hotel) {
                return response()->json(['message' => 'failed - Hotel not found'], 404);
            }
    
            $Item_ID = $hotel->Item_ID;
    
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

            $hotel->facilities()->delete();
    
            $hotel->delete();
    
            $item->delete();
    
            return response()->json(['message' => 'successful'], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'failed: ' . $e->getMessage()], 500);
        }
    }
    

}
