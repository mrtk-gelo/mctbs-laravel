<x-shell title="Assistance Settings | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'assistance'])</x-slot:nav>

    @php($routePrefix = auth()->user()->role->name === 'Staff' ? 'staff' : 'owner')
    <div class="container">
        <div class="page-header mt-3">
            <h1>Assistance</h1>
            <p>Manage approved answers, quick replies, and guest handoff messaging.</p>
        </div>

        @if ($houses->isEmpty())
            <div class="empty-state"><i class="fa fa-building"></i><h3>No properties available</h3><p>You need an authorized property to manage assistance content.</p></div>
        @else
            <form method="GET" action="{{ route($routePrefix.'.assistance.index') }}" class="card mb-3">
                <div class="card-body flex-between" style="gap:12px;flex-wrap:wrap">
                    <label class="form-label mb-0" for="house_id">Property</label>
                    <select class="form-control" style="max-width:320px" id="house_id" name="house_id" onchange="this.form.submit()">
                        @foreach ($houses as $house)
                            <option value="{{ $house->house_id }}" @selected($selectedHouse?->house_id === $house->house_id)>{{ $house->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="card mb-3">
                <div class="card-body">
                    <h2>Assistance settings</h2>
                    <form method="POST" action="{{ route($routePrefix.'.assistance.settings.update') }}">
                        @csrf
                        <input type="hidden" name="house_id" value="{{ $selectedHouse->house_id }}">
                        <label class="form-label" for="welcome_message">Welcome message</label>
                        <textarea class="form-control" id="welcome_message" name="welcome_message" rows="3" required>{{ $settings->welcome_message }}</textarea>
                        <label class="form-label mt-3" for="handoff_message">Human handoff message</label>
                        <textarea class="form-control" id="handoff_message" name="handoff_message" rows="3" required>{{ $settings->handoff_message }}</textarea>
                        <button class="btn btn-primary mt-3" type="submit">Save settings</button>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h2>Approved FAQs</h2>
                    <form method="POST" action="{{ route($routePrefix.'.assistance.faqs.store') }}" class="mb-4">
                        @csrf
                        <input type="hidden" name="house_id" value="{{ $selectedHouse->house_id }}">
                        <div class="grid gap-3" style="grid-template-columns:1fr 1fr">
                            <div><label class="form-label" for="category">Category</label><input class="form-control" id="category" name="category" placeholder="Payments" required></div>
                            <div><label class="form-label" for="question">Question</label><input class="form-control" id="question" name="question" placeholder="How do I submit payment?" required></div>
                        </div>
                        <label class="form-label mt-3" for="answer">Approved answer</label>
                        <textarea class="form-control" id="answer" name="answer" rows="3" required></textarea>
                        <label class="form-label mt-3" for="keywords">Matching keywords <span class="text-muted">(optional, comma-separated)</span></label>
                        <input class="form-control" id="keywords" name="keywords">
                        <label class="form-label mt-3" for="related_url">Related link <span class="text-muted">(optional)</span></label>
                        <input class="form-control" id="related_url" type="url" name="related_url">
                        <button class="btn btn-primary mt-3" type="submit">Add FAQ</button>
                    </form>

                    @forelse ($faqs as $faq)
                        <form method="POST" action="{{ route($routePrefix.'.assistance.faqs.update', $faq) }}" class="card mb-2">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="grid gap-3" style="grid-template-columns:1fr 1fr">
                                    <input class="form-control" name="category" value="{{ $faq->category }}" required>
                                    <input class="form-control" name="question" value="{{ $faq->question }}" required>
                                </div>
                                <textarea class="form-control mt-2" name="answer" rows="3" required>{{ $faq->answer }}</textarea>
                                <input class="form-control mt-2" name="keywords" value="{{ $faq->keywords }}" placeholder="Keywords">
                                <input class="form-control mt-2" type="url" name="related_url" value="{{ $faq->related_url }}" placeholder="Related link">
                                <label class="form-label mt-2"><input type="checkbox" name="is_active" value="1" @checked($faq->is_active)> Active</label>
                                <button class="btn btn-outline-primary mt-2" type="submit">Save FAQ</button>
                            </div>
                        </form>
                    @empty
                        <p class="text-muted">No FAQs configured for this property.</p>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2>Quick replies</h2>
                    <form method="POST" action="{{ route($routePrefix.'.assistance.quick-replies.store') }}" class="mb-4">
                        @csrf
                        <input type="hidden" name="house_id" value="{{ $selectedHouse->house_id }}">
                        <label class="form-label" for="title">Internal title</label>
                        <input class="form-control" id="title" name="title" required>
                        <label class="form-label mt-2" for="content">Reply content</label>
                        <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                        <label class="form-label mt-2" for="reply_category">Category <span class="text-muted">(optional)</span></label>
                        <input class="form-control" id="reply_category" name="category">
                        <button class="btn btn-primary mt-3" type="submit">Add quick reply</button>
                    </form>

                    @forelse ($quickReplies as $quickReply)
                        <form method="POST" action="{{ route($routePrefix.'.assistance.quick-replies.update', $quickReply) }}" class="card mb-2">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <input class="form-control" name="title" value="{{ $quickReply->title }}" required>
                                <textarea class="form-control mt-2" name="content" rows="3" required>{{ $quickReply->content }}</textarea>
                                <input class="form-control mt-2" name="category" value="{{ $quickReply->category }}" placeholder="Category">
                                <label class="form-label mt-2"><input type="checkbox" name="is_active" value="1" @checked($quickReply->is_active)> Active</label>
                                <button class="btn btn-outline-primary mt-2" type="submit">Save quick reply</button>
                            </div>
                        </form>
                    @empty
                        <p class="text-muted">No quick replies configured for this property.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</x-shell>
