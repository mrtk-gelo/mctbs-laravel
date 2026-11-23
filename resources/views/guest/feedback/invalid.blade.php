<x-shell title="Feedback Link Unavailable | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => ''])</x-slot:nav>

    <main class="container-sm" style="max-width:640px;margin:40px auto;padding:0 16px">
        <section style="padding:32px;border:1px solid #eadfd3;border-radius:16px;background:#fff;box-shadow:0 18px 48px rgba(83,54,31,.08);text-align:center">
            <div style="display:grid;width:54px;height:54px;place-items:center;margin:0 auto 16px;border-radius:50%;background:#fff3e8;color:#9a4f20">
                <i class="fa fa-link-slash" aria-hidden="true"></i>
            </div>
            <h1 style="margin:0 0 10px;color:#3d2c20;font-size:26px">Feedback link unavailable</h1>
            <p role="status" style="margin:0;color:#76695f;line-height:1.7">{{ $message }}</p>
        </section>
    </main>
</x-shell>
