@extends('frontend.layout')

@section('title', $policy->title)

@section('content')
<style>
.policy-wrap { max-width: 960px; margin: 40px auto; padding: 0 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #374151; }
.policy-head { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 35px; margin-bottom: 25px; }
.policy-head h1 { font-size: 28px; color: #1f2937; margin-bottom: 6px; }
.policy-head .meta { font-size: 13px; color: #6b7280; }
.policy-nav { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 25px; }
.policy-nav a { padding: 8px 16px; background: #fff; border: 1px solid #e5e7eb; border-radius: 20px; text-decoration: none; font-size: 13px; color: #4b5563; font-weight: 500; transition: all .15s ease; }
.policy-nav a.active { background: #0d5a39; color: #fff; border-color: #0d5a39; }
.policy-nav a:hover { background: #16a34a; color: #fff; border-color: #16a34a; }
.policy-layout { display: grid; grid-template-columns: 220px 1fr; gap: 25px; align-items: start; }
@media (max-width: 800px) { .policy-layout { grid-template-columns: 1fr; } .policy-sidebar { order: -1; } }
.policy-sidebar { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; position: sticky; top: 20px; }
.policy-sidebar h3 { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
.policy-sidebar ul { list-style: none; }
.policy-sidebar li { margin-bottom: 6px; }
.policy-sidebar a { color: #4b5563; text-decoration: none; font-size: 14px; display: block; padding: 6px 10px; border-radius: 6px; transition: all .15s ease; }
.policy-sidebar a:hover, .policy-sidebar a.active { background: #f0fdf4; color: #0d5a39; font-weight: 600; }
.policy-body { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 35px 40px; line-height: 1.7; }
.policy-body h2 { font-size: 18px; color: #1f2937; margin: 25px 0 10px; padding-bottom: 6px; border-bottom: 2px solid #f3f4f6; }
.policy-body h2:first-child { margin-top: 0; }
.policy-body p { margin-bottom: 12px; font-size: 14.5px; }
.policy-body ul, .policy-body ol { margin: 10px 0 15px 22px; }
.policy-body li { margin-bottom: 6px; font-size: 14.5px; }
.policy-body a { color: #0d5a39; text-decoration: underline; }
.policy-body table.policy-table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 14px; }
.policy-body .policy-table th, .policy-body .policy-table td { border: 1px solid #e5e7eb; padding: 10px 14px; text-align: left; }
.policy-body .policy-table th { background: #f9fafb; color: #1f2937; font-weight: 600; }
</style>

<div class="policy-wrap">
    <div class="policy-head">
        <h1>{{ $policy->title }}</h1>
        <div class="meta">Last updated: {{ $policy->updated_at->format('F j, Y') }}</div>
    </div>

    <div class="policy-layout">
        <aside class="policy-sidebar">
            <h3>Policies</h3>
            <ul>
                @foreach($policies as $p)
                    <li>
                        <a href="{{ route('policy.show', $p) }}" class="{{ $p->slug === $policy->slug ? 'active' : '' }}">
                            {{ $p->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>

        <article class="policy-body">
            {{-- Render raw HTML stored in the DB --}}
            {!! $policy->body !!}
        </article>
    </div>
</div>
@endsection