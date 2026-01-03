<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CastingCall;
use App\Models\CastingCallRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CastingCallController extends Controller
{
    /**
     * Get all casting calls (public)
     */
    public function index(Request $request)
    {
        try {
            $query = CastingCall::with([
                'recruiter', // This is actually the User model now
                'genre',
                'projectType',
                'country',
                'state',
                'requirements.subcategory'
            ])->published()->active();

            // Filters
            if ($request->has('genre_id')) {
                $query->where('genre_id', $request->genre_id);
            }

            if ($request->has('project_type_id')) {
                $query->where('project_type_id', $request->project_type_id);
            }

            if ($request->has('location')) {
                $query->where('location', 'LIKE', '%' . $request->location . '%');
            }

            if ($request->has('is_featured')) {
                $query->featured();
            }

            if ($request->has('is_urgent')) {
                $query->urgent();
            }

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('project_name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhereHas('requirements', function($subQuery) use ($search) {
                          $subQuery->where('role_name', 'LIKE', "%{$search}%")
                                   ->orWhere('role_description', 'LIKE', "%{$search}%")
                                   ->orWhere('role_type', 'LIKE', "%{$search}%");
                      });
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $castingCalls = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Casting calls retrieved successfully',
                'data' => $castingCalls,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve casting calls',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's (recruiter's) casting calls
     */
    public function recruiterIndex(Request $request)
    {
        try {
            $user = $request->user();

            $query = CastingCall::with([
                'genre',
                'projectType',
                'country',
                'state',
                'requirements.subcategory',
                'media'
            ])->byUser($user->id);

            // Status filter
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $castingCalls = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Casting calls retrieved successfully',
                'data' => $castingCalls,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve casting calls',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single casting call
     */
    public function show(Request $request, $id)
    {
        try {
            $castingCall = CastingCall::with([
                'recruiter', // User model
                'genre',
                'projectType',
                'country',
                'state',
                'requirements.subcategory',
                'media'
            ])->findOrFail($id);

            // Increment views for public access
            if (!$request->user() || $request->user()->id !== $castingCall->recruiter_id) {
                $castingCall->incrementViews();
            }

            return response()->json([
                'success' => true,
                'message' => 'Casting call retrieved successfully',
                'data' => $castingCall,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Casting call not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

     /**
     * Create new casting call
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                // Project Details
                'project_type_id' => 'required|integer|exists:project_types,id',
                'genre_id' => 'nullable|uuid|exists:genres,id',
                'project_name' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'director' => 'nullable|string|max:255',
                'production_company' => 'nullable|string|max:255',
                'audition_date' => 'nullable|date',
                'deadline' => 'required|date|after:today',
                'country_id' => 'required|integer|exists:countries,id',
                'state_id' => 'nullable|integer|exists:states,id',
                'city' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:500',
                'description' => 'required|string',
                'synopsis' => 'nullable|string',
                'additional_notes' => 'nullable|string',
                
                // Audition Details
                'audition_location' => 'nullable|string|max:500',
                'is_remote_audition' => 'boolean',
                'audition_script' => 'nullable|string',
                'audition_duration_seconds' => 'nullable|integer|min:1',
                'submission_requirements' => 'nullable|array',
                
                // Compensation
                'compensation_type' => 'nullable|in:paid,unpaid,deferred,tbd',
                'rate_amount' => 'nullable|numeric|min:0',
                'rate_currency' => 'nullable|string|size:3',
                'rate_period' => 'nullable|in:hourly,daily,weekly,monthly,project',
                
                // Status
                'status' => 'nullable|in:draft,published,closed',
                'visibility' => 'nullable|in:public,private',
                'is_featured' => 'boolean',
                'is_urgent' => 'boolean',
                
                // Requirements
                'requirements' => 'required|array|min:1',
                'requirements.*.role_name' => 'required|string|max:255',
                'requirements.*.role_description' => 'nullable|string',
                'requirements.*.role_type' => 'nullable|string|max:50',
                'requirements.*.gender' => 'nullable|in:male,female,non-binary,any',
                'requirements.*.age_group' => 'nullable|string|max:50',
                'requirements.*.skin_tone' => 'nullable|string|max:50',
                'requirements.*.height' => 'nullable|string|max:50',
                'requirements.*.subcategory_id' => 'nullable|uuid|exists:subcategories,id',
                'requirements.*.required_skills' => 'nullable|array',
                'requirements.*.required_skills.*' => 'string|max:100',
                
                // Media - UPDATED to accept actual files
                'media' => 'nullable|array|max:10',
                'media.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png,mp4,mov|max:51200', // 50MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            // Create casting call with user.id as recruiter_id
            $castingCallData = $request->except(['requirements', 'media']);
            $castingCallData['recruiter_id'] = $user->id;
            
            $castingCall = CastingCall::create($castingCallData);

            // Create requirements
            if ($request->has('requirements')) {
                foreach ($request->requirements as $index => $requirementData) {
                    $requirementData['display_order'] = $index + 1;
                    $castingCall->requirements()->create($requirementData);
                }
            }

            // ✅ NEW: Handle file uploads and create media records
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    // Store the file
                    $path = $file->store('casting-calls', 'public');
                    
                    // Create media record in database
                    DB::table('media')->insert([
                        'id' => Str::uuid(),
                        'uuid' => Str::uuid(),
                        'model_type' => CastingCall::class,
                        'model_id' => $castingCall->id,
                        'file_name' => $file->getClientOriginalName(),
                        'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'disk' => 'public',
                        'collection_name' => 'documents',
                        'order_column' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            // Load relationships
            $castingCall->load([
                'genre',
                'projectType',
                'country',
                'state',
                'requirements.subcategory',
                'media'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Casting call created successfully',
                'data' => $castingCall,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create casting call',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update casting call
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            $castingCall = CastingCall::findOrFail($id);

            // Check ownership
            if ($castingCall->recruiter_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this casting call',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                // Project Details
                'project_type_id' => 'sometimes|required|integer|exists:project_types,id',
                'genre_id' => 'nullable|uuid|exists:genres,id',
                'project_name' => 'sometimes|required|string|max:255',
                'title' => 'sometimes|required|string|max:255',
                'director' => 'nullable|string|max:255',
                'production_company' => 'nullable|string|max:255',
                'audition_date' => 'nullable|date',
                'deadline' => 'sometimes|required|date|after:today',
                'country_id' => 'sometimes|required|integer|exists:countries,id',
                'state_id' => 'nullable|integer|exists:states,id',
                'city' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:500',
                'description' => 'sometimes|required|string',
                'synopsis' => 'nullable|string',
                'additional_notes' => 'nullable|string',
                
                // Audition Details
                'audition_location' => 'nullable|string|max:500',
                'is_remote_audition' => 'boolean',
                'audition_script' => 'nullable|string',
                'audition_duration_seconds' => 'nullable|integer|min:1',
                'submission_requirements' => 'nullable|array',
                
                // Compensation
                'compensation_type' => 'nullable|in:paid,unpaid,deferred,tbd',
                'rate_amount' => 'nullable|numeric|min:0',
                'rate_currency' => 'nullable|string|size:3',
                'rate_period' => 'nullable|in:hourly,daily,weekly,monthly,project',
                
                // Status
                'status' => 'nullable|in:draft,published,closed',
                'visibility' => 'nullable|in:public,private',
                'is_featured' => 'boolean',
                'is_urgent' => 'boolean',
                
                // Requirements
                'requirements' => 'sometimes|array|min:1',
                'requirements.*.id' => 'nullable|uuid|exists:casting_call_requirements,id',
                'requirements.*.role_name' => 'required|string|max:255',
                'requirements.*.role_description' => 'nullable|string',
                'requirements.*.role_type' => 'nullable|string|max:50',
                'requirements.*.gender' => 'nullable|in:male,female,non-binary,any',
                'requirements.*.age_group' => 'nullable|string|max:50',
                'requirements.*.skin_tone' => 'nullable|string|max:50',
                'requirements.*.height' => 'nullable|string|max:50',
                'requirements.*.subcategory_id' => 'nullable|uuid|exists:subcategories,id',
                'requirements.*.required_skills' => 'nullable|array',
                'requirements.*.required_skills.*' => 'string|max:100',
                
                // Media - UPDATED to accept actual files
                'media' => 'nullable|array|max:10',
                'media.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png,mp4,mov|max:51200',
                
                // Option to remove specific media by ID
                'remove_media_ids' => 'nullable|array',
                'remove_media_ids.*' => 'uuid|exists:media,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            // Update casting call
            $castingCallData = $request->except(['requirements', 'media', 'remove_media_ids']);
            $castingCall->update($castingCallData);

            // Update requirements
            if ($request->has('requirements')) {
                // Delete existing requirements
                $castingCall->requirements()->delete();
                
                // Create new requirements
                foreach ($request->requirements as $index => $requirementData) {
                    unset($requirementData['id']); // Remove id if present
                    $requirementData['display_order'] = $index + 1;
                    $castingCall->requirements()->create($requirementData);
                }
            }

            // ✅ NEW: Remove specific media if requested
            if ($request->has('remove_media_ids') && !empty($request->remove_media_ids)) {
                foreach ($request->remove_media_ids as $mediaId) {
                    // Get media record to delete file
                    $media = DB::table('media')->where('id', $mediaId)->first();
                    if ($media && $media->model_id === $castingCall->id) {
                        // Delete file from storage
                        \Storage::disk($media->disk)->delete($media->file_name);
                        // Delete database record
                        DB::table('media')->where('id', $mediaId)->delete();
                    }
                }
            }

            // ✅ NEW: Add new media files
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    // Store the file
                    $path = $file->store('casting-calls', 'public');
                    
                    // Create media record
                    DB::table('media')->insert([
                        'id' => Str::uuid(),
                        'uuid' => Str::uuid(),
                        'model_type' => CastingCall::class,
                        'model_id' => $castingCall->id,
                        'file_name' => $file->getClientOriginalName(),
                        'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'disk' => 'public',
                        'collection_name' => 'documents',
                        'order_column' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            // Load relationships
            $castingCall->load([
                'genre',
                'projectType',
                'country',
                'state',
                'requirements.subcategory',
                'media'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Casting call updated successfully',
                'data' => $castingCall,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update casting call',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete casting call
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $castingCall = CastingCall::findOrFail($id);

            // Check ownership
            if ($castingCall->recruiter_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this casting call',
                ], 403);
            }

            $castingCall->delete();

            return response()->json([
                'success' => true,
                'message' => 'Casting call deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete casting call',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Publish casting call
     */
    public function publish(Request $request, $id)
    {
        try {
            $user = $request->user();
            $castingCall = CastingCall::findOrFail($id);

            // Check ownership
            if ($castingCall->recruiter_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to publish this casting call',
                ], 403);
            }

            $castingCall->update(['status' => 'published']);

            return response()->json([
                'success' => true,
                'message' => 'Casting call published successfully',
                'data' => $castingCall,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish casting call',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Close casting call
     */
    public function close(Request $request, $id)
    {
        try {
            $user = $request->user();
            $castingCall = CastingCall::findOrFail($id);

            // Check ownership
            if ($castingCall->recruiter_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to close this casting call',
                ], 403);
            }

            $castingCall->update(['status' => 'closed']);

            return response()->json([
                'success' => true,
                'message' => 'Casting call closed successfully',
                'data' => $castingCall,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to close casting call',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}