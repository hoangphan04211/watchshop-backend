<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    /**
     * Lấy danh sách liên hệ
     */
    public function index(Request $request)
    {
        $query = Contact::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $contacts = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($contacts);
    }

    /**
     * Tạo mới liên hệ
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'required|string|max:20',
            'content' => 'required|string',
            'reply_id' => 'nullable|integer',
        ]);

        $contact = Contact::create([
            'user_id'    => Auth::id() ?? null,
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'],
            'content'    => $validated['content'],
            'reply_id'   => $validated['reply_id'] ?? 0,
            'created_by' => Auth::id() ?? 1,
            'status'     => 1,
        ]);

        return response()->json([
            'message' => 'Liên hệ đã được tạo thành công',
            'data'    => $contact,
        ], 201);
    }

    /**
     * Xem chi tiết liên hệ
     */
    public function show(string $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Không tìm thấy liên hệ'], 404);
        }

        return response()->json($contact);
    }

    /**
     * Cập nhật liên hệ
     */
    public function update(Request $request, string $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Không tìm thấy liên hệ'], 404);
        }

        $validated = $request->validate([
            'name'    => 'sometimes|required|string|max:255',
            'email'   => 'sometimes|required|email|max:255',
            'phone'   => 'sometimes|required|string|max:20',
            'content' => 'sometimes|required|string',
            'reply_id' => 'nullable|integer',
            'status'  => 'nullable|in:0,1',
        ]);

        $validated['updated_by'] = Auth::id() ?? $contact->updated_by;

        $contact->update($validated);

        return response()->json([
            'message' => 'Cập nhật liên hệ thành công',
            'data'    => $contact,
        ]);
    }

    /**
     * Thay đổi trạng thái liên hệ (0 = inactive, 1 = active)
     */
    public function toggleStatus(string $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Không tìm thấy liên hệ'], 404);
        }

        $contact->status = $contact->status ? 0 : 1;
        $contact->save();

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'status'  => $contact->status,
        ]);
    }

    /**
     * Xóa liên hệ
     */
    public function destroy(string $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Không tìm thấy liên hệ'], 404);
        }

        $contact->delete();

        return response()->json([
            'message' => 'Liên hệ đã được xóa thành công',
        ]);
    }
}
