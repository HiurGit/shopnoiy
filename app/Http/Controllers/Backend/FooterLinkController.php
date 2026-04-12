<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\FooterLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FooterLinkController extends Controller
{
    public function index(): View
    {
        $footerLinks = FooterLink::query()->orderBy('group_name')->orderBy('sort_order')->orderBy('id')->get();

        return view('backend.footer-links.index', compact('footerLinks'));
    }

    public function create(): View
    {
        return view('backend.footer-links.create');
    }

    public function store(Request $request): RedirectResponse
    {
        FooterLink::create([
            'group_name' => $request->input('group_name', 'general'),
            'title' => $request->input('title', 'Link mới'),
            'url' => $request->input('url', '#'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('backend.footer-links')->with('success', 'Đã tạo link chân trang.');
    }

    public function show(FooterLink $footerLink): View
    {
        return view('backend.footer-links.show', compact('footerLink'));
    }

    public function edit(FooterLink $footerLink): View
    {
        return view('backend.footer-links.edit', compact('footerLink'));
    }

    public function update(Request $request, FooterLink $footerLink): RedirectResponse
    {
        $footerLink->update([
            'group_name' => $request->input('group_name', $footerLink->group_name),
            'title' => $request->input('title', $footerLink->title),
            'url' => $request->input('url', $footerLink->url),
            'sort_order' => (int) $request->input('sort_order', $footerLink->sort_order),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('backend.footer-links')->with('success', 'Đã cập nhật link chân trang.');
    }

    public function destroy(Request $request, FooterLink $footerLink): RedirectResponse|JsonResponse
    {
        $footerLink->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa link chân trang.',
                'footer_link_id' => $footerLink->id,
            ]);
        }

        return redirect()->route('backend.footer-links')->with('success', 'Đã xóa link chân trang.');
    }
}
