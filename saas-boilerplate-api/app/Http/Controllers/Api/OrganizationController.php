<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $organizations = $request->user()
            ->organizations()
            ->withCount('members')
            ->with('owner:id,name,email')
            ->latest()
            ->get();

        return response()->json($organizations);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $organization = Organization::create([
            'name'     => $data['name'],
            'owner_id' => $request->user()->id,
        ]);

        // Add owner as member
        $organization->members()->attach($request->user()->id, ['role' => 'owner']);

        // Log activity
        ActivityLog::create([
            'organization_id' => $organization->id,
            'user_id'         => $request->user()->id,
            'action'          => 'organization.created',
            'entity_type'     => 'organization',
            'entity_id'       => $organization->id,
            'metadata'        => ['name' => $organization->name],
        ]);

        return response()->json($organization->load('owner:id,name,email'), 201);
    }

    public function show(Request $request, Organization $organization)
    {
        $this->authorize('view', $organization);

        return response()->json(
            $organization->load('owner:id,name,email')
                         ->loadCount('members', 'projects')
        );
    }

    public function update(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $organization->update($data);

        return response()->json($organization);
    }

    public function destroy(Request $request, Organization $organization)
    {
        $this->authorize('delete', $organization);
        $organization->delete();

        return response()->json(['message' => 'Organization deleted successfully']);
    }
}
