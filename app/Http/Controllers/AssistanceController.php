<?php

namespace App\Http\Controllers;

use App\Models\ChatFaq;
use App\Models\ChatQuickReply;
use App\Models\TransientHouse;
use App\Services\AssistanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssistanceController extends Controller
{
    public function __construct(private AssistanceService $assistance) {}

    public function index(Request $request): View
    {
        $houses = TransientHouse::query()
            ->whereIn('house_id', $this->assistance->accessibleHouseIds($request->user()))
            ->orderBy('name')
            ->get();
        $house = $houses->firstWhere('house_id', (int) $request->integer('house_id')) ?? $houses->first();

        return view('assistance.index', [
            'houses' => $houses,
            'selectedHouse' => $house,
            'settings' => $house ? $this->assistance->settingsFor($house->house_id) : null,
            'faqs' => $house ? $this->assistance->faqsFor($request->user(), $house->house_id) : collect(),
            'units' => $house ? $house->units()->whereNull('archived_at')->orderBy('unit_type')->get() : collect(),
            'quickReplies' => $house ? $this->assistance->quickRepliesFor($request->user(), $house->house_id) : collect(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'house_id' => ['required', 'integer', 'exists:transient_houses,house_id'],
            'welcome_message' => ['required', 'string', 'max:2000'],
            'handoff_message' => ['required', 'string', 'max:2000'],
        ]);
        $this->assistance->saveSettings(
            $request->user(),
            (int) $data['house_id'],
            $data['welcome_message'],
            $data['handoff_message']
        );

        return back()->with('success', 'Assistance settings updated.');
    }

    public function storeFaq(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'house_id' => ['required', 'integer', 'exists:transient_houses,house_id'],
            'category' => ['required', 'string', 'max:100'],
            'unit_id' => ['nullable', 'integer', 'exists:units,unit_id'],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:10000'],
            'keywords' => ['nullable', 'string', 'max:1000'],
            'related_url' => ['nullable', 'url', 'max:500'],
        ]);
        $this->assistance->createFaq($request->user(), (int) $data['house_id'], $data);

        return back()->with('success', 'FAQ added.');
    }

    public function updateFaq(Request $request, ChatFaq $faq): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'unit_id' => ['nullable', 'integer', 'exists:units,unit_id'],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:10000'],
            'keywords' => ['nullable', 'string', 'max:1000'],
            'related_url' => ['nullable', 'url', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $this->assistance->updateFaq($request->user(), $faq, $data + ['is_active' => (bool) ($data['is_active'] ?? false)]);

        return back()->with('success', 'FAQ updated.');
    }

    public function storeQuickReply(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'house_id' => ['required', 'integer', 'exists:transient_houses,house_id'],
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:10000'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);
        $this->assistance->createQuickReply($request->user(), (int) $data['house_id'], $data);

        return back()->with('success', 'Quick reply added.');
    }

    public function updateQuickReply(Request $request, ChatQuickReply $quickReply): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:10000'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $this->assistance->updateQuickReply($request->user(), $quickReply, $data + ['is_active' => (bool) ($data['is_active'] ?? false)]);

        return back()->with('success', 'Quick reply updated.');
    }
}
