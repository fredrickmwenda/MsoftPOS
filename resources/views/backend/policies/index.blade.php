@extends('backend.layout.main')

@section('title', 'Policies')

@section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4>Policies</h4>
                        <div>
                            <a href="{{ route('policies.create') }}" class="btn btn-sm btn-info">
                                <i class="dripicons-add"></i> + New Policy
                            </a>
                        </div>
                    </div>

                    <!-- <div class="admin-card-header d-flex justify-between align-center">
                        <h2>Policies</h2>
                        <a href="{{ route('policies.create') }}" class="btn btn-primary">+ New Policy</a>
                    </div> -->

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="table-responsive">
                    <table id="policies-table" class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Order</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($policies as $policy)
                                <tr>
                                    <td>{{ $policy->id }}</td>
                                    <td>
                                        <a href="{{ route('policies.show', $policy) }}" target="_blank">
                                            <strong>{{ $policy->title }}</strong>
                                        </a>
                                    </td>
                                    <td><code>{{ $policy->slug }}</code></td>
                                    <td>
                                        <form method="POST" action="{{ route('policies.toggle', $policy) }}">
                                            @csrf
                                            <button type="submit" class="badge {{ $policy->is_active ? 'badge-success' : 'badge-muted' }}">
                                                {{ $policy->is_active ? 'Active' : 'Hidden' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>{{ $policy->sort_order }}</td>
                                    <td>{{ $policy->updated_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('policies.edit', $policy) }}" class="btn btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('policies.destroy', $policy) }}" onsubmit="return confirm('Delete this policy?');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center; padding: 40px; color: #6b7280;">
                                        No policies yet. <a href="{{ route('policies.create') }}">Create one</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>

                    {{ $policies->links() }}
                    
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script type="text/javascript">

    $("ul#setting").siblings('a').attr('aria-expanded','true');
    $("ul#setting").addClass("show");
    $("ul#setting #policies-menu").addClass("active");

    </script>
@endpush