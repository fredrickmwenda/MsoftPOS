@extends('backend.layout.main')

@section('title', 'Edit Policy')

@section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4>Edit Policy: <small>{{ $policy->title }}</small></h4>
                        <a href="{{ route('policies.index') }}" class="btn btn-sm btn-info">&larr; Back to list</a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('policies.update', $policy) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="title">Title <span class="text-danger">*</span></label>
                                <input type="text" id="title" name="title" value="{{ old('title', $policy->title) }}" class="form-control" required autofocus>
                                @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label for="slug">Slug</label>
                                <input type="text" id="slug" name="slug" value="{{ old('slug', $policy->slug) }}" class="form-control">
                                @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label for="excerpt">Excerpt</label>
                                <textarea id="excerpt" name="excerpt" class="form-control" rows="2">{{ old('excerpt', $policy->excerpt) }}</textarea>
                                @error('excerpt') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label for="body">Body <span class="text-danger">*</span> <small>(HTML allowed)</small></label>
                                <textarea id="body" name="body" class="form-control" rows="18" required>{{ old('body', $policy->body) }}</textarea>
                                @error('body') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label for="meta_description">Meta Description</label>
                                <input type="text" id="meta_description" name="meta_description" value="{{ old('meta_description', $policy->meta_description) }}" class="form-control" maxlength="255">
                                @error('meta_description') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-row d-flex" style="gap: 20px; flex-wrap: wrap;">
                                <div class="form-group" style="flex: 1; min-width: 200px;">
                                    <label for="sort_order">Sort Order</label>
                                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $policy->sort_order) }}" class="form-control" min="0">
                                </div>

                                <div class="form-group" style="flex: 1; min-width: 200px;">
                                    <label>Status</label>
                                    <div style="display: flex; align-items: center; gap: 10px; height: 38px; padding-top: 8px;">
                                        <input type="checkbox"
                                               name="is_active"
                                               value="1"
                                               id="is_active"
                                               {{ old('is_active', $policy->is_active) ? 'checked' : '' }}
                                               style="width: 20px; height: 20px; cursor: pointer; margin: 0;">
                                        <label for="is_active" style="margin: 0; cursor: pointer; font-weight: normal;">
                                            Active (visible on storefront)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions mt-3">
                                <button type="submit" class="btn btn-primary">Update Policy</button>
                                <a href="{{ route('policy.show', $policy) }}" target="_blank" class="btn btn-link">View on site</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection