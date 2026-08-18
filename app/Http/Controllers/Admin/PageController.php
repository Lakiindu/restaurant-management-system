<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index');
    }

    public function fetchPages(Request $request)
    {
        $query = Page::with('category')->withCount('roleOptions');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('page_name', 'like', "%{$search}%")
                    ->orWhere('page_code', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $pages = $query->orderBy('created_at', 'desc')->paginate(10);

        $data = $pages->map(function ($p) {
            return [
                'page_id' => $p->page_id,
                'page_name' => $p->page_name,
                'page_code' => $p->page_code,
                'route_name' => $p->route_name ?? '-',
                'description' => $p->description ?? '-',
                'category_id' => $p->category_id,
                'category_name' => $p->category->category_name ?? 'N/A',
                'options_count' => $p->role_options_count,
                'status' => $p->status,
                'created_at' => \Carbon\Carbon::parse($p->created_at)->format('M d, Y'),
                'updated_at' => \Carbon\Carbon::parse($p->updated_at)->format('M d, Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
                'from' => $pages->firstItem(),
                'to' => $pages->lastItem(),
            ]
        ]);
    }

    public function getPage(int $id)
    {
        $page = Page::with('category')->withCount('roleOptions')->find($id);

        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Page not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'page_id' => $page->page_id,
                'page_name' => $page->page_name,
                'page_code' => $page->page_code,
                'route_name' => $page->route_name,
                'description' => $page->description,
                'category_id' => $page->category_id,
                'category_name' => $page->category->category_name ?? 'N/A',
                'options_count' => $page->role_options_count,
                'status' => $page->status,
                'created_at' => \Carbon\Carbon::parse($page->created_at)->format('M d, Y h:i A'),
                'updated_at' => \Carbon\Carbon::parse($page->updated_at)->format('M d, Y h:i A'),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_name' => 'required|string|max:45',
            'page_code' => 'required|string|max:255|unique:pages,page_code|regex:/^[A-Z0-9_]+$/',
            'route_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'category_id' => 'required|exists:page_categories,category_id',
            'status' => 'required|in:0,1',
        ], [
            'page_code.regex' => 'Page code must be UPPERCASE letters, numbers, and underscores only (e.g. USER_LIST)',
            'page_code.unique' => 'This page code already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Page::create([
            'page_name' => $request->page_name,
            'page_code' => strtoupper($request->page_code),
            'route_name' => $request->route_name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Page created successfully!']);
    }

    public function update(Request $request, int $id)
    {
        $page = Page::find($id);
        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Page not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'page_name' => 'required|string|max:45',
            'page_code' => ['required', 'string', 'max:255', 'regex:/^[A-Z0-9_]+$/', Rule::unique('pages', 'page_code')->ignore($page->page_id, 'page_id')],
            'route_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'category_id' => 'required|exists:page_categories,category_id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $page->update([
            'page_name' => $request->page_name,
            'page_code' => strtoupper($request->page_code),
            'route_name' => $request->route_name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Page updated successfully!']);
    }

    public function destroy(int $id)
    {
        $page = Page::find($id);
        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Page not found'], 404);
        }

        $optionsCount = $page->roleOptions()->count();
        if ($optionsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete. {$optionsCount} option(s) exist for this page. Delete them first."
            ], 403);
        }

        $page->delete();
        return response()->json(['success' => true, 'message' => 'Page deleted successfully!']);
    }

    public function toggleStatus(int $id)
    {
        $page = Page::find($id);
        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Page not found'], 404);
        }

        $page->status = $page->status == 1 ? 0 : 1;
        $page->save();

        return response()->json([
            'success' => true,
            'message' => "Page " . ($page->status ? 'activated' : 'deactivated') . " successfully!"
        ]);
    }

    public function getActivePages()
    {
        $pages = Page::where('status', 1)
            ->orderBy('page_name')
            ->get(['page_id', 'page_name', 'page_code']);

        return response()->json(['success' => true, 'data' => $pages]);
    }
}
