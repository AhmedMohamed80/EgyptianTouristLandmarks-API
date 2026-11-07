<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\TripInclude;
use App\Models\Exclude;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

class TripController extends Controller
{
    // عرض جميع الرحلات مع البيانات المرتبطة
    public function Display_all_trip()
    {
        $trips = Trip::with(['photos', 'includes', 'excludes'])
            ->paginate(10); // استخدام التصفح لتقليل البيانات

        return response()->json($trips);
    }

    // عرض تفاصيل رحلة معينة
    public function show_specific_trip($id)
    {
        try {
            $trip = Trip::with(['photos', 'includes', 'excludes'])->find($id);

            if (!$trip) {
                return response()->json(['message' => 'Trip not found'], 404);
            }

            return response()->json([
                'message' => 'Successful',
                'trip' => $trip
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    // إنشاء رحلة جديدة
    public function store_new_trip(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'from_location' => 'required|string',
                'to_location' => 'required|string',
                'trip_date' => 'required|date',
                'offers' => 'nullable|numeric',
                'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',                'includes.*' => 'nullable|string',
                'excludes.*' => 'nullable|string',
            ]);
    
            // Create the trip
            $trip = new Trip();
            $trip->name = $request->name;
            $trip->description = $request->description;
            $trip->from_location = $request->from_location;
            $trip->to_location = $request->to_location;
            $trip->trip_date = $request->trip_date;
            $trip->trip_status = $request->has('trip_status') ? $request->trip_status : 1;
            $trip->offers = $request->offers;
            
            if ($request->hasFile('main_image')) {
                $main = $request->file('main_image');
                $file_extension = $main->getClientOriginalExtension();
                $file_name = time() . rand(1000, 9999) . '.' . $file_extension;
                $path = public_path('image/trips');
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }            
                $main->move($path, $file_name);
                $trip->main_image = url('image/trips/' . $file_name);

            }

    
            $trip->save();
    
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $file_extension = $photo->getClientOriginalExtension();
                    $file_name = time() . rand(1000, 9999) . '.' . $file_extension;
                    $path = public_path('image/trips/photos');
                    $photo->move($path, $file_name);
                    Photo::create([
                        'Item_ID' => null,
                        'trip_id'=>$trip->id,
                        'image_path' => asset("image/trips/photos/$file_name"),
                    ]);
                }
            }
            
            // Store the 'includes' points
            if ($request->has('includes')) {
                foreach ($request->includes as $include) {
                    $trip->includes()->create(['point' => $include]);
                }
            }
    
            // Store the 'excludes' points
            if ($request->has('excludes')) {
                foreach ($request->excludes as $exclude) {
                    $trip->excludes()->create(['point' => $exclude]);
                }
            }           
    
            // Load relationships
            $trip->load(['photos', 'includes', 'excludes']);
    
            // Return the response
            return response()->json([
                'message' => 'Trip created successfully',
                'trip' => $trip
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }
    



    // تحديث رحلة
    public function update_an_trip(Request $request, $id)
    {
        try {
            $trip = Trip::find($id);

            if (!$trip) {
                return response()->json(['message' => 'Trip not found'], 404);
            }

            $trip->update($request->only([
                'name', 'description', 'from_location', 'to_location', 
                'trip_date', 'trip_status', 'offers'
            ]));

            // تحديث الصورة الرئيسية
            if ($request->hasFile('main_image')) {
                // حذف الصورة القديمة إذا كانت موجودة
                if ($trip->main_image) {
                    Storage::disk('public')->delete($trip->main_image);
                }

                $imagePath = $request->file('main_image')->store('trips', 'public');
                $trip->main_image = $imagePath;
                $trip->save();
            }

            // تحديث TripIncludes
            if ($request->has('includes')) {
                $trip->includes()->delete();
                foreach ($request->includes as $include) {
                    TripInclude::create([
                        'trip_id' => $trip->id,
                        'point' => $include
                    ]);
                }
            }

            // تحديث Excludes
            if ($request->has('excludes')) {
                $trip->excludes()->delete();
                foreach ($request->excludes as $exclude) {
                    Exclude::create([
                        'trip_id' => $trip->id,
                        'point' => $exclude
                    ]);
                }
            }

            // تحديث Photos
            if ($request->hasFile('photos')) {
                $trip->photos()->delete(); // حذف الصور القديمة
                foreach ($request->file('photos') as $photo) {
                    $photoPath = $photo->store('trips/photos', 'public');
                    Photo::create([
                        'trip_id' => $trip->id,
                        'image_path' => $photoPath
                    ]);
                }
            }

            $trip->load(['photos', 'includes', 'excludes']);

            return response()->json([
                'message' => 'Trip updated successfully',
                'trip' => $trip->load(['photos', 'includes', 'excludes']) // تحميل البيانات المرتبطة
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    // حذف رحلة
    public function Delete_an_trip($id)
    {
        try {
            $trip = Trip::find($id);

            if (!$trip) {
                return response()->json(['message' => 'Trip not found'], 404);
            }

            // حذف الصور المرتبطة بالرحلة
            $trip->photos()->each(function ($photo) {
                Storage::disk('public')->delete($photo->image_path);
            });

            // حذف شامل لجميع النقاط والاستثناءات والصور المرتبطة بالرحلة
            $trip->includes()->delete();
            $trip->excludes()->delete();
            $trip->photos()->delete();

            $trip->delete();

            return response()->json(['message' => 'Trip deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }
}
