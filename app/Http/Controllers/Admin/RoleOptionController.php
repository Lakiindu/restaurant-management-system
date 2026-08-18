<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoleOption;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RoleOptionController extends Controller
{
    public function index()
    {
        return view('admin.role-options.index');
    }

    public function fetchOptions(Request $request)
    {
        $query = RoleOption::with('page.category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('option_name', 'like', "%{$search}%")
                  ->orWhere('option_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('page_id')) {
            $query->where('page_id', $request->page_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $options = $query->orderBy('id', 'desc')->paginate(10);

        $data = $options->map(function($opt) {
            return [
                'id' => $opt->id,
                'option_name' => $opt->option_name,
                'option_code' => $opt->option_code,
                'page_id' => $opt->page_id,
                'page_name' => $opt->page->page_name ?? 'N/A',
                'page_code' => $opt->page->page_code ?? 'N/A',
                'category_name' => $opt->page->category->category_name ?? 'N/A',
                'status' => $opt->status,
                'created_at' => \Carbon\Carbon::parse($opt->created_at)->format('M d, Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $options->currentPage(),
                'last_page' => $options->lastPage(),
                'per_page' => $options->perPage(),
                'total' => $options->total(),
                'from' => $options->firstItem(),
                'to' => $options->lastItem(),
            ]
        ]);
    }

    public function getOption(int $id)
    {
        $option = RoleOption::with('page.category')->find($id);

        if (!$option) {
            return response()->json(['success' => false, 'message' => 'Option not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $option->id,
                'option_name' => $option->option_name,
                'option_code' => $option->option_code,
                'page_id' => $option->page_id,
                'page_name' => $option->page->page_name ?? 'N/A',
                'page_code' => $option->page->page_code ?? 'N/A',
                'category_name' => $option->page->category->category_name ?? 'N/A',
                'status' => $option->status,
                'created_at' => \Carbon\Carbon::parse($option->created_at)->format('M d, Y h:i A'),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'option_name' => 'required|string|max:45',
            'option_code' => 'required|string|max:255|unique:role_options,option_code|regex:/^[A-Z0-9_]+$/',
            'page_id' => 'required|exists:pages,page_id',
            'status' => 'required|in:0,1',
        ], [
            'option_code.regex' => 'Option code must be UPPERCASE letters, numbers, and underscores only (e.g. USER_ADD)',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        RoleOption::create([
            'option_name' => $request->option_name,
            'option_code' => strtoupper($request->option_code),
            'page_id' => $request->page_id,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Option created successfully!']);
    }

    public function update(Request $request, int $id)
    {
        $option = RoleOption::find($id);
        if (!$option) {
            return response()->json(['success' => false, 'message' => 'Option not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'option_name' => 'required|string|max:45',
            'option_code' => ['required', 'string', 'max:255', 'regex:/^[A-Z0-9_]+$/', Rule::unique('role_options', 'option_code')->ignore($option->id)],
            'page_id' => 'required|exists:pages,page_id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $option->update([
            'option_name' => $request->option_name,
            'option_code' => strtoupper($request->option_code),
            'page_id' => $request->page_id,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Option updated successfully!']);
    }

    public function destroy(int $id)
    {
        $option = RoleOption::find($id);
        if (!$option) {
            return response()->json(['success' => false, 'message' => 'Option not found'], 404);
        }

        $option->delete();
        return response()->json(['success' => true, 'message' => 'Option deleted successfully!']);
    }

    public function toggleStatus(int $id)
    {
        $option = RoleOption::find($id);
        if (!$option) {
            return response()->json(['success' => false, 'message' => 'Option not found'], 404);
        }

        $option->status = $option->status == 1 ? 0 : 1;
        $option->save();

        return response()->json([
            'success' => true,
            'message' => "Option " . ($option->status ? 'activated' : 'deactivated') . " successfully!"
        ]);
    }
}